<?php

namespace App\Http\Controllers\Front;

use App\AccessLog;
use App\BasicExtra;
use App\Http\Controllers\Controller;
use App\Language;
use App\RateLimitAttempt;
use App\SecureToken;
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
    const TOKEN_TTL_HOURS  = 24;
    const MAX_DOWNLOADS    = 3;

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

    private function sendDownloadEmail(TenderPurchase $purchase, string $downloadUrl, $be): void
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
        } catch (\Exception $e) {
            \Log::error('[FMF] Email send failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Controller actions ─────────────────────────────────────────────────────

    public function index()
    {
        $currentLang = $this->getCurrentLang();
        $bs          = $currentLang->basic_setting;

        Config::set('captcha.sitekey', $bs->google_recaptcha_site_key);
        Config::set('captcha.secret', $bs->google_recaptcha_secret_key);

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersion($currentLang);
        $data['bs']          = $bs;

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
        $this->sendDownloadEmail($purchase, $downloadUrl, $be);

        // ── 11. Log success ───────────────────────────────────────────────────
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
        $tempDir = storage_path('app/temp');
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
            $filePath = base_path('../assets/front/files/tender_modules/' . $module->tender_file);
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
}
