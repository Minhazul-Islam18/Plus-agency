<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'session_token',
        'email_hash',
        'phone_hash',
        'otp_hash',
        'order_id',
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

    public function canResend(): bool
    {
        if (!$this->last_resend_at) {
            return true;
        }
        return now()->diffInSeconds($this->last_resend_at) >= self::RESEND_DELAY;
    }

    public function resendCooldownSeconds(): int
    {
        if (!$this->last_resend_at) {
            return 0;
        }
        $elapsed = now()->diffInSeconds($this->last_resend_at);
        return max(0, self::RESEND_DELAY - $elapsed);
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
}
