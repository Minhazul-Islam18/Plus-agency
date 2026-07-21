<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenderPurchase extends Model
{
    protected $fillable = [
        'tender_id',
        'user_id',
        'order_number',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'country',
        'city',
        'company_name',
        'company_address',
        'company_registration_no',
        'qty',
        'technical_proposal_fee',
        'financial_proposal_fee',
        'summary_fee',
        'tender_notice_publication_fee',
        'currency_code',
        'payment_method',
        'gateway_type',
        'payment_status',
        'paid_at',
        'receipt',
        'invoice',
        'payment_reference',
        'access_status',
        'suspend_reason',
        'suspended_at',
    ];

    protected $casts = [
        'paid_at'      => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public function tender()
    {
        return $this->hasOne('App\Tender', 'id', 'tender_id');
    }

    /**
     * Admin has suspended this transaction: no link issuing, no download.
     */
    public function isSuspended(): bool
    {
        return $this->access_status === 'suspended';
    }

    /**
     * Canonical form of a company registration number: uppercase, alphanumeric only.
     * Duplicate detection compares canonical values, so "ab-12 " and "AB12" collide.
     */
    public static function normalizeRegNo(?string $regNo): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $regNo));
    }

    /**
     * Canonical phone digits: strip every non-digit, then strip leading zeros so
     * international ("00226…"), plus ("+226…") and national-trunk ("0…") prefixes
     * all collapse to the same significant digits. "+226 76 64 20 50",
     * "0022676642050" and "22676642050" all normalise to "22676642050".
     */
    public static function normalizePhone(?string $phone): string
    {
        return ltrim(preg_replace('/\D/', '', (string) $phone), '0');
    }

    /**
     * The stored phone as an E.164 string (e.g. "+22676642050") for the SMS
     * gateway — Twilio rejects a bare national number like "76642050". Uses this
     * order's country to supply the dialling code when the stored number lacks it.
     * Returns "" when it can't be built.
     */
    public function e164Phone(): string
    {
        $digits = static::normalizePhone($this->phone_number); // significant digits, no trunk 0
        if ($digits === '') {
            return '';
        }

        $dial = \App\Http\Helpers\Countries::dialFor($this->country); // "+226" or null
        if ($dial) {
            $cc = ltrim($dial, '+');
            // Already carries the country code → just add "+"; otherwise prepend it.
            return strpos($digits, $cc) === 0 ? '+' . $digits : '+' . $cc . $digits;
        }

        return '+' . $digits;
    }

    /**
     * Robust phone equality for buyer lookup. True when the two numbers are the
     * same canonical digits, OR when one is a national-length suffix of the other
     * (>= 8 significant digits). This tolerates a buyer who stored the full
     * country-code form ("22676642050") but later types only the national number
     * ("76642050"), and vice-versa. 8-digit floor avoids short-number collisions;
     * the OTP is always sent to the stored number, never the typed one, so a
     * suffix collision cannot leak a code to the wrong person.
     */
    public static function phoneMatches(?string $a, ?string $b): bool
    {
        $na = static::normalizePhone($a);
        $nb = static::normalizePhone($b);

        if ($na === '' || $nb === '') {
            return false;
        }
        if ($na === $nb) {
            return true;
        }

        $min = min(strlen($na), strlen($nb));
        if ($min < 8) {
            return false;
        }

        return substr($na, -$min) === substr($nb, -$min);
    }

    /**
     * Module names already paid for on a tender under a given company registration
     * number. purchased_modules stores {name, cost} (no ids), so paid-state is keyed
     * by name. Registration number is the sole buyer identity for the duplicate guard:
     * the same company cannot re-pay regardless of which email is used. An empty /
     * missing registration number never matches, so it never blocks a purchase.
     */
    public static function paidModuleNamesForReg(int $tenderId, ?string $regNo): array
    {
        $regNo = static::normalizeRegNo($regNo);
        if ($regNo === '') {
            return [];
        }

        return static::where('tender_id', $tenderId)
            ->where('payment_status', 'Completed')
            ->where('company_registration_no', $regNo)
            ->get()
            ->flatMap(function ($p) {
                $mods = json_decode($p->purchased_modules, true) ?: [];
                return collect($mods)->pluck('name');
            })
            ->map(fn($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
