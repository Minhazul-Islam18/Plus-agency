<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Check if it's a MaintenanceModeException (503 error)
        if ($this->isHttpException($exception) && $exception->getStatusCode() == 503) {
            // Auto-detect browser language for maintenance mode
            $this->setLanguageFromBrowser($request);
        }

        return parent::render($request, $exception);
    }

    /**
     * Detect and set language from browser Accept-Language header
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    private function setLanguageFromBrowser($request)
    {
        // Check if user has already manually selected a language
        if (session()->has('lang')) {
            app()->setLocale(session()->get('lang'));
            return;
        }

        // Get browser's preferred languages from Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            // Fall back to default language
            $defaultLang = \App\Language::where('is_default', 1)->first();
            if (!empty($defaultLang)) {
                app()->setLocale($defaultLang->code);
            }
            return;
        }

        // Parse the Accept-Language header
        $browserLanguages = [];
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $lang) {
            $parts = explode(';', $lang);
            $code = trim($parts[0]);

            // Extract just the language code (e.g., "en" from "en-US")
            if (strpos($code, '-') !== false) {
                $code = explode('-', $code)[0];
            }

            // Get quality value (q parameter), default to 1.0
            $quality = 1.0;
            if (isset($parts[1]) && strpos($parts[1], 'q=') !== false) {
                $quality = (float) str_replace('q=', '', trim($parts[1]));
            }

            $browserLanguages[$code] = $quality;
        }

        // Sort by quality (preference) in descending order
        arsort($browserLanguages);

        // Get all available languages in the system
        $availableLanguages = \App\Language::pluck('code')->toArray();

        // Find the best match
        foreach ($browserLanguages as $langCode => $quality) {
            if (in_array($langCode, $availableLanguages)) {
                app()->setLocale($langCode);
                return;
            }
        }

        // No match found, use default language
        $defaultLang = \App\Language::where('is_default', 1)->first();
        if (!empty($defaultLang)) {
            app()->setLocale($defaultLang->code);
        }
    }


}