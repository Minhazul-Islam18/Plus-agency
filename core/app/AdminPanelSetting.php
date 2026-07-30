<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminPanelSetting extends Model
{
    protected $fillable = [
        'language_id', 'login_logo', 'login_bg_image', 'platform_name', 'tagline', 'features', 'copyright_text', 'max_login_attempts', 'lockout_duration_minutes',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    /**
     * max_login_attempts is stored per-language-row for convenience, but a
     * lockout threshold has no meaningful per-language variant — always
     * read it from the default language's row regardless of which language
     * is currently active.
     */
    public static function maxLoginAttempts(): int
    {
        $defaultLang = Language::where('is_default', 1)->first();
        $n = $defaultLang ? (int) optional(self::forLanguage($defaultLang->id))->max_login_attempts : 0;
        return $n > 0 ? $n : 5;
    }

    /**
     * Same default-language-row-only convention as maxLoginAttempts() — a
     * lockout cooldown has no meaningful per-language variant.
     */
    public static function lockoutDurationMinutes(): int
    {
        $defaultLang = Language::where('is_default', 1)->first();
        $n = $defaultLang ? (int) optional(self::forLanguage($defaultLang->id))->lockout_duration_minutes : 0;
        return $n > 0 ? $n : 30;
    }

    private static function defaultFeatures(): array
    {
        return [
            ['icon' => 'fas fa-shield-alt', 'title' => 'Secure Authentication', 'desc' => 'Advanced protection for your account'],
            ['icon' => 'fas fa-folder-open', 'title' => 'Tender Management', 'desc' => 'Manage tenders with ease'],
            ['icon' => 'fas fa-credit-card', 'title' => 'Payment Tracking', 'desc' => 'Real-time payment monitoring'],
            ['icon' => 'fas fa-chart-bar', 'title' => 'Administrative Dashboard', 'desc' => 'Powerful tools for better decisions'],
        ];
    }

    /**
     * One row per language, same pattern as BasicSetting — created with
     * explicit defaults on first access so the very first render for a
     * language isn't blank (see historical note: firstOrCreate([], [])
     * alone returns an in-memory instance missing column defaults until
     * re-queried).
     */
    public static function forLanguage(?int $languageId): self
    {
        if (empty($languageId)) {
            $languageId = optional(Language::where('is_default', 1)->first())->id;
        }

        return self::firstOrCreate(['language_id' => $languageId], [
            // platform_name/login_logo/copyright_text are left null here
            // deliberately — they fall back to the site's own basic settings
            // (website_title/logo/copyright_text) at render time when unset.
            'tagline' => 'Secure. Transparent. Efficient.',
            'features' => self::defaultFeatures(),
            'max_login_attempts' => 5,
            'lockout_duration_minutes' => 30,
        ]);
    }
}
