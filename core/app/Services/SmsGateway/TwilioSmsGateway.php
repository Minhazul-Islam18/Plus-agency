<?php

namespace App\Services\SmsGateway;

use Illuminate\Support\Facades\Log;

class TwilioSmsGateway implements SmsGatewayInterface
{
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;
    private bool $disableRiskCheck;

    /**
     * $disableRiskCheck bypasses Twilio SMS Pumping Protection (error 30453) for
     * these messages. Safe here because an OTP is only sent after the buyer's
     * email+phone match a completed purchase and pass rate limiting, so there is
     * no open endpoint for pumping fraud. Set false to re-enable SPP.
     */
    public function __construct(string $accountSid, string $authToken, string $fromNumber, bool $disableRiskCheck = true)
    {
        $this->accountSid       = $accountSid;
        $this->authToken        = $authToken;
        $this->fromNumber       = $fromNumber;
        $this->disableRiskCheck = $disableRiskCheck;
    }

    public function send(string $to, string $message): bool
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

        $fields = [
            'To'   => $to,
            'Body' => $message,
        ];

        // The sender field accepts three forms, auto-detected:
        //   • "MG…" (34-char Messaging Service SID) → MessagingServiceSid: Twilio
        //     picks the best sender per destination (e.g. the ICAGROUPE sender ID
        //     where supported, a number as fallback elsewhere). Recommended.
        //   • an alphanumeric sender ID like "ICAGROUPE" → shows the company name
        //     (only where the country supports/registers it; never US/Canada).
        //   • a phone number like "+1978…" → the number is shown.
        $from = trim($this->fromNumber);
        if (preg_match('/^MG[0-9a-f]{32}$/i', $from)) {
            $fields['MessagingServiceSid'] = $from;
        } else {
            $fields['From'] = $from;
        }
        // Skip SMS Pumping Protection for these gated OTP sends (avoids 30453).
        if ($this->disableRiskCheck) {
            $fields['RiskCheck'] = 'disable';
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => "{$this->accountSid}:{$this->authToken}",
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('[OTP/Twilio] cURL error', ['error' => $error, 'to' => substr($to, 0, 5) . '***']);
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            Log::error('[OTP/Twilio] Delivery failed', ['http_code' => $httpCode, 'response' => $response]);
            return false;
        }

        return true;
    }
}
