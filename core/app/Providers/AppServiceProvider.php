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

    try {
      $socials = Cache::remember('global_socials', now()->addMinutes(30), function () {
        return Social::where('status', 1)->orderBy('serial_number', 'ASC')->get();
      });
      $langs = Cache::remember('global_languages_active', now()->addMinutes(30), function () {
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
    $data = Cache::remember(self::globalViewCacheKey($currentLang->id), now()->addMinutes(30), function () use ($currentLang) {
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
   * Called from admin controllers that write to the data bundled above
   * (BasicController, LanguageController) so edits show up on the next
   * request instead of waiting out the TTL.
   */
  public static function flushGlobalViewCache(): void
  {
    Cache::forget('global_socials');
    Cache::forget('global_languages_active');
    foreach (Language::pluck('id') as $id) {
      Cache::forget(self::globalViewCacheKey($id));
    }
  }
}
