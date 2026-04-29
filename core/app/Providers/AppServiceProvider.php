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

    view()->composer('*', function ($view) {
      // Get current locale (set by SetLangMiddleware or manual selection)
      $currentLocale = app()->getLocale();

      if ($currentLocale) {
        $currentLang = Language::where('code', $currentLocale)->first();
      }

      // Fallback to default if not found
      if (empty($currentLang)) {
        $currentLang = Language::where('is_default', 1)->first();
      }

      $bs = $currentLang->basic_setting;
      $be = $currentLang->basic_extended;
      $bex = $currentLang->basic_extra;

      $ulinks = $currentLang->ulinks;
      $apopups = $currentLang->popups()->where('status', 1)->orderBy('serial_number', 'ASC')->get();

      if (serviceCategory()) {
        $scats = $currentLang->scategories()->where('status', 1)->orderBy('serial_number', 'ASC')->get();
      }

      if (Menu::where('language_id', $currentLang->id)->count() > 0) {
        $menus = Menu::where('language_id', $currentLang->id)->first()->menus;
      } else {
        $menus = json_encode([]);
      }

      if ($currentLang->rtl == 1) {
        $rtl = 1;
      } else {
        $rtl = 0;
      }

      $view->with('bs', $bs);
      $view->with('be', $be);
      $view->with('bex', $bex);
      if (serviceCategory()) {
        $view->with('scats', $scats);
      }
      $view->with('apopups', $apopups);
      $view->with('ulinks', $ulinks);
      $view->with('menus', $menus);
      $view->with('currentLang', $currentLang);
      $view->with('rtl', $rtl);
    });

    View::share('socials', $socials);
    View::share('langs', $langs);
  }
}
