<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenderBlacklist extends Model
{
    protected $fillable = [
        'email',
        'phone_number',
        'ip_address',
        'company_name',
        'reason',
    ];

    /**
     * Is this buyer blacklisted? Matches on any provided identifier
     * (email / phone / IP), case-insensitive and trimmed.
     */
    public static function matches(?string $email, ?string $phone, ?string $ip): bool
    {
        $email = strtolower(trim((string) $email));
        $phone = preg_replace('/\s+/', '', (string) $phone);
        $ip    = trim((string) $ip);

        if ($email === '' && $phone === '' && $ip === '') {
            return false;
        }

        return static::query()
            ->when($email !== '', fn($q) => $q->orWhereRaw('LOWER(TRIM(email)) = ?', [$email]))
            ->when($phone !== '', fn($q) => $q->orWhereRaw("REPLACE(phone_number,' ','') = ?", [$phone]))
            ->when($ip !== '', fn($q) => $q->orWhere('ip_address', $ip))
            ->exists();
    }
}
