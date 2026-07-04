<?php

namespace App\Providers;

use App\BasicExtra;
use App\Services\SmsGateway\LogSmsGateway;
use App\Services\SmsGateway\SmsGatewayInterface;
use App\Services\SmsGateway\TwilioSmsGateway;
use Illuminate\Support\ServiceProvider;
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

    try {
      $socials = Social::where('status', 1)->orderBy('serial_number', 'ASC')->get();
      $langs   = Language::where('status', 1)->get();
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

    return $this->globalViewMemo[$key] = $data;
  }
}
