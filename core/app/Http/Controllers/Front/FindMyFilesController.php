<?php

namespace App\Http\Controllers\Front;

use App\AccessLog;
use App\BasicExtra;
use App\Http\Controllers\Controller;
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
    const IP_LIMIT        = 10;  // per 15 min window
    const EMAIL_LIMIT     = 5;   // per hour
    const BLOCK_MINUTES   = [30, 120, 1440]; // progressive blocks: 30min → 2hr → 24hr

    // Token TTL and max downloads
    const TOKEN_TTL_HOURS    = 24;
    const MAX_DOWNLOADS      = 3;
    const MAX_REGEN_PER_DAY  = 3;

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function getCurrentLang()
    {
        if (session()->has('lang')) {
            return Language::where('code', session()->get('lang'))->first();
        }
        return Language::where('is_default', 1)->first();
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
                return [
                    'type'    => 'rate_limited',
                    'minutes' => max(1, (int) ceil(now()->diffInSeconds($record->blocked_until) / 60)),
                ];
            }
        }
        return null;
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
            $record->attempts++;
            $record->last_attempt_at = now();

            // Progressive block escalation
            if ($record->attempts >= 20) {
                $record->blocked_until = now()->addMinutes(self::BLOCK_MINUTES[2]); // 24h
            } elseif ($record->attempts >= 10) {
                $record->blocked_until = now()->addMinutes(self::BLOCK_MINUTES[1]); // 2h
            } elseif ($record->attempts >= 5) {
                $record->blocked_until = now()->addMinutes(self::BLOCK_MINUTES[0]); // 30min
            }

            $record->save();
        }
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

    private function sendDownloadEmail(TenderPurchase $purchase, string $downloadUrl, $be): bool
    {
        $mail = new PHPMailer(true);

        $subject   = 'Your Secure Download Link — Order ' . $purchase->order_number;
        $recipient = $purchase->email;
        $name      = trim($purchase->first_name . ' ' . $purchase->last_name);

        $body = view('mail.secure_download_link', [
            'purchase'     => $purchase,
            'downloadUrl'  => $downloadUrl,
            'expiresAt'    => now()->addHours(self::TOKEN_TTL_HOURS)->format('d M Y, H:i'),
            'maxDownloads' => self::MAX_DOWNLOADS,
            'fromName'     => $be->from_name ?: config('app.name'),
            'appUrl'       => config('app.url'),
        ])->render();

        try {
            if ($be->is_smtp == 1) {
                $mail->isSMTP();
                $mail->Host       = $be->smtp_host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $be->smtp_username;
                $mail->Password   = $be->smtp_password;
                $mail->SMTPSecure = $be->encryption;
                $mail->Port       = $be->smtp_port;
            }

            $mail->setFrom($be->from_mail, $be->from_name);
            $mail->addAddress($recipient, $name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();

            \Log::info('[FMF] Download email sent', [
                'order' => $purchase->order_number,
                'to'    => substr($recipient, 0, 4) . '***',
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

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function resolveTender(Request $request): ?Tender
    {
        $slug = trim($request->input('tender_slug', ''));
        if (!$slug) return null;
        return Tender::where('slug', $slug)->first();
    }

    private function scopeToPurchase(object $query, ?Tender $tender): object
    {
        if ($tender) {
            $query->where('tender_id', $tender->id);
        }
        return $query;
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
        $tenders = Tender::orderBy('title')->get(['id', 'title', 'slug']);

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersion($currentLang);
        $data['bs']          = $bs;
        $data['tender']      = $tender;
        $data['tenders']     = $tenders;

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
            'max_downloads'  => self::MAX_DOWNLOADS,
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

        // Risk score for display
        $risk = $this->riskScore($request, $token->email_hash);

        // ── Count this link open and expire if exhausted ─────────────────────
        $token->increment('download_count');

        if ($token->download_count >= $token->max_downloads) {
            $token->update(['status' => 'expired']);
        }

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
        $data['riskScore']   = $risk;
        $data['riskLabel']   = $risk < 30 ? 'Low' : ($risk < 70 ? 'Medium' : 'High');
        $data['riskColor']   = $risk < 30 ? '#16a34a' : ($risk < 70 ? '#d97706' : '#dc2626');
        $data['streamUrl']   = route('find_my_files.stream', ['t' => $raw]);
        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersion($currentLang);
        $data['bs']          = $currentLang->basic_setting;
        $data['appUrl']      = config('app.url');

        return view('front.find-my-files.download', $data);
    }

    private function downloadError($currentLang)
    {
        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersion($currentLang);
        $data['bs']          = $currentLang->basic_setting;
        return view('front.find-my-files.download_error', $data);
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

        // ── Fetch purchase + modules ──────────────────────────────────────────
        $purchase = TenderPurchase::where('order_number', $token->order_id)->first();

        if (!$purchase) {
            abort(404);
        }

        $modules = TenderModule::where('tender_id', $purchase->tender_id)
            ->where('status', 1)
            ->get();

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

        $added = 0;
        foreach ($modules as $module) {
            if (empty($module->tender_file)) {
                continue;
            }
            $modulesDir = env('FMF_MODULES_PATH', base_path('../assets/front/files/tender_modules'));
            $filePath = rtrim($modulesDir, '/') . '/' . $module->tender_file;
            if (file_exists($filePath)) {
                $ext      = pathinfo($module->tender_file, PATHINFO_EXTENSION);
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $module->name);
                $zip->addFile($filePath, $safeName . '.' . $ext);
                $added++;
            }
        }
        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            abort(404);
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
        return response()->download(
            $zipPath,
            'tender_documents_' . $token->order_id . '.zip',
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
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

        // ── 3. Find most recent completed purchase for this email (any tender) ──
        $purchase = TenderPurchase::where('payment_status', 'Completed')
            ->get()
            ->filter(function ($p) use ($request) {
                return strtolower(trim($p->email)) === strtolower(trim($request->input('email')));
            })
            ->sortByDesc('created_at')
            ->first();

        if (!$purchase) {
            $this->incrementAttempts($request, $emailHash);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'REGEN_NO_MATCH',
                'risk_score' => $risk,
            ]));
            // Neutral — same response as success
            return response()->json([
                'status'   => 'success',
                'redirect' => route('find_my_files.link_sent'),
            ]);
        }

        // ── 4. Regeneration cap: max 3 new tokens per order per 24h ──────────
        $regenCount = SecureToken::where('email_hash', $emailHash)
            ->where('order_id', $purchase->order_number)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($regenCount >= self::MAX_REGEN_PER_DAY) {
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'REGEN_LIMIT_EXCEEDED',
                'risk_score' => $risk,
                'order_id'   => $purchase->order_number,
            ]));
            return response()->json([
                'status' => 'error',
                'type'   => 'regen_limit',
            ]);
        }

        // ── 5. Revoke any current active tokens for this order ────────────────
        SecureToken::where('order_id', $purchase->order_number)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // ── 6. Issue new SecureToken ──────────────────────────────────────────
        $rawToken  = $this->generateToken($purchase, $emailHash, $request);
        $tokenHash = hash('sha256', $rawToken);

        SecureToken::create([
            'order_id'       => $purchase->order_number,
            'email_hash'     => $emailHash,
            'token_hash'     => $tokenHash,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(self::TOKEN_TTL_HOURS),
            'max_downloads'  => self::MAX_DOWNLOADS,
            'download_count' => 0,
            'status'         => 'active',
            'device_hash'    => $deviceHash,
            'ip'             => $request->ip(),
        ]);

        // ── 7. Email the new download link ────────────────────────────────────
        $downloadUrl = route('find_my_files.download', ['t' => $rawToken]);
        $emailSent   = $this->sendDownloadEmail($purchase, $downloadUrl, $be);
        \Log::info('[Regenerate] Email result', [
            'order' => $purchase->order_number,
            'sent'  => $emailSent,
        ]);

        if (!$emailSent) {
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'REGEN_EMAIL_FAILED',
                'risk_score' => $risk,
                'order_id'   => $purchase->order_number,
            ]));
            return response()->json(['status' => 'error', 'type' => 'email_failed']);
        }

        AccessLog::record(AccessLog::LINK_SENT, array_merge($logMeta, [
            'result'     => 'REGEN_OK',
            'risk_score' => $risk,
            'order_id'   => $purchase->order_number,
        ]));

        return response()->json([
            'status'   => 'success',
            'redirect' => route('find_my_files.link_sent'),
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

        // ── 5. Revoke previous active tokens ──────────────────────────────────
        SecureToken::where('order_id', $purchase->order_number)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // ── 6. Issue SecureToken ──────────────────────────────────────────────
        $rawToken  = $this->generateToken($purchase, $emailHash, $request);
        $tokenHash = hash('sha256', $rawToken);

        SecureToken::create([
            'order_id'       => $purchase->order_number,
            'email_hash'     => $emailHash,
            'token_hash'     => $tokenHash,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(self::TOKEN_TTL_HOURS),
            'max_downloads'  => self::MAX_DOWNLOADS,
            'download_count' => 0,
            'status'         => 'active',
            'device_hash'    => $deviceHash,
            'ip'             => $request->ip(),
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

        AccessLog::record(AccessLog::LINK_SENT, array_merge($logMeta, [
            'result'     => 'PAYREF_OK',
            'risk_score' => $risk,
            'order_id'   => $purchase->order_number,
        ]));

        return response()->json([
            'status'   => 'success',
            'redirect' => route('find_my_files.link_sent'),
        ]);
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

        // ── 3. Neutral lookup ─────────────────────────────────────────────────
        $risk     = $this->riskScore($request, $emailHash);
        $tender   = $this->resolveTender($request);
        $purchase = $this->scopeToPurchase(TenderPurchase::query(), $tender)
            ->get()
            ->first(function ($p) use ($request, $phoneHash) {
                return strtolower(trim($p->email))    === strtolower(trim($request->input('email')))
                    && $this->phoneHash($p->phone_number) === $phoneHash;
            });

        if (!$purchase) {
            \Log::info('[OTP/requestOtp] No matching purchase (email+phone)', ['ip' => $request->ip()]);
            $this->incrementAttempts($request, $emailHash);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'OTP_NO_MATCH',
                'risk_score' => $risk,
            ]));
            return response()->json([
                'status'        => 'success',
                'otp_sent'      => false,
                'session_token' => Str::uuid()->toString(),
                'masked_phone'  => OtpVerification::maskPhone($request->input('phone')),
                'resend_after'  => OtpVerification::RESEND_DELAY,
            ]);
        }

        // ── 4. Check payment status ───────────────────────────────────────────
        if ($purchase->payment_status !== 'Completed') {
            \Log::info('[OTP/requestOtp] Purchase not completed', [
                'order'  => $purchase->order_number,
                'status' => $purchase->payment_status,
            ]);
            AccessLog::record(AccessLog::LINK_REQUESTED, array_merge($logMeta, [
                'result'     => 'OTP_PAYMENT_PENDING',
                'risk_score' => $risk,
                'order_id'   => $purchase->order_number,
            ]));
            return response()->json([
                'status' => 'error',
                'type'   => 'payment_pending',
            ]);
        }

        \Log::info('[OTP/requestOtp] Purchase matched and completed', ['order' => $purchase->order_number]);

        // ── 4. Invalidate any previous pending OTP sessions ───────────────────
        OtpVerification::where('order_id', $purchase->order_number)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // ── 5. Generate & store OTP ───────────────────────────────────────────
        $rawOtp       = OtpVerification::generateOtp();
        $sessionToken = Str::uuid()->toString();
        $maskedPhone  = OtpVerification::maskPhone($purchase->phone_number);

        OtpVerification::create([
            'session_token'  => $sessionToken,
            'email_hash'     => $emailHash,
            'phone_hash'     => $phoneHash,
            'otp_hash'       => hash('sha256', $rawOtp),
            'order_id'       => $purchase->order_number,
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
            'masked_phone'=> $maskedPhone,
        ]);

        // ── 6. Send SMS ───────────────────────────────────────────────────────
        $appName = config('app.name');
        $message = "{$appName}: Your verification code is {$rawOtp}. Valid for " . OtpVerification::OTP_TTL_MIN . " minutes. Do not share this code.";

        \Log::info('[OTP/requestOtp] Attempting SMS send', ['to' => substr($purchase->phone_number, 0, 5) . '***']);

        try {
            $sms  = app(SmsGatewayInterface::class);
            $sent = $sms->send($purchase->phone_number, $message);
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
        $purchase = TenderPurchase::where('order_number', $otp->order_id)->first();

        if (!$purchase) {
            $otp->update(['status' => 'expired']);
            return response()->json(['status' => 'error', 'type' => 'session_invalid']);
        }

        $otp->update(['status' => 'verified']);

        SecureToken::where('order_id', $purchase->order_number)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        $emailHash   = $otp->email_hash;
        $rawToken    = $this->generateToken($purchase, $emailHash, $request);
        $tokenHash   = hash('sha256', $rawToken);

        SecureToken::create([
            'order_id'       => $purchase->order_number,
            'email_hash'     => $emailHash,
            'token_hash'     => $tokenHash,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(self::TOKEN_TTL_HOURS),
            'max_downloads'  => self::MAX_DOWNLOADS,
            'download_count' => 0,
            'status'         => 'active',
            'device_hash'    => $deviceHash,
            'ip'             => $request->ip(),
        ]);

        $downloadUrl = route('find_my_files.download', ['t' => $rawToken]);
        $emailSent   = $this->sendDownloadEmail($purchase, $downloadUrl, $be);
        \Log::info('[OTP/verifyOtp] Email result', [
            'order' => $purchase->order_number,
            'sent'  => $emailSent,
        ]);

        AccessLog::record(AccessLog::LINK_SENT, [
            'ip'          => $request->ip(),
            'user_agent'  => substr($request->userAgent(), 0, 255),
            'email_hash'  => $emailHash,
            'device_hash' => $deviceHash,
            'order_id'    => $purchase->order_number,
            'result'      => 'OTP_VERIFIED_OK',
            'risk_score'  => 0,
        ]);

        return response()->json([
            'status'   => 'success',
            'redirect' => route('find_my_files.link_sent'),
        ]);
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

        $appName = config('app.name');
        $message = "{$appName}: Your new verification code is {$rawOtp}. Valid for " . OtpVerification::OTP_TTL_MIN . " minutes.";

        $sms  = app(SmsGatewayInterface::class);
        $sent = $sms->send($purchase->phone_number, $message);

        if (!$sent) {
            return response()->json(['status' => 'error', 'type' => 'sms_failed']);
        }

        return response()->json([
            'status'       => 'success',
            'resend_after' => OtpVerification::RESEND_DELAY,
        ]);
    }
}
