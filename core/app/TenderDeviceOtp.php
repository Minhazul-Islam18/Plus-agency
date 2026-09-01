<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One-time email code authorizing a not-yet-recognized device onto an
 * order's secure download link (see TenderDeviceRegistration). Separate
 * from OtpVerification — that table recovers/reissues a lost link via
 * email+phone; this one is for a link the buyer already legitimately has.
 */
class TenderDeviceOtp extends Model
{
    protected $fillable = [
        'order_id',
        'device_hash',
        'email_hash',
        'otp_hash',
        'attempts',
        'expires_at',
        'last_resend_at',
        'status',
        'ip',
    ];

    protected $casts = [
        'expires_at'     => 'datetime',
        'last_resend_at' => 'datetime',
    ];

    const MAX_ATTEMPTS = 3;
    const OTP_TTL_MIN  = 10;
    const RESEND_DELAY = 60;

    public function isUsable(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        if ($this->expires_at->isPast()) {
            $this->update(['status' => 'expired']);
            return false;
        }
        return true;
    }

    public function verifyOtp(string $rawOtp): bool
    {
        return hash('sha256', $rawOtp) === $this->otp_hash;
    }

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
}
