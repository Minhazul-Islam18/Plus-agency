<?php

namespace App\Providers;

use App\BasicExtra;
use App\Services\SmsGateway\LogSmsGateway;
use App\Services\SmsGateway\SmsGatewayInterface;
use App\Services\SmsGateway\TwilioSmsGateway;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use App\Social;
use App\Language;
use App\Menu;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    $this->app->bind(SmsGatewayInterface::class, function () {
      $bex = BasicExtra::first();

      if ($bex && $bex->twilio_status == 1
          && $bex->twilio_account_sid
          && $bex->twilio_auth_token
          && $bex->twilio_from_number) {
        return new TwilioSmsGateway(
          $bex->twilio_account_sid,
          $bex->twilio_auth_token,
          $bex->twilio_from_number
        );
      }

      return new LogSmsGateway();
    });
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    Paginator::useBootstrap();

    // Admin-configurable LFM upload size caps (admin/basicinfo) override the
    // static defaults in config/lfm.php, so admins can raise/lower the limit
    // without a code deploy. Wrapped in try/catch since this table may not
    // exist yet during a fresh install's initial migrate.
    try {
      $bex = BasicExtra::first();
      if ($bex) {
        if (!empty($bex->lfm_max_image_size_mb)) {
          config(['lfm.folder_categories.image.max_size' => (int) $bex->lfm_max_image_size_mb * 1024]);
        }
        if (!empty($bex->lfm_max_file_size_mb)) {
          config(['lfm.folder_categories.file.max_size' => (int) $bex->lfm_max_file_size_mb * 1024]);
        }
      }
    } catch (\Exception $e) {
      // table not ready yet (fresh install) — fall back to config/lfm.php defaults
    }

    // Baseline {locale} default for route() calls made outside the
    // /{locale} route group (admin login/forget/forced-changepass views
    // reference front.contact; Route::fallback()'s 404 view references
    // front.index) — those requests never hit SetLocaleFromUrl, which is
    // the only other place this gets set. SetLocaleFromUrl overrides this
    // per-request for actual locale-prefixed matches; this is just the
    // fallback so route() doesn't throw UrlGenerationException elsewhere.
    try {
      $defaultLang = Language::where('is_default', 1)->first();
      if ($defaultLang) {
        \Illuminate\Support\Facades\URL::defaults(['locale' => $defaultLang->code]);
      }
    } catch (\Exception $e) {
      // table not ready yet (fresh install)
    }

    try {
      // flexible() instead of remember(): every single request reads these,
      // so a plain TTL expiry is a stampede waiting to happen — many
      // concurrent requests would all miss at once and all hit the DB
      // simultaneously. flexible() serves the still-good stale value to
      // everyone except the one request that refreshes it (under an atomic
      // lock), so an expiry never causes more than one DB hit no matter how
      // many concurrent requests land on it.
      $socials = Cache::flexible('global_socials', [1500, 1800], function () {
        return Social::where('status', 1)->orderBy('serial_number', 'ASC')->get();
      });
      $langs = Cache::flexible('global_languages_active', [1500, 1800], function () {
        return Language::where('status', 1)->get();
      });
    } catch (\Exception $e) {
      $socials = collect();
      $langs   = collect();
    }

    // This composer runs once per rendered view/partial. The data it builds is
    // identical for every view in a request, so compute it a single time and
    // reuse it — otherwise a page with N partials re-runs ~9 queries N times.
    view()->composer('*', function ($view) {
      foreach ($this->globalViewData(app()->getLocale()) as $key => $value) {
        $view->with($key, $value);
      }
    });

    View::share('socials', $socials);
    View::share('langs', $langs);
  }

  /**
   * Global data shared with every view, memoised for the current request.
   *
   * The provider is instantiated per request (php-fpm resets it between
   * requests), so this cache is request-scoped: no cross-request staleness,
   * admin edits are reflected on the next request with no cache to bust.
   *
   * @var array<string, array<string, mixed>>
   */
  private $globalViewMemo = [];

  private function globalViewData(?string $locale): array
  {
    $key = $locale ?: 'default';
    if (isset($this->globalViewMemo[$key])) {
      return $this->globalViewMemo[$key];
    }

    // Current locale set by SetLangMiddleware; fall back to the default language.
    $currentLang = $locale ? Language::where('code', $locale)->first() : null;
    if (empty($currentLang)) {
      $currentLang = Language::where('is_default', 1)->first();
    }

    // Cached cross-request (not just per-request) — this bundle is read on
    // every single view render but only changes via admin CRUD (BasicController,
    // LanguageController). TTL keeps writes from other controllers (menus,
    // popups, ulinks, categories) eventually consistent within 30 min even
    // though they don't explicitly bust this key.
    //
    // flexible(), not remember(): this is the single most-read cache key in
    // the app (every request, every route) — a plain TTL expiry here is a
    // textbook stampede (confirmed live: 40 concurrent requests all missing
    // at once caused real PDOException failures under production load
    // testing). flexible() guarantees only one request ever rebuilds it,
    // everyone else gets the still-valid stale value with zero DB hit.
    $data = Cache::flexible(self::globalViewCacheKey($currentLang->id), [1500, 1800], function () use ($currentLang) {
      $menuRow = Menu::where('language_id', $currentLang->id)->first();

      $data = [
        'bs'          => $currentLang->basic_setting,
        'be'          => $currentLang->basic_extended,
        'bex'         => $currentLang->basic_extra,
        'ulinks'      => $currentLang->ulinks,
        'apopups'     => $currentLang->popups()->where('status', 1)->orderBy('serial_number', 'ASC')->get(),
        'menus'       => $menuRow ? $menuRow->menus : json_encode([]),
        'currentLang' => $currentLang,
        'rtl'         => $currentLang->rtl == 1 ? 1 : 0,
      ];

      if (serviceCategory()) {
        $data['scats'] = $currentLang->scategories()->where('status', 1)->orderBy('serial_number', 'ASC')->get();
      }

      return $data;
    });

    return $this->globalViewMemo[$key] = $data;
  }

  private static function globalViewCacheKey(int $languageId): string
  {
    return "global_view_data:lang:{$languageId}";
  }

  /**
   * Invoked automatically via App\Traits\InvalidatesGlobalViewCache's model
   * events (saved/deleted) on BasicExtra/BasicExtended/BasicSetting/Menu/
   * Language/Popup — any save/delete on those busts this in-app cache so
   * edits show up on the next request instead of waiting out the TTL.
   */
  public static function flushGlobalViewCache(): void
  {
    Cache::forget('global_socials');
    Cache::forget('global_languages_active');
    foreach (Language::pluck('id') as $id) {
      Cache::forget(self::globalViewCacheKey($id));
    }

    self::purgeLiteSpeedCache();
  }

  /**
   * Purges the origin's own LiteSpeed LSCache — a separate layer from
   * Cloudflare's edge cache (which an admin purges manually via the
   * Cloudflare dashboard/hPanel, by design — see the comment on
   * $cfCacheableExcept in routes/web.php) and from anything Laravel itself
   * caches. Since SetPublicCacheHeaders opted the homepage + portfolio/
   * service/blog details + several listing pages into real HTTP caching
   * (both Cloudflare and LiteSpeed), a content edit that used to be visible
   * on the very next request now sits behind that cache too — this is what
   * makes it visible again immediately instead of waiting out its own TTL.
   *
   * X-LiteSpeed-Purge: * asks LiteSpeed to drop everything it cached for
   * this app, not just one URL — deliberately blunt rather than per-URL
   * tagging: the affected models (Portfolio/Service/Member/Tender/Blog/
   * Faq/Point/Testimonial/Partner, plus the 6 global-view-data models)
   * mostly feed the homepage as well as their own listing/detail pages, so
   * precise tagging would need to enumerate every page a given edit could
   * touch anyway. This is a low-traffic admin-driven site (not high-QPS),
   * so an occasional full purge on save is cheap next to that complexity.
   */
  public static function purgeLiteSpeedCache(): void
  {
    if (!headers_sent()) {
      header('X-LiteSpeed-Purge: *');
    }
  }
}
