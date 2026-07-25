<?php

namespace App\Http\Helpers;

/**
 * Canonical country list for checkout: country name, ISO-3166 alpha-2 code and
 * international dialling code. Used to drive the country <select> and the phone
 * dialling-code <select>, and to validate both server-side — the buyer picks
 * from this list, so free-typed or spoofed values are rejected.
 */
class Countries
{
    /** @var array<int, array{name: string, iso: string, dial: string}> */
    private const LIST = [
        ['name' => 'Afghanistan', 'iso' => 'AF', 'dial' => '+93'],
        ['name' => 'Albania', 'iso' => 'AL', 'dial' => '+355'],
        ['name' => 'Algeria', 'iso' => 'DZ', 'dial' => '+213'],
        ['name' => 'Andorra', 'iso' => 'AD', 'dial' => '+376'],
        ['name' => 'Angola', 'iso' => 'AO', 'dial' => '+244'],
        ['name' => 'Argentina', 'iso' => 'AR', 'dial' => '+54'],
        ['name' => 'Armenia', 'iso' => 'AM', 'dial' => '+374'],
        ['name' => 'Australia', 'iso' => 'AU', 'dial' => '+61'],
        ['name' => 'Austria', 'iso' => 'AT', 'dial' => '+43'],
        ['name' => 'Azerbaijan', 'iso' => 'AZ', 'dial' => '+994'],
        ['name' => 'Bahamas', 'iso' => 'BS', 'dial' => '+1242'],
        ['name' => 'Bahrain', 'iso' => 'BH', 'dial' => '+973'],
        ['name' => 'Bangladesh', 'iso' => 'BD', 'dial' => '+880'],
        ['name' => 'Barbados', 'iso' => 'BB', 'dial' => '+1246'],
        ['name' => 'Belarus', 'iso' => 'BY', 'dial' => '+375'],
        ['name' => 'Belgium', 'iso' => 'BE', 'dial' => '+32'],
        ['name' => 'Belize', 'iso' => 'BZ', 'dial' => '+501'],
        ['name' => 'Benin', 'iso' => 'BJ', 'dial' => '+229'],
        ['name' => 'Bhutan', 'iso' => 'BT', 'dial' => '+975'],
        ['name' => 'Bolivia', 'iso' => 'BO', 'dial' => '+591'],
        ['name' => 'Bosnia and Herzegovina', 'iso' => 'BA', 'dial' => '+387'],
        ['name' => 'Botswana', 'iso' => 'BW', 'dial' => '+267'],
        ['name' => 'Brazil', 'iso' => 'BR', 'dial' => '+55'],
        ['name' => 'Brunei', 'iso' => 'BN', 'dial' => '+673'],
        ['name' => 'Bulgaria', 'iso' => 'BG', 'dial' => '+359'],
        ['name' => 'Burkina Faso', 'iso' => 'BF', 'dial' => '+226'],
        ['name' => 'Burundi', 'iso' => 'BI', 'dial' => '+257'],
        ['name' => 'Cambodia', 'iso' => 'KH', 'dial' => '+855'],
        ['name' => 'Cameroon', 'iso' => 'CM', 'dial' => '+237'],
        ['name' => 'Canada', 'iso' => 'CA', 'dial' => '+1'],
        ['name' => 'Cape Verde', 'iso' => 'CV', 'dial' => '+238'],
        ['name' => 'Central African Republic', 'iso' => 'CF', 'dial' => '+236'],
        ['name' => 'Chad', 'iso' => 'TD', 'dial' => '+235'],
        ['name' => 'Chile', 'iso' => 'CL', 'dial' => '+56'],
        ['name' => 'China', 'iso' => 'CN', 'dial' => '+86'],
        ['name' => 'Colombia', 'iso' => 'CO', 'dial' => '+57'],
        ['name' => 'Comoros', 'iso' => 'KM', 'dial' => '+269'],
        ['name' => 'Congo (Brazzaville)', 'iso' => 'CG', 'dial' => '+242'],
        ['name' => 'Congo (Kinshasa)', 'iso' => 'CD', 'dial' => '+243'],
        ['name' => 'Costa Rica', 'iso' => 'CR', 'dial' => '+506'],
        ['name' => "Côte d'Ivoire", 'iso' => 'CI', 'dial' => '+225'],
        ['name' => 'Croatia', 'iso' => 'HR', 'dial' => '+385'],
        ['name' => 'Cuba', 'iso' => 'CU', 'dial' => '+53'],
        ['name' => 'Cyprus', 'iso' => 'CY', 'dial' => '+357'],
        ['name' => 'Czechia', 'iso' => 'CZ', 'dial' => '+420'],
        ['name' => 'Denmark', 'iso' => 'DK', 'dial' => '+45'],
        ['name' => 'Djibouti', 'iso' => 'DJ', 'dial' => '+253'],
        ['name' => 'Dominican Republic', 'iso' => 'DO', 'dial' => '+1809'],
        ['name' => 'Ecuador', 'iso' => 'EC', 'dial' => '+593'],
        ['name' => 'Egypt', 'iso' => 'EG', 'dial' => '+20'],
        ['name' => 'El Salvador', 'iso' => 'SV', 'dial' => '+503'],
        ['name' => 'Equatorial Guinea', 'iso' => 'GQ', 'dial' => '+240'],
        ['name' => 'Eritrea', 'iso' => 'ER', 'dial' => '+291'],
        ['name' => 'Estonia', 'iso' => 'EE', 'dial' => '+372'],
        ['name' => 'Eswatini', 'iso' => 'SZ', 'dial' => '+268'],
        ['name' => 'Ethiopia', 'iso' => 'ET', 'dial' => '+251'],
        ['name' => 'Fiji', 'iso' => 'FJ', 'dial' => '+679'],
        ['name' => 'Finland', 'iso' => 'FI', 'dial' => '+358'],
        ['name' => 'France', 'iso' => 'FR', 'dial' => '+33'],
        ['name' => 'Gabon', 'iso' => 'GA', 'dial' => '+241'],
        ['name' => 'Gambia', 'iso' => 'GM', 'dial' => '+220'],
        ['name' => 'Georgia', 'iso' => 'GE', 'dial' => '+995'],
        ['name' => 'Germany', 'iso' => 'DE', 'dial' => '+49'],
        ['name' => 'Ghana', 'iso' => 'GH', 'dial' => '+233'],
        ['name' => 'Greece', 'iso' => 'GR', 'dial' => '+30'],
        ['name' => 'Guatemala', 'iso' => 'GT', 'dial' => '+502'],
        ['name' => 'Guinea', 'iso' => 'GN', 'dial' => '+224'],
        ['name' => 'Guinea-Bissau', 'iso' => 'GW', 'dial' => '+245'],
        ['name' => 'Guyana', 'iso' => 'GY', 'dial' => '+592'],
        ['name' => 'Haiti', 'iso' => 'HT', 'dial' => '+509'],
        ['name' => 'Honduras', 'iso' => 'HN', 'dial' => '+504'],
        ['name' => 'Hong Kong', 'iso' => 'HK', 'dial' => '+852'],
        ['name' => 'Hungary', 'iso' => 'HU', 'dial' => '+36'],
        ['name' => 'Iceland', 'iso' => 'IS', 'dial' => '+354'],
        ['name' => 'India', 'iso' => 'IN', 'dial' => '+91'],
        ['name' => 'Indonesia', 'iso' => 'ID', 'dial' => '+62'],
        ['name' => 'Iran', 'iso' => 'IR', 'dial' => '+98'],
        ['name' => 'Iraq', 'iso' => 'IQ', 'dial' => '+964'],
        ['name' => 'Ireland', 'iso' => 'IE', 'dial' => '+353'],
        ['name' => 'Israel', 'iso' => 'IL', 'dial' => '+972'],
        ['name' => 'Italy', 'iso' => 'IT', 'dial' => '+39'],
        ['name' => 'Jamaica', 'iso' => 'JM', 'dial' => '+1876'],
        ['name' => 'Japan', 'iso' => 'JP', 'dial' => '+81'],
        ['name' => 'Jordan', 'iso' => 'JO', 'dial' => '+962'],
        ['name' => 'Kazakhstan', 'iso' => 'KZ', 'dial' => '+7'],
        ['name' => 'Kenya', 'iso' => 'KE', 'dial' => '+254'],
        ['name' => 'Kuwait', 'iso' => 'KW', 'dial' => '+965'],
        ['name' => 'Kyrgyzstan', 'iso' => 'KG', 'dial' => '+996'],
        ['name' => 'Laos', 'iso' => 'LA', 'dial' => '+856'],
        ['name' => 'Latvia', 'iso' => 'LV', 'dial' => '+371'],
        ['name' => 'Lebanon', 'iso' => 'LB', 'dial' => '+961'],
        ['name' => 'Lesotho', 'iso' => 'LS', 'dial' => '+266'],
        ['name' => 'Liberia', 'iso' => 'LR', 'dial' => '+231'],
        ['name' => 'Libya', 'iso' => 'LY', 'dial' => '+218'],
        ['name' => 'Liechtenstein', 'iso' => 'LI', 'dial' => '+423'],
        ['name' => 'Lithuania', 'iso' => 'LT', 'dial' => '+370'],
        ['name' => 'Luxembourg', 'iso' => 'LU', 'dial' => '+352'],
        ['name' => 'Madagascar', 'iso' => 'MG', 'dial' => '+261'],
        ['name' => 'Malawi', 'iso' => 'MW', 'dial' => '+265'],
        ['name' => 'Malaysia', 'iso' => 'MY', 'dial' => '+60'],
        ['name' => 'Maldives', 'iso' => 'MV', 'dial' => '+960'],
        ['name' => 'Mali', 'iso' => 'ML', 'dial' => '+223'],
        ['name' => 'Malta', 'iso' => 'MT', 'dial' => '+356'],
        ['name' => 'Mauritania', 'iso' => 'MR', 'dial' => '+222'],
        ['name' => 'Mauritius', 'iso' => 'MU', 'dial' => '+230'],
        ['name' => 'Mexico', 'iso' => 'MX', 'dial' => '+52'],
        ['name' => 'Moldova', 'iso' => 'MD', 'dial' => '+373'],
        ['name' => 'Monaco', 'iso' => 'MC', 'dial' => '+377'],
        ['name' => 'Mongolia', 'iso' => 'MN', 'dial' => '+976'],
        ['name' => 'Montenegro', 'iso' => 'ME', 'dial' => '+382'],
        ['name' => 'Morocco', 'iso' => 'MA', 'dial' => '+212'],
        ['name' => 'Mozambique', 'iso' => 'MZ', 'dial' => '+258'],
        ['name' => 'Myanmar', 'iso' => 'MM', 'dial' => '+95'],
        ['name' => 'Namibia', 'iso' => 'NA', 'dial' => '+264'],
        ['name' => 'Nepal', 'iso' => 'NP', 'dial' => '+977'],
        ['name' => 'Netherlands', 'iso' => 'NL', 'dial' => '+31'],
        ['name' => 'New Zealand', 'iso' => 'NZ', 'dial' => '+64'],
        ['name' => 'Nicaragua', 'iso' => 'NI', 'dial' => '+505'],
        ['name' => 'Niger', 'iso' => 'NE', 'dial' => '+227'],
        ['name' => 'Nigeria', 'iso' => 'NG', 'dial' => '+234'],
        ['name' => 'North Macedonia', 'iso' => 'MK', 'dial' => '+389'],
        ['name' => 'Norway', 'iso' => 'NO', 'dial' => '+47'],
        ['name' => 'Oman', 'iso' => 'OM', 'dial' => '+968'],
        ['name' => 'Pakistan', 'iso' => 'PK', 'dial' => '+92'],
        ['name' => 'Palestine', 'iso' => 'PS', 'dial' => '+970'],
        ['name' => 'Panama', 'iso' => 'PA', 'dial' => '+507'],
        ['name' => 'Papua New Guinea', 'iso' => 'PG', 'dial' => '+675'],
        ['name' => 'Paraguay', 'iso' => 'PY', 'dial' => '+595'],
        ['name' => 'Peru', 'iso' => 'PE', 'dial' => '+51'],
        ['name' => 'Philippines', 'iso' => 'PH', 'dial' => '+63'],
        ['name' => 'Poland', 'iso' => 'PL', 'dial' => '+48'],
        ['name' => 'Portugal', 'iso' => 'PT', 'dial' => '+351'],
        ['name' => 'Qatar', 'iso' => 'QA', 'dial' => '+974'],
        ['name' => 'Romania', 'iso' => 'RO', 'dial' => '+40'],
        ['name' => 'Russia', 'iso' => 'RU', 'dial' => '+7'],
        ['name' => 'Rwanda', 'iso' => 'RW', 'dial' => '+250'],
        ['name' => 'Saudi Arabia', 'iso' => 'SA', 'dial' => '+966'],
        ['name' => 'Senegal', 'iso' => 'SN', 'dial' => '+221'],
        ['name' => 'Serbia', 'iso' => 'RS', 'dial' => '+381'],
        ['name' => 'Seychelles', 'iso' => 'SC', 'dial' => '+248'],
        ['name' => 'Sierra Leone', 'iso' => 'SL', 'dial' => '+232'],
        ['name' => 'Singapore', 'iso' => 'SG', 'dial' => '+65'],
        ['name' => 'Slovakia', 'iso' => 'SK', 'dial' => '+421'],
        ['name' => 'Slovenia', 'iso' => 'SI', 'dial' => '+386'],
        ['name' => 'Somalia', 'iso' => 'SO', 'dial' => '+252'],
        ['name' => 'South Africa', 'iso' => 'ZA', 'dial' => '+27'],
        ['name' => 'South Korea', 'iso' => 'KR', 'dial' => '+82'],
        ['name' => 'South Sudan', 'iso' => 'SS', 'dial' => '+211'],
        ['name' => 'Spain', 'iso' => 'ES', 'dial' => '+34'],
        ['name' => 'Sri Lanka', 'iso' => 'LK', 'dial' => '+94'],
        ['name' => 'Sudan', 'iso' => 'SD', 'dial' => '+249'],
        ['name' => 'Sweden', 'iso' => 'SE', 'dial' => '+46'],
        ['name' => 'Switzerland', 'iso' => 'CH', 'dial' => '+41'],
        ['name' => 'Syria', 'iso' => 'SY', 'dial' => '+963'],
        ['name' => 'Taiwan', 'iso' => 'TW', 'dial' => '+886'],
        ['name' => 'Tajikistan', 'iso' => 'TJ', 'dial' => '+992'],
        ['name' => 'Tanzania', 'iso' => 'TZ', 'dial' => '+255'],
        ['name' => 'Thailand', 'iso' => 'TH', 'dial' => '+66'],
        ['name' => 'Togo', 'iso' => 'TG', 'dial' => '+228'],
        ['name' => 'Trinidad and Tobago', 'iso' => 'TT', 'dial' => '+1868'],
        ['name' => 'Tunisia', 'iso' => 'TN', 'dial' => '+216'],
        ['name' => 'Turkey', 'iso' => 'TR', 'dial' => '+90'],
        ['name' => 'Turkmenistan', 'iso' => 'TM', 'dial' => '+993'],
        ['name' => 'Uganda', 'iso' => 'UG', 'dial' => '+256'],
        ['name' => 'Ukraine', 'iso' => 'UA', 'dial' => '+380'],
        ['name' => 'United Arab Emirates', 'iso' => 'AE', 'dial' => '+971'],
        ['name' => 'United Kingdom', 'iso' => 'GB', 'dial' => '+44'],
        ['name' => 'United States', 'iso' => 'US', 'dial' => '+1'],
        ['name' => 'Uruguay', 'iso' => 'UY', 'dial' => '+598'],
        ['name' => 'Uzbekistan', 'iso' => 'UZ', 'dial' => '+998'],
        ['name' => 'Venezuela', 'iso' => 'VE', 'dial' => '+58'],
        ['name' => 'Vietnam', 'iso' => 'VN', 'dial' => '+84'],
        ['name' => 'Yemen', 'iso' => 'YE', 'dial' => '+967'],
        ['name' => 'Zambia', 'iso' => 'ZM', 'dial' => '+260'],
        ['name' => 'Zimbabwe', 'iso' => 'ZW', 'dial' => '+263'],
    ];

    /** @return array<int, array{name: string, iso: string, dial: string}> */
    public static function all(): array
    {
        return self::LIST;
    }

    /**
     * Only the fields the checkout pickers render: name, dialling code and flag.
     * The raw `iso` is not sent to the view — it is folded into the flag here.
     *
     * @return array<int, array{name: string, dial: string, flag: string}>
     */
    public static function forCheckout(): array
    {
        return array_map(
            fn (array $c) => [
                'name' => $c['name'],
                'dial' => $c['dial'],
                'flag' => self::flag($c['iso']),
            ],
            self::LIST
        );
    }

    /**
     * Flag emoji for an ISO-3166 alpha-2 code, built from its two Regional
     * Indicator Symbols (U+1F1E6 is 'A', so each letter maps to 0x1F1E6 + offset).
     * Avoids shipping any flag image or icon font.
     */
    public static function flag(string $iso): string
    {
        $iso = strtoupper($iso);
        if (strlen($iso) !== 2) {
            return '';
        }

        $base = 0x1F1E6; // 🇦
        return mb_chr($base + (ord($iso[0]) - 65), 'UTF-8')
             . mb_chr($base + (ord($iso[1]) - 65), 'UTF-8');
    }

    /** Country names, for the country <select> and its validation rule. */
    public static function names(): array
    {
        return array_column(self::LIST, 'name');
    }

    /** Distinct dialling codes, for the phone-code <select> and its validation rule. */
    public static function dialCodes(): array
    {
        return array_values(array_unique(array_column(self::LIST, 'dial')));
    }

    /** Dialling code for a country name, or null when the name is not on the list. */
    public static function dialFor(?string $countryName): ?string
    {
        foreach (self::LIST as $c) {
            if (strcasecmp($c['name'], (string) $countryName) === 0) {
                return $c['dial'];
            }
        }
        return null;
    }

    /**
     * Split a stored digits-only number (no leading "+") back into a dial
     * code + national number, for pre-filling a code-picker + number input
     * from a previously-saved value. Longest-matching dial code wins, so
     * "+1" doesn't shadow "+1246" (Barbados). Falls back to no code (all
     * digits treated as the national number) when nothing matches.
     *
     * @return array{code: string, number: string}
     */
    public static function splitDial(string $digits): array
    {
        $best = '';
        foreach (self::dialCodes() as $dial) {
            $bare = ltrim($dial, '+');
            if (strpos($digits, $bare) === 0 && strlen($bare) > strlen($best)) {
                $best = $bare;
            }
        }

        if ($best === '') {
            return ['code' => '', 'number' => $digits];
        }

        return ['code' => '+' . $best, 'number' => substr($digits, strlen($best))];
    }
}
