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


if (! function_exists('make_slug')) {
    function make_slug($string)
    {
        $slug = preg_replace('/\s+/u', '-', trim($string));
        $slug = str_replace("/", "", $slug);
        $slug = str_replace("?", "", $slug);
        return $slug;
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
