<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * A device/browser recognized for one order's secure download link. The
 * first device to use a link is auto-trusted; every later one needs an
 * email OTP round (see TenderDeviceOtp) before it gets a row here.
 */
class TenderDeviceRegistration extends Model
{
    protected $fillable = [
        'order_id',
        'device_hash',
        'device_label',
        'device_type',
        'browser_name',
        'browser_version',
        'os_name',
        'ip',
        'network_label',
        'status',
        'is_primary',
        'validation_method',
        'otp_uses',
        'registered_at',
        'last_used_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'last_used_at'  => 'datetime',
        'is_primary'    => 'boolean',
    ];

    public function accessLogs()
    {
        return $this->hasMany('App\TenderDeviceAccessLog', 'device_registration_id')->orderByDesc('accessed_at');
    }

    /**
     * Full Font Awesome icon class (prefix + name) for a parsed browser_name
     * — display only. Named browsers use the "fab" (brand) set; the
     * fallback uses "fas" (solid) since fa-globe has no brand variant.
     */
    public static function browserIcon(?string $browserName): string
    {
        return match ($browserName) {
            'Chrome'  => 'fab fa-chrome',
            'Edge'    => 'fab fa-edge',
            'Firefox' => 'fab fa-firefox',
            'Safari'  => 'fab fa-safari',
            'Opera'   => 'fab fa-opera',
            default   => 'fas fa-globe',
        };
    }

    /** Matching brand color for browserIcon() — display only. */
    public static function browserColor(?string $browserName): string
    {
        return match ($browserName) {
            'Chrome'  => '#4285F4',
            'Edge'    => '#0078D7',
            'Firefox' => '#FF7139',
            'Safari'  => '#00A2E8',
            'Opera'   => '#FF1B2D',
            default   => '#6c757d',
        };
    }
}
