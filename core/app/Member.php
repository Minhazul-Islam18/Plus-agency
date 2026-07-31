<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Member extends Model
{
    use InvalidatesHomeListingCache;
    public $timestamps = false;

    public function language() {
        return $this->belongsTo('App\Language');
    }

    /**
     * wa.me link built from the stored WhatsApp number, so it opens the
     * WhatsApp app (or WhatsApp Web on desktop) directly. Strips everything
     * but digits, so it works whether the admin typed "+226 76 64 20 50" or
     * "22676642050".
     */
    public function getWhatsappLinkAttribute(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->whatsapp);
        return $digits !== '' ? 'https://wa.me/' . $digits : null;
    }
}
