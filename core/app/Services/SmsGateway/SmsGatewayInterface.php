<?php

namespace App\Services\SmsGateway;

interface SmsGatewayInterface
{
    /**
     * Send an SMS message to the given phone number.
     *
     * @param  string $to      E.164 or local format phone number
     * @param  string $message SMS body text
     * @return bool            true on success, false on failure
     */
    public function send(string $to, string $message): bool;
}
