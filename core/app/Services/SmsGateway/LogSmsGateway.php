<?php

namespace App\Services\SmsGateway;

use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGatewayInterface
{
    public function send(string $to, string $message): bool
    {
        Log::warning('[OTP/SMS] No SMS gateway configured. Message not sent.', [
            'to' => $to,
        ]);
        return false;
    }
}
