<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /**
     * Special page types. Each may exist only once per language.
     * Normal pages (null / '') are unlimited.
     */
    const SPECIAL_TYPES = [
        'terms'        => 'Terms & Conditions',
        'privacy'      => 'Privacy Policy',
        'legal_notice' => 'Legal Notice',
    ];

    public function language() {
        return $this->belongsTo('App\Language');
    }

    /**
     * Resolve the page assigned to a special type for a given language.
     */
    public static function forType(string $type, int $languageId)
    {
        return static::where('language_id', $languageId)
            ->where('page_type', $type)
            ->where('status', 1)
            ->first();
    }
}
