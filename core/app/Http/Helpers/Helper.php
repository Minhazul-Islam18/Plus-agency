<?php

use App\BasicExtra;
use App\Page;

// Asset directory constants. Two forms per logical directory: the bare
// public-URL-relative form (used directly, e.g. LFM uploads) and the
// base_path()-relative form (prefixed '../', used where code needs an
// absolute filesystem path, e.g. realpath()/htaccess-guard writes).
foreach ([
    'FRONT_IMG_PATH'          => 'assets/front/img/',
    'FRONT_IMG_DIR'           => '../assets/front/img/',
    'FRONT_IMG_PUBLIC_DIR'    => 'public/assets/front/img/',
    'FRONT_ADMIN_IMG_PATH'    => 'assets/admin/img/',
    'FRONT_ADMIN_IMG_DIR'     => '../assets/admin/img/',
    'FRONT_INVOICES_PATH'     => 'assets/front/invoices/',
    'FRONT_RECEIPT_PATH'      => 'assets/front/receipt/',
    'FRONT_TENDER_INVOICE_DIR' => '../assets/front/invoices/tender/',
    'FRONT_TENDER_FILES_DIR'  => '../assets/front/files/tender_modules',
    'FRONT_TENDER_FILES_PATH' => 'assets/front/files/tender_modules/',
    'FRONT_LFM_FILES_DIR'     => '../assets/lfm/files',
] as $name => $value) {
    if (! defined($name)) {
        define($name, $value);
    }
}

if (! function_exists('setEnvironmentValue')) {
    function setEnvironmentValue(array $values)
    {

        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {

                $str .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }
            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) return false;
        return true;
    }
}


if (! function_exists('convertUtf8')) {
    function convertUtf8($value)
    {
        return mb_detect_encoding($value, mb_detect_order(), true) === 'UTF-8' ? $value : mb_convert_encoding($value, 'UTF-8');
    }
}


if (! function_exists('allowed_image_extensions')) {
    /**
     * Image extensions accepted for direct (non-LFM) admin uploads —
     * derived from LFM's own image-category MIME whitelist
     * (config/lfm.php, 'folder_categories.image.valid_mime') so both
     * stay in sync from one place. Add a new format there and it
     * automatically shows up here too, in both validation and hint text.
     */
    function allowed_image_extensions()
    {
        $mimeToExt = [
            'image/jpeg'    => ['jpg', 'jpeg'],
            'image/pjpeg'   => ['jpg', 'jpeg'],
            'image/jpg'     => ['jpg'],
            'image/png'     => ['png'],
            'image/gif'     => ['gif'],
            'image/webp'    => ['webp'],
            'image/avif'    => ['avif'],
            'image/svg+xml' => ['svg'],
        ];

        $mimes = config('lfm.folder_categories.image.valid_mime', []);
        $extensions = [];
        foreach ($mimes as $mime) {
            foreach ($mimeToExt[$mime] ?? [] as $ext) {
                $extensions[$ext] = true;
            }
        }

        return array_keys($extensions);
    }
}


if (! function_exists('allowed_image_extensions_label')) {
    /** e.g. "JPG, JPEG, PNG, GIF, WEBP, AVIF, SVG" — for upload-field hint text. */
    function allowed_image_extensions_label()
    {
        return implode(', ', array_map('strtoupper', allowed_image_extensions()));
    }
}


if (! function_exists('max_upload_size_label')) {
    /**
     * e.g. "20 MB" — the real, admin-configurable LFM upload limit (see
     * admin/basicinfo's own "Upload Size Limit" card, lfm_max_image_size_mb
     * / lfm_max_file_size_mb on basic_settings_extended) rather than a
     * hardcoded number in every form's hint text — same "one source of
     * truth" reasoning as allowed_image_extensions() above. $type is
     * 'image' (jpg/png/svg/webp/avif pickers) or 'file' (documents —
     * pdf/zip/etc). Defaults match the same 20/50 fallback basicinfo
     * itself uses when the columns are still null.
     */
    function max_upload_size_label($type = 'image')
    {
        static $bex = null;
        if ($bex === null) {
            $bex = BasicExtra::first() ?? false;
        }

        $mb = $type === 'file'
            ? ($bex->lfm_max_file_size_mb ?? 50)
            : ($bex->lfm_max_image_size_mb ?? 20);

        return $mb . ' MB';
    }
}


if (! function_exists('make_slug')) {
    function make_slug($string)
    {
        $slug = preg_replace('/\s+/u', '-', trim($string));
        $slug = str_replace("/", "", $slug);
        $slug = str_replace("?", "", $slug);
        return $slug;
    }
}

if (! function_exists('intelligent_slug')) {
    /**
     * Meaningful-word slug generator — used ONLY to auto-fill the URL Slug
     * field when an admin leaves it blank (Portfolio/Blog/Tender). A long
     * tender title like "Appel d'offres international pour la réalisation
     * des travaux de forages d'exploitation à gros débit y compris..."
     * otherwise turns into an equally long, near-unreadable URL —
     * make_slug()/slug_create() just dash-join the WHOLE title verbatim.
     * This transliterates accents, drops filler/function words (French/
     * English/Portuguese — the site's main languages), and caps length,
     * keeping the words that actually carry meaning.
     *
     * An admin-TYPED slug never goes through this — only sanitized via
     * make_slug()/slug_create() as before, so their own wording is
     * respected exactly. This only runs on the auto-generate path.
     */
    function intelligent_slug($title, $maxWords = 7, $maxLength = 60)
    {
        $ascii = \Illuminate\Support\Str::ascii((string) $title); // é->e, ’->', «»->"", etc.
        $words = preg_split('/[^a-zA-Z0-9]+/', $ascii, -1, PREG_SPLIT_NO_EMPTY);

        static $stopwords = null;
        if ($stopwords === null) {
            $stopwords = array_flip([
                // French
                'a', 'au', 'aux', 'avec', 'ce', 'ces', 'dans', 'de', 'des', 'du',
                'elle', 'en', 'et', 'eu', 'il', 'je', 'la', 'le', 'les', 'leur',
                'lui', 'ma', 'mais', 'me', 'meme', 'mes', 'moi', 'mon', 'ne', 'nos',
                'notre', 'nous', 'on', 'ou', 'par', 'pas', 'pour', 'qu', 'que',
                'qui', 'sa', 'se', 'ses', 'son', 'sur', 'ta', 'te', 'tes', 'toi',
                'ton', 'tu', 'un', 'une', 'vos', 'votre', 'vous', 'y', 'est',
                'sont', 'etre', 'ete', 'compris', 'ainsi', 'afin',
                // English
                'the', 'an', 'and', 'or', 'but', 'of', 'for', 'to', 'in', 'on',
                'at', 'by', 'with', 'is', 'are', 'was', 'were', 'be', 'been',
                'being', 'this', 'that', 'these', 'those', 'as', 'it', 'its',
                'from', 'into', 'including', 'regarding',
                // Portuguese
                'o', 'os', 'as', 'do', 'da', 'dos', 'das', 'em', 'para', 'com',
                'uma', 'no', 'na', 'nas', 'ao', 'aos',
                // Procurement boilerplate — this site's portfolios are largely
                // sourced from tender titles, and nearly all of them open with
                // some variant of "Appel d'offres international pour la
                // réalisation de..." ("International call for tenders for the
                // completion of..."). Zero differentiating value in a URL.
                'appel', 'offres', 'international', 'realisation',
            ]);
        }

        $meaningful = array_values(array_filter($words, function ($w) use ($stopwords) {
            // Numbers always kept (e.g. "40" in "40 SAEPmV") even though
            // they'd never be in a stopword list anyway — belt and braces.
            // Single letters dropped too — mostly orphaned French elisions
            // (d'offres/l'eau/qu'il splitting into "d"/"l"/"qu"+word on the
            // apostrophe) rather than actual content.
            if (strlen($w) === 1 && !ctype_digit($w)) {
                return false;
            }
            return ctype_digit($w) || !isset($stopwords[strtolower($w)]);
        }));

        // Stopwords ate everything (a title that's ALL function words, or a
        // non-Latin script these lists don't cover) — fall back to the
        // un-filtered word list so this never returns an empty slug.
        if (empty($meaningful)) {
            $meaningful = $words;
        }

        $slug = '';
        $count = 0;
        foreach ($meaningful as $word) {
            $word = strtolower($word);
            $candidate = $slug === '' ? $word : $slug . '-' . $word;
            if (strlen($candidate) > $maxLength || $count >= $maxWords) {
                break;
            }
            $slug = $candidate;
            $count++;
        }

        return $slug !== '' ? $slug : \Illuminate\Support\Str::slug($title);
    }
}

if (! function_exists('unique_intelligent_slug')) {
    /**
     * intelligent_slug() shortens by dropping words, so two different long
     * titles can collapse to the same short slug (e.g. two tenders both
     * boiling down to "transport-logistique"). $existsCallback is a
     * module-specific uniqueness check (excluding the row's own id on an
     * update) — appends -2, -3, ... until the result is actually free.
     */
    function unique_intelligent_slug($title, callable $existsCallback, $maxWords = 7, $maxLength = 60)
    {
        $base = intelligent_slug($title, $maxWords, $maxLength);
        $slug = $base;
        $i = 2;
        while ($existsCallback($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}

if (! function_exists('record_slug_redirect')) {
    /**
     * Editable-slug feature (Portfolio/Blog/Tender): call this with the
     * OLD slug right before overwriting it with a new one, so the old URL
     * keeps working (301) instead of 404ing. $module is 'portfolio' |
     * 'blog' | 'tender' — see each front controller's slug lookup.
     *
     * Stores the target's id, not the new slug string — a redirect is
     * resolved from the item's CURRENT slug at request time, so changing
     * the slug a second (or third) time doesn't leave earlier redirects
     * pointing at a now-also-dead intermediate slug.
     */
    function record_slug_redirect($module, $oldSlug, $targetId)
    {
        if (empty($oldSlug)) {
            return;
        }
        \App\UrlRedirect::updateOrCreate(
            ['module' => $module, 'old_slug' => $oldSlug],
            ['target_id' => $targetId, 'created_at' => now()]
        );
    }
}

if (! function_exists('clear_slug_redirect')) {
    /**
     * Call with a slug right before it becomes an item's ACTIVE slug
     * (create, or an admin's manual edit). If that exact string was
     * previously recorded as someone else's "old" redirect key, that stale
     * row would otherwise hijack real traffic away from the item now
     * actually using it.
     */
    function clear_slug_redirect($module, $slug)
    {
        \App\UrlRedirect::where('module', $module)->where('old_slug', $slug)->delete();
    }
}

if (! function_exists('resolve_slug_redirect')) {
    /**
     * Front-end lookup: given a slug that didn't match any live row, see if
     * it's a known old slug for this module. Returns the CURRENT model
     * instance to redirect to, or null (real 404).
     */
    function resolve_slug_redirect($module, $slug)
    {
        $redirect = \App\UrlRedirect::where('module', $module)->where('old_slug', $slug)->first();
        if (!$redirect) {
            return null;
        }

        $modelClass = [
            'portfolio' => \App\Portfolio::class,
            'blog' => \App\Blog::class,
            'tender' => \App\Tender::class,
        ][$module] ?? null;

        return $modelClass ? $modelClass::find($redirect->target_id) : null;
    }
}


if (! function_exists('make_input_name')) {
    function make_input_name($string)
    {
        return preg_replace('/\s+/u', '_', trim($string));
    }
}


if (! function_exists('serviceCategory')) {
    function serviceCategory()
    {
        // Memoized per request — called repeatedly across the layout/homepage
        // on every render (menu conditions, section toggles, etc.).
        static $hbex = null;
        if ($hbex === null) {
            $hbex = BasicExtra::first() ?? false;
        }
        if ($hbex && $hbex->service_category == 1) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('asset_v')) {
    // filemtime-based cache-busting: only changes the URL when the file
    // itself changes, so browsers can actually cache it in between —
    // unlike a ?v=time() query string, which busts the cache on every request.
    // Deployment serves /assets from the project root (sibling of core/,
    // i.e. dirname(base_path())), not core/public — asset() URLs already
    // account for this via the web server; filemtime() must too.
    function asset_v($path)
    {
        $full = dirname(base_path()) . '/' . ltrim($path, '/');
        $version = file_exists($full) ? filemtime($full) : time();
        return asset($path) . '?v=' . $version;
    }
}

if (!function_exists('currentLang')) {
    // Single source of truth for "what language is this request in", now
    // that locale comes from the URL (/{locale}/..., set by
    // SetLocaleFromUrl) rather than a site_lang cookie. Replaces ~20
    // call-site copies of "if cookie/session has lang, else default" that
    // were never updated when the cookie (and before that, session)
    // mechanism was retired — they silently kept reading a value nothing
    // sets anymore, so every page always fell back to the default
    // language regardless of the /en/ or /fr/ URL. app()->getLocale() is
    // correct for both: SetLocaleFromUrl sets it from the URL segment on
    // locale-prefixed routes, and SetLangMiddleware (still active on the
    // few deliberately-unprefixed routes, e.g. tender payment callbacks)
    // sets it the old way there — one call works everywhere.
    function currentLang()
    {
        return \App\Language::where('code', app()->getLocale())->where('status', 1)->first()
            ?? \App\Language::where('is_default', 1)->first();
    }
}

if (!function_exists('tenderCountdown')) {
    // Real days/hours remaining, computed server-side — the countdown
    // markup's static HTML previously always hardcoded "00j 00h" regardless
    // of the actual deadline, relying entirely on dark-tenders-fx.js's
    // client-side tick() to overwrite it after the page loads. Search
    // crawlers (and anyone for a brief moment before JS runs) only ever
    // saw "00". The JS still takes over and re-ticks every 60s — this only
    // fixes what the *first* render shows.
    function tenderCountdown($deadline)
    {
        $diffSeconds = max(0, \Carbon\Carbon::parse($deadline)->getTimestamp() - now()->getTimestamp());
        return [
            'd' => str_pad((string) intdiv($diffSeconds, 86400), 2, '0', STR_PAD_LEFT),
            'h' => str_pad((string) intdiv($diffSeconds % 86400, 3600), 2, '0', STR_PAD_LEFT),
        ];
    }
}

if (!function_exists('slug_create')) {
    function slug_create($val)
    {
        $slug = preg_replace('/\s+/u', '-', trim($val));
        $slug = str_replace("/", "", $slug);
        $slug = str_replace("?", "", $slug);
        return $slug;
    }
}


if (!function_exists('getHref')) {
    function getHref($link)
    {
        $href = "#";

        if ($link["type"] == 'home') {
            $href = route('front.index');
        } else if ($link["type"] == 'services' || $link["type"] == 'services-megamenu') {
            $href = route('front.services');
        } else if ($link["type"] == 'portfolios' || $link["type"] == 'portfolios-megamenu') {
            $href = route('front.portfolios');
        } else if ($link["type"] == 'team') {
            $href = route('front.team');
        } else if ($link["type"] == 'tenders' || $link["type"] == 'tenders-megamenu') {
            $href = route('tenders');
        } else if ($link["type"] == 'gallery') {
            $href = route('front.gallery');
        } else if ($link["type"] == 'faq') {
            $href = route('front.faq');
        } else if ($link["type"] == 'blogs' || $link["type"] == 'blogs-megamenu') {
            $href = route('front.blogs');
        } else if ($link["type"] == 'feedback') {
            $href = route('feedback');
        } else if ($link["type"] == 'contact') {
            $href = route('front.contact');
        } else if ($link["type"] == 'custom') {
            if (empty($link["href"])) {
                $href = "#";
            } else {
                $href = $link["href"];
            }
        } else {
            // Memoized per request — the same menu tree is walked once for
            // the desktop nav and again for the mobile nav, so without this
            // every custom-page link's slug gets looked up twice.
            static $pageCache = [];
            $pageid = (int)$link["type"];
            if (!array_key_exists($pageid, $pageCache)) {
                $pageCache[$pageid] = Page::find($pageid);
            }
            $page = $pageCache[$pageid];
            if (!empty($page)) {
                $href = route('front.dynamicPage', [$page->slug]);
            } else {
                $href = '#';
            }
        }

        return $href;
    }
}



if (!function_exists('create_menu')) {
    function create_menu($arr)
    {
        echo '<ul style="z-index: 0;">';
        foreach ($arr["children"] as $el) {

            // determine if the class is 'submenus' or not
            $class = null;
            if (array_key_exists("children", $el)) {
                $class = 'class="submenus"';
            }


            // determine the href
            $href = getHref($el);


            echo '<li ' . $class . '>';
            echo '<a  href="' . $href . '" target="' . $el["target"] . '">' . $el["text"] . '</a>';
            if (array_key_exists("children", $el)) {
                create_menu($el);
            }
            echo '</li>';
        }
        echo '</ul>';
    }
}



if (!function_exists('hex2rgb')) {
    function hex2rgb($colour)
    {
        if ($colour[0] == '#') {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) == 6) {
            list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }
}


if (!function_exists('onlyDigitalItemsInCart')) {
    function onlyDigitalItemsInCart()
    {
        $cart = session()->get('cart');

        if (!empty($cart)) {
            foreach ($cart as $key => $cartItem) {
                if (array_key_exists('type', $cartItem) && $cartItem['type'] != 'digital') {
                    return false;
                }
            }
        }

        return true;
    }
}


if (!function_exists('containsDigitalItemsInCart')) {
    function containsDigitalItemsInCart()
    {
        $cart = session()->get('cart');

        if (!empty($cart)) {
            foreach ($cart as $key => $cartItem) {
                if (array_key_exists('type', $cartItem) && $cartItem['type'] == 'digital') {
                    return true;
                }
            }
        }

        return false;
    }
}


if (!function_exists('onlyDigitalItems')) {
    function onlyDigitalItems($order)
    {
        $oitems = $order->orderitems;

        foreach ($oitems as $key => $oitem) {
            if ($oitem->product->type != 'digital') {
                return false;
            }
        }

        return true;
    }
}


if (!function_exists('containsDigitalItem')) {
    function containsDigitalItem($order)
    {
        $oitems = $order->orderitems;

        foreach ($oitems as $key => $oitem) {
            if ($oitem->product->type == 'digital') {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('cartLength')) {
    function cartLength()
    {
        $length = 0;
        if (session()->has('cart') && !empty(session()->get('cart'))) {
            $cart = session()->get('cart');
            foreach ($cart as $key => $cartItem) {
                $length += (float)$cartItem['qty'];
            }
        }

        return round($length, 2);
    }
}

if (!function_exists('cartTotal')) {
    function cartTotal()
    {
        $total = 0;
        if (session()->has('cart') && !empty(session()->get('cart'))) {
            $cart = session()->get('cart');
            foreach ($cart as $key => $cartItem) {
                $total += (float)$cartItem['price'] * (float)$cartItem['qty'];
            }
        }

        return round($total, 2);
    }
}

if (!function_exists('cartSubTotal')) {
    function cartSubTotal()
    {
        $coupon = session()->has('coupon') && !empty(session()->get('coupon')) ? session()->get('coupon') : 0;
        $cartTotal = cartTotal();
        $subTotal = $cartTotal - $coupon;

        return round($subTotal, 2);
    }
}


if (!function_exists('tax')) {
    function tax()
    {
        $bex = BasicExtra::first();
        $tax = $bex->tax;

        if (session()->has('cart') && !empty(session()->get('cart'))) {
            $tax = (cartSubTotal() * $tax) / 100;
        }

        return round($tax, 2);
    }
}

if (!function_exists('coupon')) {
    function coupon()
    {
        return session()->has('coupon') && !empty(session()->get('coupon')) ? round(session()->get('coupon'), 2) : 0.00;
    }
}
