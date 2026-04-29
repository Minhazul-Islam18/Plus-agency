<?php

namespace App\Console\Commands;

use App\BasicExtra;
use App\Services\SmsGateway\TwilioSmsGateway;
use Illuminate\Console\Command;

class TestTwilioSms extends Command
{
    protected $signature = 'twilio:test {phone : Phone number in E.164 format, e.g. +8801XXXXXXXXX}';
    protected $description = 'Send a test SMS via Twilio to verify credentials and number';

    public function handle(): int
    {
        $bex = BasicExtra::first();

        $sid   = $bex->twilio_account_sid ?? null;
        $token = $bex->twilio_auth_token  ?? null;
        $from  = $bex->twilio_from_number ?? null;

        if (!$sid || !$token || !$from) {
            $this->error('Missing Twilio config. Save credentials in Admin → Scripts → Twilio SMS.');
            return 1;
        }

        $this->line("SID   : " . substr($sid, 0, 6) . '...');
        $this->line("From  : {$from}");
        $this->line("To    : " . $this->argument('phone'));

        $gateway = new TwilioSmsGateway($sid, $token, $from);
        $ok = $gateway->send($this->argument('phone'), 'Test SMS from Plus Agency. Twilio is working!');

        if ($ok) {
            $this->info('SMS sent successfully.');
            return 0;
        }

        $this->error('SMS failed. Check laravel.log for details.');
        return 1;
    }
}
