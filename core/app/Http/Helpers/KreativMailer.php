<?php

namespace App\Http\Helpers;

use App\EmailTemplate;
use App\Language;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class KreativMailer {

    /**
     * Resolve a basic_settings image filename to an absolute filesystem
     * path, trying every location the app has stored logos in historically
     * (same candidate list as TenderController::generateInvoice's PDF logo
     * embedding). Returns null if the file can't be found anywhere.
     */
    public static function resolveAssetPath(?string $filename): ?string {
        if (empty($filename)) {
            return null;
        }

        $candidates = [
            storage_path('app/public/front/img/' . $filename),
            base_path(FRONT_IMG_PUBLIC_DIR . $filename),
            base_path(FRONT_IMG_DIR . $filename),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public function mailFromAdmin($data) {
        $temp = EmailTemplate::where('email_type', '=', $data['templateType'])->first();

        // Missing template (e.g. not seeded on this environment) → log and stop
        // instead of a fatal null-property access, so the caller can report a
        // clean failure to the user.
        if (!$temp) {
            \Log::error('[KreativMailer] Email template missing', [
                'templateType' => $data['templateType'] ?? null,
            ]);
            throw new \RuntimeException('Email template not found: ' . ($data['templateType'] ?? ''));
        }

        $body = $temp->email_body;
        if (array_key_exists('customer_name', $data)) {
            $body = preg_replace("/{customer_name}/", $data['customer_name'], $body);
        }
        if (array_key_exists('customer_username', $data)) {
            $body = preg_replace("/{customer_username}/", $data['customer_username'], $body);
        }
        if (array_key_exists('package_name', $data)) {
            $body = preg_replace("/{package_name}/", $data['package_name'], $body);
        }
        if (array_key_exists('cause_name', $data)) {
            $body = preg_replace("/{cause_name}/", $data['cause_name'], $body);
        }
        if (array_key_exists('course_name', $data)) {
            $body = preg_replace("/{course_name}/", $data['course_name'], $body);
        }
        if (array_key_exists('tender_name', $data)) {
            $body = preg_replace("/{tender_name}/", $data['tender_name'], $body);
        }
        if (array_key_exists('download_url', $data)) {
            $body = preg_replace("/{download_url}/", $data['download_url'], $body);
        }
        // HTML block of one-or-more titled download buttons (tender multi-download).
        // str_replace so the HTML is never treated as a regex backreference.
        if (array_key_exists('download_list', $data)) {
            $body = str_replace('{download_list}', $data['download_list'], $body);
        }
        if (array_key_exists('expires_at', $data)) {
            $body = preg_replace("/{expires_at}/", $data['expires_at'], $body);
        }
        if (array_key_exists('max_downloads', $data)) {
            $body = preg_replace("/{max_downloads}/", $data['max_downloads'], $body);
        }
        if (array_key_exists('event_name', $data)) {
            $body = preg_replace("/{event_name}/", $data['event_name'], $body);
        }
        if (array_key_exists('ticket_id', $data)) {
            $body = preg_replace("/{ticket_id}/", $data['ticket_id'], $body);
        }
        if (array_key_exists('activation_date', $data)) {
            $body = preg_replace("/{activation_date}/", $data['activation_date'], $body);
        }
        if (array_key_exists('expire_date', $data)) {
            $body = preg_replace("/{expire_date}/", $data['expire_date'], $body);
        }
        if (array_key_exists('order_number', $data)) {
            $body = preg_replace("/{order_number}/", $data['order_number'], $body);
        }
        if (array_key_exists('order_link', $data)) {
            $body = preg_replace("/{order_link}/", $data['order_link'], $body);
        }
        if (array_key_exists('verification_link', $data)) {
            $body = preg_replace("/{verification_link}/", $data['verification_link'], $body);
        }
        if (array_key_exists('remaining_days', $data)) {
            $body = preg_replace("/{remaining_days}/", $data['remaining_days'], $body);
        }
        if (array_key_exists('current_package_name', $data)) {
            $body = preg_replace("/{current_package_name}/", $data['current_package_name'], $body);
        }
        if (array_key_exists('packages_link', $data)) {
            $body = preg_replace("/{packages_link}/", $data['packages_link'], $body);
        }
        if (array_key_exists('expired_package', $data)) {
            $body = preg_replace("/{expired_package}/", $data['expired_package'], $body);
        }
        if (array_key_exists('website_title', $data)) {
            $body = preg_replace("/{website_title}/", $data['website_title'], $body);
        }
        if (array_key_exists('contact_email', $data)) {
            $body = preg_replace("/{contact_email}/", $data['contact_email'], $body);
        }
        if (array_key_exists('contact_subject', $data)) {
            $body = preg_replace("/{contact_subject}/", $data['contact_subject'], $body);
        }
        if (array_key_exists('contact_message', $data)) {
            $body = str_replace('{contact_message}', $data['contact_message'], $body);
        }
        if (array_key_exists('admin_reply_message', $data)) {
            $body = str_replace('{admin_reply_message}', $data['admin_reply_message'], $body);
        }
        if (array_key_exists('otp_code', $data)) {
            $body = preg_replace("/{otp_code}/", $data['otp_code'], $body);
        }
        if (array_key_exists('otp_ttl_minutes', $data)) {
            $body = preg_replace("/{otp_ttl_minutes}/", $data['otp_ttl_minutes'], $body);
        }
        if (array_key_exists('admin_name', $data)) {
            $body = preg_replace("/{admin_name}/", $data['admin_name'], $body);
        }
        if (array_key_exists('admin_email', $data)) {
            $body = preg_replace("/{admin_email}/", $data['admin_email'], $body);
        }
        if (array_key_exists('activation_link', $data)) {
            $body = preg_replace("/{activation_link}/", $data['activation_link'], $body);
        }
        if (array_key_exists('temporary_password', $data)) {
            $body = preg_replace("/{temporary_password}/", $data['temporary_password'], $body);
        }
        if (array_key_exists('expiry_date_time', $data)) {
            $body = preg_replace("/{expiry_date_time}/", $data['expiry_date_time'], $body);
        }
        if (array_key_exists('login_url', $data)) {
            $body = preg_replace("/{login_url}/", $data['login_url'], $body);
        }
        if (array_key_exists('logo_url', $data)) {
            $body = preg_replace("/{logo_url}/", $data['logo_url'], $body);
        }
        if (array_key_exists('changed_at', $data)) {
            $body = preg_replace("/{changed_at}/", $data['changed_at'], $body);
        }
        if (array_key_exists('locked_until', $data)) {
            $body = preg_replace("/{locked_until}/", $data['locked_until'], $body);
        }
        // Admin-authored freeform text (newsletter subject/content) — str_replace,
        // never preg_replace, so a stray '$1' or backslash in what the admin typed
        // can't be misread as a regex backreference in the replacement.
        if (array_key_exists('newsletter_subject', $data)) {
            $body = str_replace('{newsletter_subject}', $data['newsletter_subject'], $body);
        }
        if (array_key_exists('newsletter_content', $data)) {
            $body = str_replace('{newsletter_content}', $data['newsletter_content'], $body);
        }
        if (array_key_exists('unsubscribe_link', $data)) {
            $body = str_replace('{unsubscribe_link}', $data['unsubscribe_link'], $body);
        }

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $be = $currentLang->basic_extended;

        // Replace {placeholder} tokens in the subject the same way as the body
        $subject = $temp->email_subject;
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $subject = str_replace('{' . $key . '}', $value, $subject);
            }
        }

        $mail = new PHPMailer(true);


        if ($be->is_smtp == 1) {
            try {

                $mail->isSMTP();
                $mail->Host       = $be->smtp_host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $be->smtp_username;
                $mail->Password   = $be->smtp_password;
                $mail->SMTPSecure = $be->encryption;
                $mail->Port       = $be->smtp_port;

            } catch (Exception $e) { }
        }

        try {

            $mail->CharSet = 'UTF-8';

            //Recipients
            $mail->setFrom($be->from_mail, $be->from_name);
            $mail->addAddress($data['toMail'], $data['toName']);

            // Attachments
            if (array_key_exists('attachment', $data) && $data['type'] == 'productOrder') {
                $mail->addAttachment(FRONT_INVOICES_PATH . 'product/' . $data['attachment']);
            } elseif (array_key_exists('attachment', $data) && $data['type'] == 'packageSubscription') {
                $mail->addAttachment(FRONT_INVOICES_PATH . $data['attachment']);
            } elseif (array_key_exists('attachment', $data) && $data['type'] == 'packageOrder') {
                $mail->addAttachment(FRONT_INVOICES_PATH . $data['attachment']);
            } elseif (array_key_exists('attachment', $data) && $data['type'] == 'courseEnroll') {
                $mail->addAttachment(FRONT_INVOICES_PATH . 'course/' . $data['attachment']);
            } elseif (array_key_exists('attachment', $data) && $data['type'] == 'donation') {
                $mail->addAttachment(FRONT_INVOICES_PATH . $data['attachment']);
            } elseif (array_key_exists('attachment', $data) && $data['type'] == 'tenderDownloadLink') {
                if (file_exists($data['attachment'])) {
                    $mail->addAttachment($data['attachment'], $data['attachmentName'] ?? basename($data['attachment']));
                }
            }

            // Logo embedded inline (cid:applogo) rather than referenced by a
            // remote URL — a recipient's mail client fetches remote images
            // over the public internet, which fails on local/dev domains and
            // is blocked by default in many clients anyway. Shipping the
            // bytes with the email guarantees it renders.
            if (array_key_exists('logo_path', $data) && file_exists($data['logo_path'])) {
                if (strtolower(pathinfo($data['logo_path'], PATHINFO_EXTENSION)) === 'svg') {
                    // Most email clients (Outlook chief among them) don't
                    // render inline SVG at all, no matter how it's embedded —
                    // a format limitation, not a sizing/markup issue. Drop
                    // the <img> so it degrades to the text branding next to
                    // it instead of showing a broken image.
                    $body = preg_replace('/<img[^>]*src="cid:applogo"[^>]*>/', '', $body);
                } else {
                    $mail->addEmbeddedImage($data['logo_path'], 'applogo');
                }
            }

            // Content
            $mail->isHTML(true);

            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();

            return true;
        } catch (Exception $e) {
            \Log::error('[KreativMailer] Send failed', [
                'templateType' => $data['templateType'] ?? null,
                'toMail'       => $data['toMail'] ?? null,
                'error'        => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Fires a one-off test email through whatever SMTP values are currently in
     * the admin form (not necessarily saved yet), so "Send Test Email" can
     * verify host/port/username/password before committing them. Works for
     * any SMTP provider, including Resend's SMTP relay (smtp.resend.com,
     * username "resend", password = API key).
     */
    public static function sendTestSmtp(array $config, string $toMail): array
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp_username'];
            $mail->Password   = $config['smtp_password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['smtp_port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($config['from_mail'], $config['from_name']);
            $mail->addAddress($toMail);

            $mail->isHTML(true);
            $mail->Subject = 'SMTP test email';
            $mail->Body    = '<p>This is a test email sent from your admin panel\'s SMTP settings. If you received this, SMTP is configured correctly.</p>';

            $mail->send();

            return ['success' => true, 'message' => 'Test email sent successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $mail->ErrorInfo ?: $e->getMessage()];
        }
    }

}
