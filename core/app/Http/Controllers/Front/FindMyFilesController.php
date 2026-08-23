<?php

namespace App\Http\Controllers\Front;

use App\AccessLog;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Countries;
use App\Http\Helpers\KreativMailer;
use App\Language;
use App\OtpVerification;
use App\RateLimitAttempt;
use App\SecureToken;
use App\Services\SmsGateway\SmsGatewayInterface;
use App\Tender;
use App\TenderModule;
use App\TenderPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;

class FindMyFilesController extends Controller
{
    // Rate limit thresholds
    const FREE_ATTEMPTS         = 3;   // failed tries allowed before any cooldown kicks in
    const ATTEMPT_DECAY_MINUTES = 30;  // sliding window: idle this long → strike counter resets
    // Progressive cooldown (minutes) applied to the 4th, 5th, 6th, 7th, 8th+ failed attempt.
    // Capped at 15 min — long enough to deter brute force, short enough not to punish real users.
    const BLOCK_MINUTES   = [1, 2, 5, 10, 15];

    // Token TTL and max downloads
    const TOKEN_TTL_HOURS    = 24;
    const MAX_DOWNLOADS      = 3;
    const MAX_REGEN_PER_DAY  = 3;

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function getCurrentLang()
    {
        return currentLang();
    }

    private function getVersion($currentLang)
    {
        $be = $currentLang->basic_extended;
        return $be->theme_version === 'dark' ? 'default' : $be->theme_version;
    }

    private function emailHash(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    private function deviceHash(Request $request): string
    {
        return hash('sha256', $request->userAgent() . $request->ip());
    }

    /**
     * User-agent-only fingerprint, used to bind an OTP-recovery download link to
     * the exact browser that verified. Kept separate from deviceHash (which mixes
     * in the IP) so the device and IP signals are checked independently.
     */
    private function uaHash(Request $request): string
    {
        return hash('sha256', (string) $request->userAgent());
    }

    /**
     * True when a token is session/device/IP-bound (OTP recovery) and the current
     * request does not match all three. Unbound tokens (session_secret NULL — e.g.
     * post-payment auto-delivery links, which are emailed and portable) are never
     * blocked here. Bound links lock to the browser+device+network that verified;
     * a mismatch means the buyer must re-run OTP on this device for a fresh link.
     */
    private function bindingFails(SecureToken $token, Request $request): bool
    {
        if (empty($token->session_secret)) {
            return false; // not a bound link
        }
        if ((string) $token->ip !== (string) $request->ip()) {
            return true;
        }
        if (!hash_equals((string) $token->device_hash, $this->uaHash($request))) {
            return true;
        }
        $cookie = (string) $request->cookie('fmf_dl', '');
        return $cookie === '' || !hash_equals((string) $token->session_secret, hash('sha256', $cookie));
    }

    /**
     * How many times each secure download link may be opened. Editable at
     * admin/tender/settings (basic_settings_extra.tender_max_downloads); falls
     * back to the MAX_DOWNLOADS constant when unset or invalid.
     */
    private function maxDownloads(): int
    {
        $n = (int) optional(\App\BasicExtra::first())->tender_max_downloads;
        return $n > 0 ? $n : self::MAX_DOWNLOADS;
    }

    /**
     * How many times one order's link may be (re)issued per 24h, shared across
     * all 4 Find-My-Files methods. Editable at admin/tender/settings
     * (basic_settings_extra.tender_max_regen_per_day); falls back to the
     * MAX_REGEN_PER_DAY constant when unset or invalid.
     */
    private function maxRegenPerDay(): int
    {
        $n = (int) optional(\App\BasicExtra::first())->tender_max_regen_per_day;
        return $n > 0 ? $n : self::MAX_REGEN_PER_DAY;
    }

    /**
     * Whether the per-order recovery cap applies to a given method — false if
     * the master switch is off, or that specific method's switch is off.
     * $method is one of: 'order_number' | 'otp' | 'payref' | 'regenerate'.
     */
    private function regenCapApplies(string $method): bool
    {
        $bex = \App\BasicExtra::first();
        if (!$bex || !$bex->tender_regen_cap_enabled) {
            return false;
        }
        return (bool) ($bex->{"tender_regen_cap_$method"} ?? true);
    }

    /**
     * Brand name for the OTP SMS body — the configured site title, falling back to
     * the app name. Avoids the default "Laravel" showing in messages.
     */
    private function brandName(): string
    {
        $title = optional(optional(Language::where('is_default', 1)->first())->basic_setting)->website_title;
        return trim((string) $title) !== '' ? $title : config('app.name');
    }

    private function generateToken(TenderPurchase $purchase, string $emailHash, Request $request): string
    {
        $payload = implode('|', [
            $purchase->order_number,
            $emailHash,
            now()->timestamp,
            Str::random(16),
        ]);
        return hash_hmac('sha256', $payload, config('app.key'));
    }

    // ── Rate limiting ──────────────────────────────────────────────────────────

    private function checkRateLimit(Request $request, string $emailHash): ?array
    {
        $keys = [
            'ip:'     . $request->ip(),
            'email:'  . $emailHash,
            'device:' . $this->deviceHash($request),
        ];

        foreach ($keys as $key) {
            $record = RateLimitAttempt::where('key', $key)->first();
            if ($record && $record->isBlocked()) {
                $minutes = max(1, (int) ceil(now()->diffInSeconds($record->blocked_until) / 60));
                return [
                    'type'      => 'rate_limited',
                    'minutes'   => $minutes,
                    'wait_text' => $this->humanWait($minutes),
                ];
            }
        }
        return null;
    }

    /**
     * Human-friendly cooldown string, e.g. "45 seconds", "2 minutes", "1 hour 5 minutes".
     */
    private function humanWait(int $minutes): string
    {
        if ($minutes <= 1) {
            return __('a minute');
        }
        if ($minutes < 60) {
            return $minutes . ' ' . __('minutes');
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        $out = $h . ' ' . ($h === 1 ? __('hour') : __('hours'));
        if ($m > 0) {
            $out .= ' ' . $m . ' ' . __('minutes');
        }
        return $out;
    }

    private function incrementAttempts(Request $request, string $emailHash): void
    {
        $keys = [
            'ip:'     . $request->ip(),
            'email:'  . $emailHash,
            'device:' . $this->deviceHash($request),
        ];

        foreach ($keys as $key) {
            $record = RateLimitAttempt::firstOrCreate(
                ['key' => $key],
                ['attempts' => 0]
            );

            // Sliding window: if the user has been idle past the decay window and is not
            // currently blocked, forgive the old strikes so a fresh session starts clean.
            if (
                $record->last_attempt_at
                && !$record->isBlocked()
                && $record->last_attempt_at->lt(now()->subMinutes(self::ATTEMPT_DECAY_MINUTES))
            ) {
                $record->attempts = 0;
            }

            $record->attempts++;
            $record->last_attempt_at = now();

            // First FREE_ATTEMPTS are free; after that apply a progressive, capped cooldown.
            $over = $record->attempts - self::FREE_ATTEMPTS;
            if ($over >= 1) {
                $idx = min($over - 1, count(self::BLOCK_MINUTES) - 1);
                $record->blocked_until = now()->addMinutes(self::BLOCK_MINUTES[$idx]);
            }

            $record->save();
        }
    }

    /**
     * Clear strikes for this identity after a successful, legitimate request so honest
     * users are never carried into a cooldown by past failures.
     */
    private function resetAttempts(Request $request, string $emailHash): void
    {
        $keys = [
            'ip:'     . $request->ip(),
            'email:'  . $emailHash,
            'device:' . $this->deviceHash($request),
        ];

        RateLimitAttempt::whereIn('key', $keys)->update([
            'attempts'      => 0,
            'blocked_until' => null,
        ]);
    }

    // ── Basic risk score (0–100) ───────────────────────────────────────────────

    private function riskScore(Request $request, string $emailHash): float
    {
        $score = 0;

        // Recent LINK_REQUESTED events from same IP in last 10 min
        $recentIpAttempts = AccessLog::where('event_type', AccessLog::LINK_REQUESTED)
            ->where('ip', $request->ip())
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        $score += min(50, $recentIpAttempts * 10);

        // Same email hash in last hour
        $recentEmailAttempts = AccessLog::where('event_type', AccessLog::LINK_REQUESTED)
            ->where('email_hash', $emailHash)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $score += min(50, $recentEmailAttempts * 10);

        return min(100, $score);
    }

    // ── Send email via PHPMailer (matching project pattern) ───────────────────

    /**
     * $downloads may be a single URL string (legacy single-order callers) or a
     * list of ['title' => tender title, 'url' => download page URL] (multi-tender
     * OTP flow). Renders one titled button per tender into {download_list}.
     */
    private function sendDownloadEmail(TenderPurchase $purchase, $downloads, $be): bool
    {
        $language = Language::where('is_default', 1)->first();
        $bs       = $language->basic_setting;

        // Normalise a bare URL to a one-item list titled with the order's tender.
        if (is_string($downloads)) {
            $downloads = [[
                'title' => optional($purchase->tender)->title ?: $purchase->order_number,
                'url'   => $downloads,
            ]];
        }

        $downloadList = $this->buildDownloadButtons($downloads);
        $firstUrl     = $downloads[0]['url'] ?? '#';

        try {
            $mailer = new KreativMailer;
            $mailer->mailFromAdmin([
                'toMail'        => $purchase->email,
                'toName'        => $purchase->first_name,
                'customer_name' => $purchase->first_name,
                'order_number'  => $purchase->order_number,
                'download_url'  => $firstUrl,
                'download_list' => $downloadList,
                'expires_at'    => now()->addHours(self::TOKEN_TTL_HOURS)->format('d M Y, H:i'),
                'max_downloads' => $this->maxDownloads(),
                'website_title' => $bs->website_title,
                'templateType'  => 'tender_recovery_link',
                'type'          => 'tenderRecoveryLink',
            ]);

            \Log::info('[FMF] Download email sent', [
                'order' => $purchase->order_number,
                'to'    => substr($purchase->email, 0, 4) . '***',
            ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('[FMF] Email send failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Email-safe (inline-styled, table-based) stack of titled download buttons —
     * one per tender — injected into the {download_list} placeholder.
     */
    private function buildDownloadButtons(array $downloads): string
    {
        $rows = '';
        foreach ($downloads as $d) {
            $title = htmlspecialchars((string) ($d['title'] ?? ''), ENT_QUOTES, 'UTF-8');
            $url   = htmlspecialchars((string) ($d['url'] ?? '#'), ENT_QUOTES, 'UTF-8');

            // Sub-line: "Order XXXX · Buyer Name · Company" — only the parts present.
            $meta = array_filter([
                ($d['order']   ?? '') !== '' ? 'Order ' . $d['order'] : '',
                $d['name']    ?? '',
                $d['company'] ?? '',
            ], fn ($v) => trim((string) $v) !== '');
            $sub = $meta
                ? '<p style="margin:0 0 12px 0; font-size:12px; color:#64748b; line-height:1.5; word-break:break-word;">'
                    . htmlspecialchars(implode(' · ', $meta), ENT_QUOTES, 'UTF-8') . '</p>'
                : '';

            // Each tender = a self-contained white card: dark title on white, then
            // its own blue button. Title never sits on a coloured background.
            $rows .= '
              <tr>
                <td style="padding-bottom:12px;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0; border-radius:8px; background-color:#ffffff;">
                    <tr>
                      <td style="padding:16px 18px;">
                        <p style="margin:0 0 4px 0; font-size:14px; font-weight:700; color:#0f172a; line-height:1.45; word-break:break-word;">' . $title . '</p>
                        ' . $sub . '
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <td align="center" style="background-color:#2563eb; border-radius:6px;">
                              <a href="' . $url . '" target="_blank" style="display:block; padding:13px 24px; font-size:14px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:6px; text-align:center; background-color:#2563eb; letter-spacing:0.01em; line-height:1;">&#8659;&nbsp; Download Secure Files</a>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>';
        }

        return '<table width="100%" cellpadding="0" cellspacing="0" border="0">' . $rows . '</table>';
    }

    /**
     * Absolute path to the buyer's payment-receipt PDF, generating it if the
     * stored file is missing (older orders / cleared storage). Mirrors the
     * post-payment invoice generation. Returns null if it can't be produced.
     */
    private function receiptPath(TenderPurchase $purchase): ?string
    {
        $dir = storage_path('app/invoices/tender/');

        if (!empty($purchase->invoice) && file_exists($dir . $purchase->invoice)) {
            return $dir . $purchase->invoice;
        }

        try {
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $lang = Language::where('is_default', 1)->first();
            $bse  = optional($lang)->basic_extra ?? \App\BasicExtra::first();
            $bs   = optional($lang)->basic_setting;

            $logoSrc = null;
            if ($bs && !empty($bs->logo)) {
                foreach ([
                    storage_path('app/public/front/img/' . $bs->logo),
                    base_path(FRONT_IMG_PUBLIC_DIR . $bs->logo),
                    base_path(FRONT_IMG_DIR . $bs->logo),
                ] as $p) {
                    if (file_exists($p)) {
                        $ext     = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                        $mime    = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/' . $ext;
                        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($p));
                        break;
                    }
                }
            }

            $purchase->load('tender');
            $fileName = $purchase->order_number . '.pdf';

            \PDF::loadView('pdf.tender', [
                'order'   => $purchase,
                'bse'     => $bse,
                'bs'      => $bs,
                'logoSrc' => $logoSrc,
            ])->setPaper('a4', 'portrait')->save($dir . $fileName);

            $purchase->update(['invoice' => $fileName]);

            return $dir . $fileName;
        } catch (\Throwable $e) {
            \Log::error('[FMF] Receipt generation failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function resolveTender(Request $request): ?Tender
    {
        $slug = trim($request->input('tender_slug', ''));
        if (!$slug) return null;
        return Tender::where('slug', $slug)->first();
    }

    /**
     * Tenders the buyer ticked on the OTP form. Accepts the multi-select
     * tender_slugs[] and falls back to the single tender_slug. Returns an empty
     * collection when nothing was chosen.
     */
    private function resolveTenders(Request $request)
    {
        $slugs = collect((array) $request->input('tender_slugs', []))
            ->merge([$request->input('tender_slug')])
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->unique()
            ->values();

        if ($slugs->isEmpty()) {
            return collect();
        }

        return Tender::whereIn('slug', $slugs)->get();
    }

    private function scopeToPurchase(object $query, ?Tender $tender): object
    {
        if ($tender) {
            $query->where('tender_id', $tender->id);
        }
        return $query;
    }

    /**
     * A suspended transaction is blocked from issuing links or downloading.
     * Returns the JSON "under verification" response for AJAX entry points,
     * or null when the order is fine to proceed.
     */
    private function suspendedResponse(?TenderPurchase $purchase)
    {
        if ($purchase && $purchase->isSuspended()) {
            return response()->json([
                'status'  => 'error',
                'type'    => 'suspended',
                'message' => __('This order is currently being verified. For any information, please contact ICA.'),
            ]);
        }
        return null;
    }

    // ── Controller actions ─────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $currentLang = $this->getCurrentLang();
        $bs          = $currentLang->basic_setting;

        Config::set('captcha.sitekey', $bs->google_recaptcha_site_key);
        Config::set('captcha.secret', $bs->google_recaptcha_secret_key);

        $slug    = trim($request->query('tender', ''));
        $tender  = $slug ? Tender::where('slug', $slug)->first() : null;
        $tenders = Tender::orderBy('title')->get(['id', 'title', 'slug', 'tender_code']);

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersion($currentLang);
        $data['bs']          = $bs;
        $data['tender']      = $tender;
        $data['tenders']     = $tenders;
        $data['countries']   = Countries::forCheckout();

        return view('front.find-my-files.index', $data);
    }

    public function requestLink(Request $request)
    {
        $currentLang = $this->getCurrentLang();
        $bs          = $currentLang->basic_setting;
        $be          = $currentLang->basic_extended;

        Config::set('captcha.sitekey', $bs->google_recaptcha_site_key);
        Config::set('captcha.secret', $bs->google_recaptcha_secret_key);

        $emailHash  = $this->emailHash($request->input('email', ''));
        $deviceHash = $this->deviceHash($request);

        $logMeta = [
            'ip'          => $request->ip(),
            'user_agent'  => substr($request->userAgent(), 0, 255),
            'email_hash'  => $emailHash,
            'device_hash' => $deviceHash,
            'order_id'    => $request->input('order_number', ''),
        ];

        // ── 1. Format validation ──────────────────────────────────────────────
        $rules = [
            'email'        => 'required|email',
            'order_number' => 'required|string|min:3|max:50',
        ];

        if ($bs->is_recaptcha == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'INVALID_INPUT',
                'risk_score' => 0,
            ]));
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        // ── 2. Log the attempt ────────────────────────────────────────────────
        $risk = $this->riskScore($request, $emailHash);

        AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
            'result'     => 'PENDING',
            'risk_score' => $risk,
        ]));

        // ── 3. Rate limit check ───────────────────────────────────────────────
        $blocked = $this->checkRateLimit($request, $emailHash);

        if ($blocked) {
            AccessLog::record(AccessLog::RATE_LIMIT_TRIGGERED, array_merge($logMeta, [
                'result'     => 'RATE_LIMITED',
                'risk_score' => $risk,
            ]));
            return response()->json([
                'status'  => 'error',
                'type'    => 'rate_limited',
                'minutes' => $blocked['minutes'],
            ]);
        }

        // ── 4. Order lookup ───────────────────────────────────────────────────
        $orderNumber = strtoupper(trim($request->input('order_number')));
        $purchase    = TenderPurchase::where('order_number', $orderNumber)->first();

        if (!$purchase) {
            $this->incrementAttempts($request, $emailHash);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'ORDER_NOT_FOUND',
                'risk_score' => $risk,
            ]));
            return response()->json([
                'status'  => 'error',
                'type'    => 'order_not_found',
                'message' => __('No order was found with this order number. Please check and try again.'),
            ]);
        }

        // ── 5. Email match ────────────────────────────────────────────────────
        if (strtolower(trim($purchase->email)) !== strtolower(trim($request->input('email')))) {
            $this->incrementAttempts($request, $emailHash);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'EMAIL_MISMATCH',
                'risk_score' => $risk,
            ]));
            return response()->json([
                'status'  => 'error',
                'type'    => 'email_mismatch',
                'message' => __('The email address does not match the one used for this order.'),
            ]);
        }

        // ── 6. Payment status check ───────────────────────────────────────────
        if ($purchase->payment_status !== 'Completed') {
            $this->incrementAttempts($request, $emailHash);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'INVALID_STATUS',
                'risk_score' => $risk,
            ]));
            return response()->json([
                'status'  => 'error',
                'type'    => 'invalid_status',
                'message' => __('This order has not been completed. Only paid orders are eligible for file recovery.'),
            ]);
        }

        // ── 6b. Suspension check — admin-blocked transactions go no further ───
        if ($r = $this->suspendedResponse($purchase)) {
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'SUSPENDED',
                'risk_score' => $risk,
            ]));
            return $r;
        }

        // ── 6c. Recovery cap: max N link issuances per order per 24h, shared
        // across all 4 Find-My-Files methods (admin configurable) ────────────
        if ($this->regenCapApplies('order_number')) {
            $regenCount = SecureToken::where('email_hash', $emailHash)
                ->where('order_id', $purchase->order_number)
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            if ($regenCount >= $this->maxRegenPerDay()) {
                AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                    'result'     => 'REGEN_LIMIT_EXCEEDED',
                    'risk_score' => $risk,
                    'order_id'   => $purchase->order_number,
                ]));
                return response()->json(['status' => 'error', 'type' => 'regen_limit']);
            }
        }

        // ── 7. Revoke any previous active tokens for this order ───────────────
        SecureToken::where('order_id', $purchase->order_number)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // ── 8. Generate & store signed token ──────────────────────────────────
        $rawToken  = $this->generateToken($purchase, $emailHash, $request);
        $tokenHash = hash('sha256', $rawToken);

        SecureToken::create([
            'order_id'       => $purchase->order_number,
            'email_hash'     => $emailHash,
            'token_hash'     => $tokenHash,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(self::TOKEN_TTL_HOURS),
            'max_downloads'  => $this->maxDownloads(),
            'download_count' => 0,
            'status'         => 'active',
            'device_hash'    => $deviceHash,
            'ip'             => $request->ip(),
        ]);

        // ── 9. Build signed download URL ──────────────────────────────────────
        $downloadUrl = route('find_my_files.download', ['t' => $rawToken]);

        // ── 10. Send email ────────────────────────────────────────────────────
        $emailSent = $this->sendDownloadEmail($purchase, $downloadUrl, $be);
        \Log::info('[OrderNumber] Email result', [
            'order' => $purchase->order_number,
            'sent'  => $emailSent,
        ]);

        // ── 11. Log success ───────────────────────────────────────────────────
        if (!$emailSent) {
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'EMAIL_FAILED',
                'risk_score' => $risk,
            ]));
            return response()->json(['status' => 'error', 'type' => 'email_failed']);
        }

        $this->resetAttempts($request, $emailHash);

        AccessLog::record(AccessLog::LINK_SENT, array_merge($logMeta, [
            'result'     => 'OK',
            'risk_score' => $risk,
        ]));

        return response()->json([
            'status'   => 'success',
            'redirect' => route('find_my_files.link_sent'),
        ]);
    }

    public function securityInfo()
    {
        $currentLang = $this->getCurrentLang();

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersion($currentLang);
        $data['bs']          = $currentLang->basic_setting;

        return view('front.find-my-files.security_info', $data);
    }

    public function linkSent()
    {
        $currentLang = $this->getCurrentLang();

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersion($currentLang);
        $data['bs']          = $currentLang->basic_setting;
        $data['maxDownloads'] = $this->maxDownloads();

        return view('front.find-my-files.link_sent', $data);
    }

    // ── Resolve and validate a raw token string ───────────────────────────────

    private function resolveToken(string $raw): ?SecureToken
    {
        $hash  = hash('sha256', $raw);
        $token = SecureToken::where('token_hash', $hash)->first();

        if (!$token) {
            return null;
        }

        // Expire overdue tokens automatically
        if ($token->status === 'active' && $token->expires_at->isPast()) {
            $token->update(['status' => 'expired']);
        }

        return $token;
    }

    // ── Step 8 — Download confirmation page ───────────────────────────────────

    public function download(Request $request)
    {
        $currentLang = $this->getCurrentLang();
        $raw         = $request->query('t', '');

        if (empty($raw)) {
            return $this->downloadError($currentLang);
        }

        $token = $this->resolveToken($raw);

        if (!$token || !$token->isValid()) {
            AccessLog::record(AccessLog::DOWNLOAD_FAILED, [
                'ip'         => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'result'     => $token ? ($token->status === 'expired' ? 'TOKEN_EXPIRED' : 'TOKEN_EXHAUSTED') : 'TOKEN_NOT_FOUND',
                'risk_score' => 0,
            ]);
            return $this->downloadError($currentLang);
        }

        // ── Suspension check — admin may have suspended after the link was issued ─
        $purchase = TenderPurchase::where('order_number', $token->order_id)->first();
        if ($purchase && $purchase->isSuspended()) {
            return $this->suspendedDownloadError($currentLang, $purchase);
        }

        // ── Binding check — link is locked to the browser+device+IP that verified ─
        if ($this->bindingFails($token, $request)) {
            AccessLog::record(AccessLog::DOWNLOAD_FAILED, [
                'ip'         => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'order_id'   => $token->order_id,
                'result'     => 'BINDING_MISMATCH',
                'risk_score' => 0,
            ]);
            return $this->downloadError($currentLang, [
                'message' => __('This link is locked to the device, browser and network that requested it. Please repeat the verification on this device to get a new link.'),
            ]);
        }

        // Risk score for logging
        $risk = $this->riskScore($request, $token->email_hash);

        // NOTE: opening this confirmation page no longer consumes a download. The
        // count is charged in downloadStream() only when the file is actually
        // served, so an email-link click that never transfers a file is free.

        AccessLog::record(AccessLog::LINK_CLICKED, [
            'ip'          => $request->ip(),
            'device_hash' => $this->deviceHash($request),
            'user_agent'  => substr($request->userAgent(), 0, 255),
            'email_hash'  => $token->email_hash,
            'order_id'    => $token->order_id,
            'result'      => 'OK',
            'risk_score'  => $risk,
        ]);

        $data['token']       = $token;
        $data['rawToken']    = $raw;
        $data['streamUrl']   = route('find_my_files.stream', ['t' => $raw]);
        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersion($currentLang);
        $data['bs']          = $currentLang->basic_setting;
        $data['appUrl']      = config('app.url');

        return view('front.find-my-files.download', $data);
    }

    private function downloadError($currentLang, array $opts = [])
    {
        $data['bse']          = $currentLang->basic_extra;
        $data['currentLang']  = $currentLang;
        $data['version']      = $this->getVersion($currentLang);
        $data['bs']           = $currentLang->basic_setting;
        $data['errorTitle']   = $opts['title']     ?? null;
        $data['errorMessage'] = $opts['message']   ?? null;
        $data['suspended']    = $opts['suspended'] ?? false;
        $data['maxDownloads'] = $this->maxDownloads();
        return view('front.find-my-files.download_error', $data);
    }

    /**
     * Suspended-transaction page for the download confirm/stream step.
     * Also revokes any lingering active tokens so the links die immediately.
     */
    private function suspendedDownloadError($currentLang, ?TenderPurchase $purchase)
    {
        if ($purchase) {
            SecureToken::where('order_id', $purchase->order_number)
                ->where('status', 'active')
                ->update(['status' => 'revoked']);
        }

        return $this->downloadError($currentLang, [
            'suspended' => true,
            'title'     => __('This link has been disabled by the administrator.'),
            'message'   => __('This link has been disabled by the administrator. Please contact ICA support.'),
        ]);
    }

    // ── Step 8 — Stream ZIP of all modules ────────────────────────────────────

    public function downloadStream(Request $request)
    {
        $raw = $request->query('t', '');

        if (empty($raw)) {
            abort(403);
        }

        $token = $this->resolveToken($raw);

        if (!$token || !$token->isValid()) {
            AccessLog::record(AccessLog::DOWNLOAD_FAILED, [
                'ip'         => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'result'     => 'TOKEN_INVALID_AT_STREAM',
                'risk_score' => 0,
            ]);
            abort(403);
        }

        // Enforce the browser+device+IP binding on the actual file transfer too.
        if ($this->bindingFails($token, $request)) {
            AccessLog::record(AccessLog::DOWNLOAD_FAILED, [
                'ip'         => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'order_id'   => $token->order_id,
                'result'     => 'BINDING_MISMATCH_AT_STREAM',
                'risk_score' => 0,
            ]);
            abort(403);
        }

        // ── Fetch purchase + modules ──────────────────────────────────────────
        $purchase = TenderPurchase::where('order_number', $token->order_id)->first();

        if (!$purchase) {
            abort(404);
        }

        // Suspended transaction → kill its tokens and refuse the file outright.
        if ($purchase->isSuspended()) {
            SecureToken::where('order_id', $purchase->order_number)
                ->where('status', 'active')
                ->update(['status' => 'revoked']);
            abort(403);
        }

        // Only the modules this buyer actually owns may be downloaded.
        // Paid modules are matched by name (purchased_modules stores no ids); free
        // modules (no cost) are always allowed. Authorise this order's own modules
        // plus every module the same company (registration no.) owns across orders;
        // the union also covers legacy orders placed before registration numbers,
        // whose own modules stay downloadable even though their reg. no. is blank.
        $ownNames = collect(json_decode($purchase->purchased_modules, true) ?: [])
            ->pluck('name')->map(fn($n) => trim((string) $n))->filter()->all();
        $paidNames = array_values(array_unique(array_merge(
            $ownNames,
            TenderPurchase::paidModuleNamesForReg($purchase->tender_id, $purchase->company_registration_no)
        )));

        $modules = TenderModule::where('tender_id', $purchase->tender_id)
            ->where('status', 1)
            ->get()
            ->filter(function ($m) use ($paidNames) {
                return is_null($m->cost) || in_array(trim($m->name), $paidNames, true);
            })
            ->values();

        // ── Build ZIP ─────────────────────────────────────────────────────────
        $tempDir = env('FMF_ZIP_TEMP_PATH', storage_path('app/temp'));
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipName = 'tender_' . $token->order_id . '_' . time() . '.zip';
        $zipPath = $tempDir . '/' . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500);
        }

        // ── Watermark config (global) + per-buyer stamp variables ─────────────
        $wm      = \App\BasicExtra::first();
        $wmOn    = $wm && (int) $wm->tender_watermark_enabled === 1;
        $encOn   = $wm && (int) $wm->tender_pdf_encrypt_enabled === 1 && trim((string) $wm->tender_pdf_password) !== '';
        $wmSettings = [
            'watermark' => $wmOn,
            'template'  => $wm->tender_watermark_template ?? '',
            'opacity'   => $wm->tender_watermark_opacity ?? 0.30,
            'color'     => $wm->tender_watermark_color ?? 'FF0000',
            'font_size' => $wm->tender_watermark_font_size ?? 24,
            'rotation'  => $wm->tender_watermark_rotation ?? 45,
            'encrypt'   => $encOn,
            'password'  => (string) ($wm->tender_pdf_password ?? ''),
        ];
        $wmVars = [
            'company'      => (string) $purchase->company_name,
            'name'         => trim($purchase->first_name . ' ' . $purchase->last_name),
            'first_name'   => (string) $purchase->first_name,
            'last_name'    => (string) $purchase->last_name,
            'tender_code'  => (string) optional($purchase->tender)->tender_code,
            'tender_title' => (string) optional($purchase->tender)->title,
            'order_number' => (string) $purchase->order_number,
            'email'        => (string) $purchase->email,
            'datetime'     => now()->utc()->format('j F Y, H:i') . ' UTC',
            'date'         => now()->utc()->format('j F Y'),
        ];

        $processPdf = $wmOn || $encOn;
        $stamper    = $processPdf ? new \App\Services\TenderPdfStamper() : null;
        $stampTemps = [];

        $failStamp = function ($module, $reason) use ($zip, $zipPath, &$stampTemps, $request, $token) {
            $zip->close();
            @unlink($zipPath);
            foreach ($stampTemps as $t) {
                @unlink($t);
            }
            \Log::error('Tender watermark failed — download blocked', [
                'order_id' => $token->order_id,
                'module'   => $module->name ?? null,
                'reason'   => $reason,
            ]);
            AccessLog::record(AccessLog::DOWNLOAD_FAILED, [
                'ip'         => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'order_id'   => $token->order_id,
                'result'     => 'WATERMARK_FAILED',
                'risk_score' => 0,
            ]);
        };

        $added = 0;
        foreach ($modules as $module) {
            if (empty($module->tender_file)) {
                continue;
            }
            $modulesDir = env('FMF_MODULES_PATH', storage_path('app/tender_modules'));
            $filePath = rtrim($modulesDir, '/') . '/' . $module->tender_file;
            if (!file_exists($filePath)) {
                continue;
            }

            $ext      = strtolower(pathinfo($module->tender_file, PATHINFO_EXTENSION));
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $module->name);

            // PDFs get a personalised traceable watermark. A module uploaded as a
            // ZIP (e.g. a technical or financial proposal bundling several PDFs)
            // is unpacked so every PDF inside is stamped/encrypted too, then
            // repackaged. Other file types (docx, xls, images) can't be stamped
            // and pass through untouched.
            if ($processPdf && $ext === 'pdf') {
                $stampedPath = $tempDir . '/wm_' . $token->order_id . '_' . $module->id . '_' . uniqid() . '.pdf';
                try {
                    $stamper->stampFile($filePath, $stampedPath, $wmSettings, $wmVars);
                } catch (\Throwable $e) {
                    // Block-on-failure: never serve an un-stamped tender PDF.
                    $failStamp($module, $e->getMessage());
                    abort(500, 'Document could not be prepared for download. Please contact ICA support.');
                }
                $stampTemps[] = $stampedPath;
                $zip->addFile($stampedPath, $safeName . '.' . $ext);
            } elseif ($processPdf && $ext === 'zip') {
                try {
                    $rebuilt = $this->stampPdfsInZip($filePath, $tempDir, $wmSettings, $wmVars, $stamper);
                } catch (\Throwable $e) {
                    // Same fail-closed rule: if any PDF inside the bundle can't be
                    // stamped, the whole download is refused.
                    $failStamp($module, $e->getMessage());
                    abort(500, 'Document could not be prepared for download. Please contact ICA support.');
                }
                $stampTemps[] = $rebuilt;
                $zip->addFile($rebuilt, $safeName . '.zip');
            } else {
                $zip->addFile($filePath, $safeName . '.' . $ext);
            }
            $added++;
        }

        // Always include the buyer's payment receipt PDF in the archive.
        $receipt = $this->receiptPath($purchase);
        if ($receipt && file_exists($receipt)) {
            $zip->addFile($receipt, 'Payment_Receipt_' . $purchase->order_number . '.pdf');
            $added++;
        }

        $zip->close();

        // Stamped temp PDFs are already copied into the archive; drop them.
        foreach ($stampTemps as $t) {
            @unlink($t);
        }

        if ($added === 0) {
            @unlink($zipPath);
            abort(404);
        }

        // ── Charge one download now that the file is actually being served ────
        // (page-open no longer counts — only a real file transfer does). Expire
        // the token once the allowance is spent.
        $token->increment('download_count');
        if ($token->download_count >= $token->max_downloads) {
            $token->update(['status' => 'expired']);
        }

        AccessLog::record(AccessLog::DOWNLOAD_SUCCESS, [
            'ip'          => $request->ip(),
            'device_hash' => $this->deviceHash($request),
            'user_agent'  => substr($request->userAgent(), 0, 255),
            'email_hash'  => $token->email_hash,
            'order_id'    => $token->order_id,
            'result'      => 'OK',
            'risk_score'  => 0,
        ]);

        // ── Stream + delete ───────────────────────────────────────────────────
        // no-store so repeat downloads always hit the server (and get counted)
        // instead of being replayed from the browser cache for the same URL.
        return response()->download(
            $zipPath,
            'tender_documents_' . $token->order_id . '.zip',
            [
                'Content-Type'  => 'application/zip',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Expires'       => '0',
            ]
        )->deleteFileAfterSend(true);
    }

    /**
     * Unpack a module ZIP, stamp/encrypt EVERY PDF it contains (recursively,
     * preserving the internal folder layout), then repackage into a fresh ZIP.
     *
     * Non-PDF entries are copied through unchanged. Any single PDF that fails to
     * stamp throws — the caller then refuses the whole download (fail-closed),
     * so a buyer can never receive an un-watermarked PDF hidden inside a bundle.
     *
     * @return string path to the rebuilt ZIP (caller owns cleanup)
     * @throws \RuntimeException
     */
    private function stampPdfsInZip(
        string $srcZip,
        string $workRoot,
        array $wmSettings,
        array $wmVars,
        \App\Services\TenderPdfStamper $stamper
    ): string {
        $extractDir = $workRoot . '/unzip_' . uniqid();
        $outZip     = $workRoot . '/wmzip_' . uniqid() . '.zip';

        try {
            $za = new \ZipArchive();
            if ($za->open($srcZip) !== true) {
                throw new \RuntimeException('Could not open module archive: ' . basename($srcZip));
            }

            // Guard against Zip-Slip: reject any entry that would escape the
            // extraction directory (path traversal or an absolute path).
            for ($i = 0; $i < $za->numFiles; $i++) {
                $name = $za->getNameIndex($i);
                if ($name === false) {
                    continue;
                }
                if (str_contains($name, '..') || preg_match('#^([A-Za-z]:)?[\\\\/]#', $name)) {
                    $za->close();
                    throw new \RuntimeException('Unsafe path in archive: ' . $name);
                }
            }

            if (!mkdir($extractDir, 0755, true) && !is_dir($extractDir)) {
                $za->close();
                throw new \RuntimeException('Could not create extraction directory.');
            }
            if (!$za->extractTo($extractDir)) {
                $za->close();
                throw new \RuntimeException('Could not extract module archive.');
            }
            $za->close();

            // Stamp every PDF found anywhere in the tree, in place.
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extractDir, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($it as $file) {
                if (!$file->isFile() || strtolower($file->getExtension()) !== 'pdf') {
                    continue;
                }
                $p   = $file->getPathname();
                $tmp = $p . '.stamped';
                $stamper->stampFile($p, $tmp, $wmSettings, $wmVars);
                if (!@rename($tmp, $p)) {
                    @unlink($tmp);
                    throw new \RuntimeException('Could not replace stamped PDF: ' . $file->getFilename());
                }
            }

            // Repackage, preserving the original relative structure.
            $out = new \ZipArchive();
            if ($out->open($outZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Could not create processed archive.');
            }
            $rebuild = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extractDir, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($rebuild as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $rel = ltrim(substr($file->getPathname(), strlen($extractDir)), '/\\');
                $out->addFile($file->getPathname(), $rel);
            }
            $out->close();

            return $outZip;
        } catch (\Throwable $e) {
            @unlink($outZip);
            throw $e;
        } finally {
            // The rebuilt ZIP is self-contained; the extraction tree is disposable.
            $this->rrmdir($extractDir);
        }
    }

    /** Recursively delete a directory and its contents. */
    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MODULE 4 — Expired Link / Regenerate
    // ══════════════════════════════════════════════════════════════════════════

    public function requestRegenerate(Request $request)
    {
        $currentLang = $this->getCurrentLang();
        $be          = $currentLang->basic_extended;

        $emailHash  = $this->emailHash($request->input('email', ''));
        $deviceHash = $this->deviceHash($request);

        $logMeta = [
            'ip'          => $request->ip(),
            'user_agent'  => substr($request->userAgent(), 0, 255),
            'email_hash'  => $emailHash,
            'device_hash' => $deviceHash,
            'order_id'    => '',
        ];

        // ── 1. Format validation ──────────────────────────────────────────────
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        // ── 2. Rate limit check ───────────────────────────────────────────────
        $blocked = $this->checkRateLimit($request, $emailHash);
        if ($blocked) {
            AccessLog::record(AccessLog::RATE_LIMIT_TRIGGERED, array_merge($logMeta, [
                'result'     => 'RATE_LIMITED',
                'risk_score' => 0,
            ]));
            return response()->json([
                'status'  => 'error',
                'type'    => 'rate_limited',
                'minutes' => $blocked['minutes'],
            ]);
        }

        $risk = $this->riskScore($request, $emailHash);

        // ── 3. Find the order(s) to re-issue links for ────────────────────────
        $email    = strtolower(trim($request->input('email')));
        $regNo    = TenderPurchase::normalizeRegNo($request->input('company_registration_no', ''));
        $tenderId = (int) $request->input('tender_id', 0);

        if ($regNo !== '' && $tenderId > 0) {
            // Checkout "already paid" context: the entered email MUST be the one
            // that paid under this registration number on this tender. Otherwise
            // anyone knowing a registration number could mail themselves the link.
            // Single order only — a checkout retry is scoped to the tender the
            // buyer is currently on, not their whole purchase history.
            $regOrders = TenderPurchase::where('payment_status', 'Completed')
                ->where('tender_id', $tenderId)
                ->get()
                ->filter(fn ($p) => TenderPurchase::normalizeRegNo($p->company_registration_no) === $regNo);

            $purchase = $regOrders->first(fn ($p) => strtolower(trim($p->email)) === $email);

            if (!$purchase) {
                // Email does not match this registration number's order → refuse.
                $this->incrementAttempts($request, $emailHash);
                AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                    'result'     => 'REGEN_EMAIL_MISMATCH',
                    'risk_score' => $risk,
                ]));
                return response()->json([
                    'status' => 'error',
                    'type'   => 'email_mismatch',
                ]);
            }

            $purchases = collect([$purchase]);

            // Registration-number recovery already has its own second factor
            // (the reg. no. itself) — issue immediately, no OTP gate.
            return $this->issueRecoveryLinks($purchases, $emailHash, $request, $be, $logMeta, $risk);
        }

        // Email-only recovery (Method 4): every completed purchase under this
        // email within a required purchase-date range — the link is only ever
        // sent to the address that placed the order, so it can't be redirected
        // elsewhere. Range is validated on paid_at, the actual payment
        // timestamp. This is the weakest-auth path (email alone), so it's
        // additionally gated behind an emailed OTP before anything is issued.
        $rangeValidator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'date_from' => 'required|date|before_or_equal:today',
            'date_to'   => 'required|date|before_or_equal:today',
        ]);

        if ($rangeValidator->fails()) {
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        $dateFrom = \Carbon\Carbon::parse($request->input('date_from'))->startOfDay();
        $dateTo   = \Carbon\Carbon::parse($request->input('date_to'))->endOfDay();
        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        // Some Completed purchases have no paid_at (older rows, or admin
        // manually marking an offline payment Completed without going through
        // the gateway helper) — fall back to created_at so those orders stay
        // reachable instead of silently disappearing from every possible date
        // range.
        $purchases = TenderPurchase::where('payment_status', 'Completed')
            ->get()
            ->filter(fn ($p) => strtolower(trim($p->email)) === $email)
            ->filter(function ($p) use ($dateFrom, $dateTo) {
                $ref = $p->paid_at ?: $p->created_at;
                return $ref && $ref->gte($dateFrom) && $ref->lte($dateTo);
            })
            ->sortByDesc(fn ($p) => $p->paid_at ?: $p->created_at)
            ->values();

        if ($purchases->isEmpty()) {
            // No email match at all → explicit "not found", same product
            // choice already made for the OTP method (clearer UX over
            // anti-enumeration; rate limiting is what actually slows probing,
            // not response ambiguity).
            $this->incrementAttempts($request, $emailHash);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'REGEN_NO_MATCH',
                'risk_score' => $risk,
            ]));
            return response()->json(['status' => 'error', 'type' => 'no_match']);
        }

        // ── 4. Invalidate any previous pending OTP for these orders ───────────
        $orderIds = $purchases->pluck('order_number')->all();
        $primary  = $purchases->first();

        OtpVerification::whereIn('order_id', $orderIds)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // ── 5. Generate, store & email the OTP ─────────────────────────────────
        $rawOtp       = OtpVerification::generateOtp();
        $sessionToken = Str::uuid()->toString();

        OtpVerification::create([
            'session_token'  => $sessionToken,
            'email_hash'     => $emailHash,
            'phone_hash'     => null,
            'channel'        => 'email',
            'otp_hash'       => hash('sha256', $rawOtp),
            'order_id'       => $primary->order_number,
            'order_ids'      => $orderIds,
            'expires_at'     => now()->addMinutes(OtpVerification::OTP_TTL_MIN),
            'attempts'       => 0,
            'last_resend_at' => now(),
            'status'         => 'pending',
            'ip'             => $request->ip(),
            'device_hash'    => $deviceHash,
            'masked_phone'   => null,
        ]);

        $bs   = $currentLang->basic_setting;
        $sent = $this->sendRecoveryOtpEmail($email, $primary->first_name, $rawOtp, $bs);

        if (!$sent) {
            OtpVerification::where('session_token', $sessionToken)->update(['status' => 'expired']);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'REGEN_OTP_EMAIL_FAILED',
                'risk_score' => $risk,
                'order_id'   => $primary->order_number,
            ]));
            return response()->json(['status' => 'error', 'type' => 'email_failed']);
        }

        AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
            'result'     => 'REGEN_OTP_SENT',
            'risk_score' => $risk,
            'order_id'   => $primary->order_number,
        ]));

        return response()->json([
            'status'        => 'success',
            'otp_sent'      => true,
            'session_token' => $sessionToken,
            'masked_email'  => OtpVerification::maskEmail($email),
            'resend_after'  => OtpVerification::RESEND_DELAY,
        ]);
    }

    /**
     * Sends the recovery OTP code by email. Shared by the initial request and
     * the resend endpoint.
     */
    private function sendRecoveryOtpEmail(string $email, string $toName, string $rawOtp, $bs): bool
    {
        try {
            $mailer = new KreativMailer;
            return (bool) $mailer->mailFromAdmin([
                'toMail'          => $email,
                'toName'          => $toName,
                'otp_code'        => $rawOtp,
                'otp_ttl_minutes' => OtpVerification::OTP_TTL_MIN,
                'website_title'   => $bs->website_title,
                'templateType'    => 'tender_recovery_otp',
                'type'            => 'tenderRecoveryOtp',
            ]);
        } catch (\Exception $e) {
            \Log::error('[Regenerate] OTP email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Issues a SecureToken + emails download links for every eligible purchase
     * in $purchases (skips suspended / capped orders individually). Shared by
     * the immediate registration-number path and the post-OTP-verify path for
     * the email-only branch.
     */
    private function issueRecoveryLinks($purchases, string $emailHash, Request $request, $be, array $logMeta, float $risk)
    {
        // One binding secret for the whole batch — every emailed link in this
        // request is locked to this browser + device + IP (same as OTP/payref).
        $sessionSecret = Str::random(40);
        $sessionHash   = hash('sha256', $sessionSecret);

        $downloads      = [];
        $anySuspended   = false;
        $anyCapExceeded = false;

        foreach ($purchases as $purchase) {
            if ($purchase->isSuspended()) {
                $anySuspended = true;
                continue;
            }

            // Regeneration cap: max N new tokens per order per 24h (admin
            // configurable, shared across all 4 methods) — scoped to THIS
            // order, so one order hitting its cap doesn't block the rest.
            if ($this->regenCapApplies('regenerate')) {
                $regenCount = SecureToken::where('email_hash', $emailHash)
                    ->where('order_id', $purchase->order_number)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->count();

                if ($regenCount >= $this->maxRegenPerDay()) {
                    $anyCapExceeded = true;
                    AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                        'result'     => 'REGEN_LIMIT_EXCEEDED',
                        'risk_score' => $risk,
                        'order_id'   => $purchase->order_number,
                    ]));
                    continue;
                }
            }

            SecureToken::where('order_id', $purchase->order_number)
                ->where('status', 'active')
                ->update(['status' => 'revoked']);

            $rawToken = $this->generateToken($purchase, $emailHash, $request);

            SecureToken::create([
                'order_id'       => $purchase->order_number,
                'email_hash'     => $emailHash,
                'token_hash'     => hash('sha256', $rawToken),
                'issued_at'      => now(),
                'expires_at'     => now()->addHours(self::TOKEN_TTL_HOURS),
                'max_downloads'  => $this->maxDownloads(),
                'download_count' => 0,
                'status'         => 'active',
                'device_hash'    => $this->uaHash($request), // UA only (IP checked separately)
                'ip'             => $request->ip(),
                'session_secret' => $sessionHash,
            ]);

            $downloads[] = [
                'title'   => optional($purchase->tender)->title ?: $purchase->order_number,
                'url'     => route('find_my_files.download', ['t' => $rawToken]),
                'name'    => trim($purchase->first_name . ' ' . $purchase->last_name),
                'company' => (string) $purchase->company_name,
                'order'   => $purchase->order_number,
            ];

            AccessLog::record(AccessLog::LINK_SENT, array_merge($logMeta, [
                'result'     => 'REGEN_OK',
                'risk_score' => $risk,
                'order_id'   => $purchase->order_number,
            ]));
        }

        // Every matched order turned out non-issuable.
        if (empty($downloads)) {
            if ($anySuspended && $r = $this->suspendedResponse($purchases->first(fn ($p) => $p->isSuspended()))) {
                return $r;
            }
            if ($anyCapExceeded) {
                return response()->json(['status' => 'error', 'type' => 'regen_limit']);
            }
            // Shouldn't normally reach here ($purchases was non-empty and every
            // entry is either suspended, cap-exceeded, or issued) — but stay
            // consistent with the "clear over neutral" choice above if it does.
            return response()->json(['status' => 'error', 'type' => 'no_match']);
        }

        // ── Email the new download link(s) ─────────────────────────────────────
        $emailSent = $this->sendDownloadEmail($purchases->first(), $downloads, $be);
        \Log::info('[Regenerate] Email result', [
            'orders' => array_column($downloads, 'order'),
            'sent'   => $emailSent,
        ]);

        if (!$emailSent) {
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'REGEN_EMAIL_FAILED',
                'risk_score' => $risk,
            ]));
            return response()->json(['status' => 'error', 'type' => 'email_failed']);
        }

        $this->resetAttempts($request, $emailHash);

        // Drop the binding cookie on this browser so every emailed link is
        // locked to it (+ device + IP), matching the OTP and payment-reference
        // flows.
        return response()->json([
            'status'   => 'success',
            'redirect' => route('find_my_files.link_sent'),
        ])->cookie(
            'fmf_dl',
            $sessionSecret,
            self::TOKEN_TTL_HOURS * 60,
            '/',
            null,
            $request->secure(),
            true,
            false,
            'Lax'
        );
    }

    public function verifyRegenerateOtp(Request $request)
    {
        $currentLang = $this->getCurrentLang();
        $be          = $currentLang->basic_extended;
        $deviceHash  = $this->deviceHash($request);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'session_token' => 'required|string|size:36',
            'otp_code'      => 'required|string|size:6|regex:/^\d{6}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        $otp = OtpVerification::where('session_token', $request->input('session_token'))
            ->where('channel', 'email')
            ->first();

        if (!$otp) {
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        if (!$otp->isUsable()) {
            $type = ($otp->status === 'exhausted') ? 'otp_exhausted' : 'otp_expired';
            return response()->json(['status' => 'error', 'type' => $type]);
        }

        // ── Wrong OTP ─────────────────────────────────────────────────────────
        if (!$otp->verifyOtp($request->input('otp_code'))) {
            $otp->increment('attempts');

            if ($otp->attempts >= OtpVerification::MAX_ATTEMPTS) {
                $otp->update(['status' => 'exhausted']);
                AccessLog::record(AccessLog::LINK_REQUESTED, [
                    'ip'          => $request->ip(),
                    'user_agent'  => substr($request->userAgent(), 0, 255),
                    'email_hash'  => $otp->email_hash,
                    'device_hash' => $deviceHash,
                    'order_id'    => $otp->order_id ?? '',
                    'result'      => 'REGEN_OTP_EXHAUSTED',
                    'risk_score'  => 0,
                ]);
                return response()->json(['status' => 'error', 'type' => 'otp_exhausted']);
            }

            return response()->json([
                'status'        => 'error',
                'type'          => 'otp_invalid',
                'attempts_left' => OtpVerification::MAX_ATTEMPTS - $otp->attempts,
            ]);
        }

        // ── OTP correct — resolve the same order set and issue links ──────────
        $orderIds  = !empty($otp->order_ids) ? $otp->order_ids : [$otp->order_id];
        $purchases = TenderPurchase::whereIn('order_number', $orderIds)->get();

        if ($purchases->isEmpty()) {
            $otp->update(['status' => 'expired']);
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        $otp->update(['status' => 'verified']);

        $emailHash = $otp->email_hash;
        $risk      = $this->riskScore($request, $emailHash);
        $logMeta   = [
            'ip'          => $request->ip(),
            'user_agent'  => substr($request->userAgent(), 0, 255),
            'email_hash'  => $emailHash,
            'device_hash' => $deviceHash,
            'order_id'    => '',
        ];

        return $this->issueRecoveryLinks($purchases, $emailHash, $request, $be, $logMeta, $risk);
    }

    public function resendRegenerateOtp(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'session_token' => 'required|string|size:36',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        $otp = OtpVerification::where('session_token', $request->input('session_token'))
            ->where('channel', 'email')
            ->first();

        if (!$otp || !$otp->isUsable()) {
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        if (!$otp->canResend()) {
            return response()->json([
                'status'       => 'error',
                'type'         => 'resend_too_soon',
                'resend_after' => $otp->resendCooldownSeconds(),
            ]);
        }

        $primary = TenderPurchase::where('order_number', $otp->order_id)->first();

        if (!$primary) {
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        $rawOtp = OtpVerification::generateOtp();
        $otp->update([
            'otp_hash'       => hash('sha256', $rawOtp),
            'expires_at'     => now()->addMinutes(OtpVerification::OTP_TTL_MIN),
            'attempts'       => 0,
            'last_resend_at' => now(),
            'status'         => 'pending',
        ]);

        $currentLang = $this->getCurrentLang();
        $bs          = $currentLang->basic_setting;
        $sent        = $this->sendRecoveryOtpEmail($primary->email, $primary->first_name, $rawOtp, $bs);

        if (!$sent) {
            return response()->json(['status' => 'error', 'type' => 'email_failed']);
        }

        return response()->json([
            'status'       => 'success',
            'resend_after' => OtpVerification::RESEND_DELAY,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MODULE 3 — Email + Payment Reference
    // ══════════════════════════════════════════════════════════════════════════

    public function requestByPaymentRef(Request $request)
    {
        $currentLang = $this->getCurrentLang();
        $bs          = $currentLang->basic_setting;
        $be          = $currentLang->basic_extended;

        $emailHash  = $this->emailHash($request->input('email', ''));
        $deviceHash = $this->deviceHash($request);

        $logMeta = [
            'ip'          => $request->ip(),
            'user_agent'  => substr($request->userAgent(), 0, 255),
            'email_hash'  => $emailHash,
            'device_hash' => $deviceHash,
            'order_id'    => '',
        ];

        \Log::info('[PayRef] Request received', [
            'ip'    => $request->ip(),
            'email' => substr($request->input('email', ''), 0, 4) . '***',
            'ref'   => substr($request->input('payment_reference', ''), 0, 6) . '***',
        ]);

        // ── 1. Format validation ──────────────────────────────────────────────
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email'             => 'required|email',
            'payment_reference' => ['required', 'string', 'min:4', 'max:200', 'regex:/^[A-Za-z0-9\-_\s]+$/'],
        ]);

        if ($validator->fails()) {
            \Log::warning('[PayRef] Validation failed', ['errors' => $validator->errors()->toArray()]);
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        // ── 2. Rate limit check ───────────────────────────────────────────────
        $blocked = $this->checkRateLimit($request, $emailHash);
        if ($blocked) {
            \Log::warning('[PayRef] Rate limited', ['ip' => $request->ip()]);
            AccessLog::record(AccessLog::RATE_LIMIT_TRIGGERED, array_merge($logMeta, [
                'result'     => 'RATE_LIMITED',
                'risk_score' => 0,
            ]));
            return response()->json([
                'status'  => 'error',
                'type'    => 'rate_limited',
                'minutes' => $blocked['minutes'],
            ]);
        }

        $risk = $this->riskScore($request, $emailHash);

        // ── 3. Lookup — normalised match on email + payment_reference ───────────
        // Strip all whitespace before comparing so "ABC DEF" matches "ABCDEF"
        $normalised = strtoupper(preg_replace('/\s+/', '', trim($request->input('payment_reference'))));

        \Log::info('[PayRef] Searching purchase', ['ref_normalised' => $normalised]);

        $purchase = TenderPurchase::where('payment_status', 'Completed')
            ->whereNotNull('payment_reference')
            ->get()
            ->first(function ($p) use ($request, $normalised) {
                $storedRef = strtoupper(preg_replace('/\s+/', '', trim($p->payment_reference)));
                return $storedRef === $normalised
                    && strtolower(trim($p->email)) === strtolower(trim($request->input('email')));
            });

        // ── 4. No match — return real error ──────────────────────────────────
        if (!$purchase) {
            \Log::info('[PayRef] No match found (ref or email mismatch)', ['ip' => $request->ip()]);
            $this->incrementAttempts($request, $emailHash);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'PAYREF_NO_MATCH',
                'risk_score' => $risk,
            ]));
            return response()->json([
                'status'  => 'error',
                'type'    => 'no_match',
            ]);
        }

        \Log::info('[PayRef] Purchase matched', ['order' => $purchase->order_number]);

        // ── 4b. Suspension check ──────────────────────────────────────────────
        if ($r = $this->suspendedResponse($purchase)) {
            return $r;
        }

        // ── 4c. Recovery cap: max N link issuances per order per 24h, shared
        // across all 4 Find-My-Files methods (admin configurable) ────────────
        if ($this->regenCapApplies('payref')) {
            $regenCount = SecureToken::where('email_hash', $emailHash)
                ->where('order_id', $purchase->order_number)
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            if ($regenCount >= $this->maxRegenPerDay()) {
                AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                    'result'     => 'REGEN_LIMIT_EXCEEDED',
                    'risk_score' => $risk,
                    'order_id'   => $purchase->order_number,
                ]));
                return response()->json(['status' => 'error', 'type' => 'regen_limit']);
            }
        }

        // ── 5. Revoke previous active tokens ──────────────────────────────────
        SecureToken::where('order_id', $purchase->order_number)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // Bind the link to this browser + device + IP (same as OTP recovery), so
        // the emailed link only works in the browser that requested it.
        $sessionSecret = Str::random(40);
        $sessionHash   = hash('sha256', $sessionSecret);

        // ── 6. Issue SecureToken ──────────────────────────────────────────────
        $rawToken  = $this->generateToken($purchase, $emailHash, $request);
        $tokenHash = hash('sha256', $rawToken);

        SecureToken::create([
            'order_id'       => $purchase->order_number,
            'email_hash'     => $emailHash,
            'token_hash'     => $tokenHash,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(self::TOKEN_TTL_HOURS),
            'max_downloads'  => $this->maxDownloads(),
            'download_count' => 0,
            'status'         => 'active',
            'device_hash'    => $this->uaHash($request), // UA only (IP checked separately)
            'ip'             => $request->ip(),
            'session_secret' => $sessionHash,
        ]);

        \Log::info('[PayRef] SecureToken issued', ['order' => $purchase->order_number]);

        // ── 7. Email the download link ────────────────────────────────────────
        $downloadUrl = route('find_my_files.download', ['t' => $rawToken]);
        $emailSent   = $this->sendDownloadEmail($purchase, $downloadUrl, $be);

        \Log::info('[PayRef] Email result', [
            'order' => $purchase->order_number,
            'sent'  => $emailSent,
        ]);

        if (!$emailSent) {
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'PAYREF_EMAIL_FAILED',
                'risk_score' => $risk,
                'order_id'   => $purchase->order_number,
            ]));
            return response()->json([
                'status' => 'error',
                'type'   => 'email_failed',
            ]);
        }

        $this->resetAttempts($request, $emailHash);

        AccessLog::record(AccessLog::LINK_SENT, array_merge($logMeta, [
            'result'     => 'PAYREF_OK',
            'risk_score' => $risk,
            'order_id'   => $purchase->order_number,
        ]));

        // Drop the binding cookie on this browser so the emailed link is locked
        // to it (+ device + IP). Opening it elsewhere is refused at download.
        return response()->json([
            'status'   => 'success',
            'redirect' => route('find_my_files.link_sent'),
        ])->cookie(
            'fmf_dl',
            $sessionSecret,
            self::TOKEN_TTL_HOURS * 60,
            '/',
            null,
            $request->secure(),
            true,
            false,
            'Lax'
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MODULE 2 — Email + Phone (OTP)
    // ══════════════════════════════════════════════════════════════════════════

    private function phoneHash(string $phone): string
    {
        return hash('sha256', preg_replace('/\D/', '', $phone));
    }

    public function requestOtp(Request $request)
    {
        $currentLang = $this->getCurrentLang();

        $emailHash  = $this->emailHash($request->input('email', ''));
        $phoneHash  = $this->phoneHash($request->input('phone', ''));
        $deviceHash = $this->deviceHash($request);

        $logMeta = [
            'ip'          => $request->ip(),
            'user_agent'  => substr($request->userAgent(), 0, 255),
            'email_hash'  => $emailHash,
            'device_hash' => $deviceHash,
            'order_id'    => '',
        ];

        // ── Log every request that hits this endpoint ─────────────────────────
        \Log::info('[OTP/requestOtp] Request received', [
            'ip'    => $request->ip(),
            'email' => substr($request->input('email', ''), 0, 5) . '***',
            'phone' => substr($request->input('phone', ''), 0, 4) . '***',
        ]);

        // ── 1. Format validation ──────────────────────────────────────────────
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|email',
            'phone' => ['required', 'string', 'min:6', 'max:20', 'regex:/^\+?[\d\s\-\(\)]+$/'],
        ]);

        if ($validator->fails()) {
            \Log::warning('[OTP/requestOtp] Validation failed', ['errors' => $validator->errors()->toArray()]);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'INVALID_INPUT',
                'risk_score' => 0,
            ]));
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        // ── 2. Rate limit check ───────────────────────────────────────────────
        $blocked = $this->checkRateLimit($request, $emailHash);
        if ($blocked) {
            \Log::warning('[OTP/requestOtp] Rate limited', ['ip' => $request->ip()]);
            AccessLog::record(AccessLog::RATE_LIMIT_TRIGGERED, array_merge($logMeta, [
                'result'     => 'RATE_LIMITED',
                'risk_score' => 0,
            ]));
            return response()->json([
                'status'  => 'error',
                'type'    => 'rate_limited',
                'minutes' => $blocked['minutes'],
            ]);
        }

        // ── 3. Neutral lookup (across every tender the buyer ticked) ──────────
        $risk    = $this->riskScore($request, $emailHash);
        $tenders = $this->resolveTenders($request);

        $matched = TenderPurchase::query()
            ->when($tenders->isNotEmpty(), fn ($q) => $q->whereIn('tender_id', $tenders->pluck('id')))
            ->get()
            ->filter(function ($p) use ($request) {
                return strtolower(trim($p->email)) === strtolower(trim($request->input('email')))
                    && TenderPurchase::phoneMatches($p->phone_number, $request->input('phone'));
            })
            ->values();

        // No email+phone match at all → explicit "not found" (product choice:
        // clearer UX over anti-enumeration). Still rate-limited to slow probing.
        if ($matched->isEmpty()) {
            \Log::info('[OTP/requestOtp] No matching purchase (email+phone)', ['ip' => $request->ip()]);
            $this->incrementAttempts($request, $emailHash);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'OTP_NO_MATCH',
                'risk_score' => $risk,
            ]));
            return response()->json(['status' => 'error', 'type' => 'no_match']);
        }

        // ── 4. Keep only downloadable orders (completed, not suspended) ───────
        $completed = $matched->filter(fn ($p) => $p->payment_status === 'Completed')->values();

        if ($completed->isEmpty()) {
            \Log::info('[OTP/requestOtp] Matched but none completed', [
                'orders' => $matched->pluck('order_number')->all(),
            ]);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'OTP_PAYMENT_PENDING',
                'risk_score' => $risk,
                'order_id'   => $matched->first()->order_number,
            ]));
            return response()->json(['status' => 'error', 'type' => 'payment_pending']);
        }

        // Drop suspended orders. Every remaining ORDER gets its own link — two
        // separate purchases on the same tender (e.g. different buyers/modules
        // under one shared email+phone) are distinct and each is recoverable.
        // The cards are labelled with Order · Name · Company, so duplicate tender
        // titles are unambiguous.
        $usable = $completed
            ->reject(fn ($p) => $p->isSuspended())
            ->sortByDesc('id')
            ->values();

        if ($usable->isEmpty()) {
            return $this->suspendedResponse($completed->first())
                ?? response()->json(['status' => 'error', 'type' => 'suspended']);
        }

        // Primary order carries the OTP identity + receives the SMS; every usable
        // order rides along in order_ids so one OTP unlocks the whole selection.
        $purchase = $usable->first();
        $orderIds = $usable->pluck('order_number')->all();

        \Log::info('[OTP/requestOtp] Purchases matched and completed', [
            'primary' => $purchase->order_number,
            'orders'  => $orderIds,
        ]);

        // ── 4b. Invalidate any previous pending OTP sessions for these orders ─
        OtpVerification::whereIn('order_id', $orderIds)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // ── 5. Generate & store OTP ───────────────────────────────────────────
        $rawOtp       = OtpVerification::generateOtp();
        $sessionToken = Str::uuid()->toString();
        $e164Phone    = $purchase->e164Phone();               // E.164 for the SMS gateway
        $maskedPhone  = OtpVerification::maskPhone($e164Phone);

        OtpVerification::create([
            'session_token'  => $sessionToken,
            'email_hash'     => $emailHash,
            'phone_hash'     => $phoneHash,
            'otp_hash'       => hash('sha256', $rawOtp),
            'order_id'       => $purchase->order_number,
            'order_ids'      => $orderIds,
            'expires_at'     => now()->addMinutes(OtpVerification::OTP_TTL_MIN),
            'attempts'       => 0,
            'last_resend_at' => now(),
            'status'         => 'pending',
            'ip'             => $request->ip(),
            'device_hash'    => $deviceHash,
            'masked_phone'   => $maskedPhone,
        ]);

        \Log::info('[OTP/requestOtp] OTP record created', [
            'order'       => $purchase->order_number,
            'masked_phone' => $maskedPhone,
        ]);

        // ── 6. Send SMS ───────────────────────────────────────────────────────
        $appName = $this->brandName();
        $message = "{$appName}: Your verification code is {$rawOtp}. Valid for " . OtpVerification::OTP_TTL_MIN . " minutes. Do not share this code.";

        \Log::info('[OTP/requestOtp] Attempting SMS send', ['to' => substr($e164Phone, 0, 6) . '***']);

        try {
            $sms  = app(SmsGatewayInterface::class);
            $sent = $sms->send($e164Phone, $message);
        } catch (\Throwable $e) {
            $sent = false;
            \Log::error('[OTP/requestOtp] SMS threw exception', [
                'exception' => $e->getMessage(),
                'order'     => $purchase->order_number,
            ]);
        }

        if (!$sent) {
            OtpVerification::where('session_token', $sessionToken)->update(['status' => 'expired']);
            \Log::error('[OTP/requestOtp] SMS send failed', ['order' => $purchase->order_number]);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'OTP_SMS_FAILED',
                'risk_score' => $risk,
                'order_id'   => $purchase->order_number,
            ]));
            return response()->json(['status' => 'error', 'type' => 'sms_failed']);
        }

        \Log::info('[OTP/requestOtp] SMS sent successfully', ['order' => $purchase->order_number]);
        AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
            'result'     => 'OTP_SENT',
            'risk_score' => $risk,
            'order_id'   => $purchase->order_number,
        ]));

        return response()->json([
            'status'        => 'success',
            'otp_sent'      => true,
            'session_token' => $sessionToken,
            'masked_phone'  => $maskedPhone,
            'resend_after'  => OtpVerification::RESEND_DELAY,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $currentLang = $this->getCurrentLang();
        $be          = $currentLang->basic_extended;
        $deviceHash  = $this->deviceHash($request);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'session_token' => 'required|string|size:36',
            'otp_code'      => 'required|string|size:6|regex:/^\d{6}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        $otp = OtpVerification::where('session_token', $request->input('session_token'))->first();

        if (!$otp) {
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        if (!$otp->isUsable()) {
            $type = ($otp->status === 'exhausted') ? 'otp_exhausted' : 'otp_expired';
            return response()->json(['status' => 'error', 'type' => $type]);
        }

        // ── Wrong OTP ─────────────────────────────────────────────────────────
        if (!$otp->verifyOtp($request->input('otp_code'))) {
            $otp->increment('attempts');

            if ($otp->attempts >= OtpVerification::MAX_ATTEMPTS) {
                $otp->update(['status' => 'exhausted']);
                AccessLog::record(AccessLog::LINK_REQUESTED, [
                    'ip'          => $request->ip(),
                    'user_agent'  => substr($request->userAgent(), 0, 255),
                    'email_hash'  => $otp->email_hash,
                    'device_hash' => $deviceHash,
                    'order_id'    => $otp->order_id ?? '',
                    'result'      => 'OTP_EXHAUSTED',
                    'risk_score'  => 0,
                ]);
                return response()->json(['status' => 'error', 'type' => 'otp_exhausted']);
            }

            return response()->json([
                'status'        => 'error',
                'type'          => 'otp_invalid',
                'attempts_left' => OtpVerification::MAX_ATTEMPTS - $otp->attempts,
            ]);
        }

        // ── OTP correct ───────────────────────────────────────────────────────
        // One verification can cover several tenders; order_ids holds them all
        // (older single-order records fall back to order_id).
        $orderIds  = !empty($otp->order_ids) ? $otp->order_ids : [$otp->order_id];
        $purchases = TenderPurchase::whereIn('order_number', $orderIds)->get();

        if ($purchases->isEmpty()) {
            $otp->update(['status' => 'expired']);
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        $otp->update(['status' => 'verified']);

        $emailHash = $otp->email_hash;
        $downloads = [];      // {title, url} per tender, for the browser + email
        $anyCapExceeded = false;

        // Session/device/IP binding: one secret for this verification, stored
        // (hashed) on every token and dropped as an httpOnly cookie on THIS
        // browser. The download is then locked to this browser + device + IP.
        $sessionSecret = Str::random(40);
        $sessionHash   = hash('sha256', $sessionSecret);

        foreach ($purchases as $purchase) {
            // Skip anything no longer downloadable (not completed / suspended).
            if ($purchase->payment_status !== 'Completed' || $purchase->isSuspended()) {
                continue;
            }

            // Recovery cap: max N link issuances per order per 24h, shared
            // across all 4 Find-My-Files methods (admin configurable) — scoped
            // to THIS order, so one capped order doesn't block the rest.
            if ($this->regenCapApplies('otp')) {
                $regenCount = SecureToken::where('email_hash', $emailHash)
                    ->where('order_id', $purchase->order_number)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->count();

                if ($regenCount >= $this->maxRegenPerDay()) {
                    $anyCapExceeded = true;
                    AccessLog::record(AccessLog::LINK_REQUESTED, [
                        'ip'          => $request->ip(),
                        'user_agent'  => substr($request->userAgent(), 0, 255),
                        'email_hash'  => $emailHash,
                        'device_hash' => $deviceHash,
                        'order_id'    => $purchase->order_number,
                        'result'      => 'REGEN_LIMIT_EXCEEDED',
                        'risk_score'  => 0,
                    ]);
                    continue;
                }
            }

            // Fresh token per order → each tender keeps its own open-counter.
            SecureToken::where('order_id', $purchase->order_number)
                ->where('status', 'active')
                ->update(['status' => 'revoked']);

            $rawToken = $this->generateToken($purchase, $emailHash, $request);

            SecureToken::create([
                'order_id'       => $purchase->order_number,
                'email_hash'     => $emailHash,
                'token_hash'     => hash('sha256', $rawToken),
                'issued_at'      => now(),
                'expires_at'     => now()->addHours(self::TOKEN_TTL_HOURS),
                'max_downloads'  => $this->maxDownloads(),
                'download_count' => 0,
                'status'         => 'active',
                'device_hash'    => $this->uaHash($request), // UA only (IP checked separately)
                'ip'             => $request->ip(),
                'session_secret' => $sessionHash,
            ]);

            $downloads[] = [
                'title'   => optional($purchase->tender)->title ?: $purchase->order_number,
                'url'     => route('find_my_files.download', ['t' => $rawToken]),
                // Per-order buyer details — names/companies can differ across the
                // purchases sharing this email+phone, so label each link with the
                // one that actually placed it (year-end archiving clarity).
                'name'    => trim($purchase->first_name . ' ' . $purchase->last_name),
                'company' => (string) $purchase->company_name,
                'order'   => $purchase->order_number,
            ];

            AccessLog::record(AccessLog::LINK_SENT, [
                'ip'          => $request->ip(),
                'user_agent'  => substr($request->userAgent(), 0, 255),
                'email_hash'  => $emailHash,
                'device_hash' => $deviceHash,
                'order_id'    => $purchase->order_number,
                'result'      => 'OTP_VERIFIED_OK',
                'risk_score'  => 0,
            ]);
        }

        // Every matched order turned out non-issuable after verification.
        if (empty($downloads)) {
            if ($r = $this->suspendedResponse($purchases->first(fn ($p) => $p->isSuspended()))) {
                return $r;
            }
            if ($anyCapExceeded) {
                return response()->json(['status' => 'error', 'type' => 'regen_limit']);
            }
            return response()->json(['status' => 'error', 'type' => 'suspended']);
        }

        $emailSent = $this->sendDownloadEmail($purchases->first(), $downloads, $be);
        \Log::info('[OTP/verifyOtp] Email result', [
            'orders' => array_column($downloads, 'title'),
            'sent'   => $emailSent,
        ]);

        $this->resetAttempts($request, $emailHash);

        // Drop the binding cookie on this browser (httpOnly, lives as long as the
        // links). The download routes require it to match.
        return response()->json([
            'status'    => 'success',
            'downloads' => $downloads,
        ])->cookie(
            'fmf_dl',
            $sessionSecret,
            self::TOKEN_TTL_HOURS * 60,
            '/',
            null,
            $request->secure(), // secure only over HTTPS
            true,               // httpOnly
            false,
            'Lax'
        );
    }

    public function resendOtp(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'session_token' => 'required|string|size:36',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'type' => 'validation']);
        }

        $otp = OtpVerification::where('session_token', $request->input('session_token'))->first();

        if (!$otp || !$otp->isUsable()) {
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        if (!$otp->canResend()) {
            return response()->json([
                'status'       => 'error',
                'type'         => 'resend_too_soon',
                'resend_after' => $otp->resendCooldownSeconds(),
            ]);
        }

        $purchase = TenderPurchase::where('order_number', $otp->order_id)->first();

        if (!$purchase) {
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        $rawOtp = OtpVerification::generateOtp();
        $otp->update([
            'otp_hash'       => hash('sha256', $rawOtp),
            'expires_at'     => now()->addMinutes(OtpVerification::OTP_TTL_MIN),
            'attempts'       => 0,
            'last_resend_at' => now(),
            'status'         => 'pending',
        ]);

        $appName = $this->brandName();
        $message = "{$appName}: Your new verification code is {$rawOtp}. Valid for " . OtpVerification::OTP_TTL_MIN . " minutes.";

        $sms  = app(SmsGatewayInterface::class);
        $sent = $sms->send($purchase->e164Phone(), $message);

        if (!$sent) {
            return response()->json(['status' => 'error', 'type' => 'sms_failed']);
        }

        return response()->json([
            'status'       => 'success',
            'resend_after' => OtpVerification::RESEND_DELAY,
        ]);
    }
}
