<?php

namespace App\Services\SmsGateway;

use Illuminate\Support\Facades\Log;

class TwilioSmsGateway implements SmsGatewayInterface
{
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;

    public function __construct(string $accountSid, string $authToken, string $fromNumber)
    {
        $this->accountSid = $accountSid;
        $this->authToken  = $authToken;
        $this->fromNumber = $fromNumber;
    }

    public function send(string $to, string $message): bool
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => "{$this->accountSid}:{$this->authToken}",
            CURLOPT_POSTFIELDS     => http_build_query([
                'From' => $this->fromNumber,
                'To'   => $to,
                'Body' => $message,
            ]),
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
