<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminPanelSetting extends Model
{
    protected $fillable = [
        'login_logo', 'login_bg_image', 'platform_name', 'tagline', 'features', 'copyright_text', 'max_login_attempts',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public static function maxLoginAttempts(): int
    {
        $n = (int) optional(self::first())->max_login_attempts;
        return $n > 0 ? $n : 5;
    }

    /**
     * Singleton row, created with explicit defaults on first access. Plain
     * firstOrCreate([], []) would return an in-memory instance missing the
     * migration's column defaults until re-queried (MySQL fills them on
     * INSERT, but the just-created Eloquent instance only has what was
     * explicitly passed) — so the very first page render after the table is
     * created would show blank branding. Passing the defaults here keeps the
     * freshly-created instance correct without an extra round-trip.
     */
    public static function singleton(): self
    {
        return self::firstOrCreate([], [
            // platform_name/login_logo/copyright_text are left null here
            // deliberately — they fall back to the site's own basic settings
            // (website_title/logo/copyright_text) at render time when unset.
            // tagline/features have no site-wide equivalent, so they get a
            // real default.
            'tagline' => 'Secure. Transparent. Efficient.',
            'features' => [
                ['icon' => 'fas fa-shield-alt', 'title' => 'Secure Authentication', 'desc' => 'Advanced protection for your account'],
                ['icon' => 'fas fa-folder-open', 'title' => 'Tender Management', 'desc' => 'Manage tenders with ease'],
                ['icon' => 'fas fa-credit-card', 'title' => 'Payment Tracking', 'desc' => 'Real-time payment monitoring'],
                ['icon' => 'fas fa-chart-bar', 'title' => 'Administrative Dashboard', 'desc' => 'Powerful tools for better decisions'],
            ],
            'max_login_attempts' => 5,
        ]);
    }
}
