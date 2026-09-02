<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Free, no-key city/country/ISP lookup for an IP (ip-api.com) — used only to
 * show admins roughly where a device was verified/used from. City-level
 * accuracy at best, never a match/blocking criterion. Fails silently
 * (returns null) on any error/timeout/rate-limit so it can never block a
 * real device-recognition or download flow.
 */
class GeoIpLookup
{
    public static function lookup(?string $ip): ?array
    {
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        // v4: bumped when the isp value started falling back to the `as`
        // field for generic connection-type labels (e.g. "FTTX") so stale
        // cached entries from before don't silently keep the useless value.
        $cacheKey = 'geoip:v4:' . $ip;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($ip) {
            try {
                $response = Http::timeout(2)
                    ->get("http://ip-api.com/json/{$ip}", ['fields' => 'status,country,city,isp,org,as']);

                if (!$response->ok()) {
                    return null;
                }

                $data = $response->json();
                if (($data['status'] ?? null) !== 'success') {
                    return null;
                }

                return [
                    'city'    => $data['city'] ?? null,
                    'country' => $data['country'] ?? null,
                    'isp'     => self::resolveIsp($data),
                ];
            } catch (\Throwable $e) {
                Log::warning('GeoIpLookup failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * ip-api's `isp` field is sometimes just a generic connection-type
     * label instead of a brand name — e.g. "FTTX" for a fiber line in
     * Burkina Faso, with the real provider ("Orange Burkina Faso") only
     * present in the `as` field's free-text description. Prefer `org`
     * when set, then a non-generic `isp`, then fall back to stripping the
     * "ASxxxxx " prefix off `as`.
     */
    private static function resolveIsp(array $data): ?string
    {
        $genericLabels = [
            'fttx', 'ftth', 'fttb', 'fttc', 'dsl', 'adsl', 'vdsl', 'cable',
            'fiber', 'fibre', 'gpon', 'wimax', 'lte', '4g', '5g',
            'broadband', 'wireless', 'mobile', 'dial-up', 'dialup',
        ];

        $org = trim((string) ($data['org'] ?? ''));
        if ($org !== '') {
            return self::cleanIsp($org);
        }

        $isp = trim((string) ($data['isp'] ?? ''));
        if ($isp !== '' && !in_array(mb_strtolower($isp), $genericLabels, true)) {
            return self::cleanIsp($isp);
        }

        $as = trim((string) ($data['as'] ?? ''));
        if ($as !== '' && preg_match('/^AS\d+\s+(.+)$/i', $as, $m)) {
            return self::cleanIsp(trim($m[1]));
        }

        return $isp !== '' ? $isp : null;
    }

    /**
     * ISP field often comes back as the legal registrant on file with the
     * regional internet registry rather than the brand name — e.g.
     * "Prodip Dhali t/a Dot Internet" for an ISP that trades as just
     * "Dot Internet". Keep only the part after "t/a"/"trading as" when
     * present; leave anything else untouched.
     */
    private static function cleanIsp(?string $isp): ?string
    {
        if (empty($isp)) {
            return $isp;
        }

        if (preg_match('/\b(?:t\/a|trading\s+as)\s+(.+)$/i', $isp, $m)) {
            return trim($m[1]);
        }

        return $isp;
    }
}
