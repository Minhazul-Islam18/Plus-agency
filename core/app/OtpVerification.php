<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'session_token',
        'email_hash',
        'phone_hash',
        'channel',
        'otp_hash',
        'order_id',
        'order_ids',
        'expires_at',
        'attempts',
        'last_resend_at',
        'status',
        'ip',
        'device_hash',
        'masked_phone',
    ];

    protected $casts = [
        'expires_at'     => 'datetime',
        'last_resend_at' => 'datetime',
        'order_ids'      => 'array',
    ];

    const MAX_ATTEMPTS  = 3;
    const OTP_TTL_MIN   = 10;
    const RESEND_DELAY  = 60; // seconds before resend is allowed

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        if ($this->isExpired()) {
            $this->update(['status' => 'expired']);
            return false;
        }
        return true;
    }

    /**
     * Seconds elapsed since the last (re)send. Uses raw timestamps because
     * Carbon 3's diffInSeconds() is signed — now()->diffInSeconds($past) returns
     * a NEGATIVE value, which previously broke both the cooldown gate and the
     * remaining-seconds figure (e.g. showed "168s").
     */
    private function secondsSinceLastResend(): int
    {
        if (!$this->last_resend_at) {
            return PHP_INT_MAX;
        }
        return now()->getTimestamp() - $this->last_resend_at->getTimestamp();
    }

    public function canResend(): bool
    {
        return $this->secondsSinceLastResend() >= self::RESEND_DELAY;
    }

    public function resendCooldownSeconds(): int
    {
        return max(0, self::RESEND_DELAY - $this->secondsSinceLastResend());
    }

    public function verifyOtp(string $rawOtp): bool
    {
        return hash('sha256', $rawOtp) === $this->otp_hash;
    }

    public static function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        $len    = strlen($digits);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return substr($phone, 0, 3) . str_repeat('*', max(0, strlen($phone) - 5)) . substr($phone, -2);
    }

    /**
     * "j***@domain.com" — first character of the local part visible, rest
     * masked, domain untouched.
     */
    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2 || $parts[0] === '') {
            return str_repeat('*', strlen($email));
        }
        [$local, $domain] = $parts;
        $visible = substr($local, 0, 1);
        return $visible . str_repeat('*', max(1, strlen($local) - 1)) . '@' . $domain;
    }
}
