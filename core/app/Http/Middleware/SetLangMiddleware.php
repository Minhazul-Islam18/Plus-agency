<?php

namespace App\Http\Middleware;

use Closure;
use App\Language;
use App;

class SetLangMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
     public function handle($request, Closure $next)
     {

         if (session()->has('lang')) {
           // User has manually selected a language - respect their choice
           app()->setLocale(session()->get('lang'));
         } else {
           // Auto-detect browser language
           $detectedLang = $this->detectBrowserLanguage($request);

           if ($detectedLang) {
             // Use detected language (don't persist to session)
             app()->setLocale($detectedLang);
           } else {
             // Fall back to default language if no match found
             $defaultLang = Language::where('is_default', 1)->first();
             if (!empty($defaultLang)) {
               app()->setLocale($defaultLang->code);
             }
           }
         }

         return $next($request);
     }

     /**
      * Detect browser language from Accept-Language header
      *
      * @param  \Illuminate\Http\Request  $request
      * @return string|null
      */
     private function detectBrowserLanguage($request)
     {
         // Get browser's preferred languages from Accept-Language header
         $acceptLanguage = $request->header('Accept-Language');

         if (!$acceptLanguage) {
             return null;
         }

         // Parse the Accept-Language header
         // Format: "en-US,en;q=0.9,ar;q=0.8,es;q=0.7"
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
         $availableLanguages = Language::pluck('code')->toArray();

         // Find the best match
         foreach ($browserLanguages as $langCode => $quality) {
             if (in_array($langCode, $availableLanguages)) {
                 return $langCode;
             }
         }

         return null;
     }
}
