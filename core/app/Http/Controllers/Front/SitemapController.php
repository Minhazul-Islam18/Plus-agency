<?php

namespace App\Http\Controllers\Front;

use App\Blog;
use App\Http\Controllers\Controller;
use App\Language;
use App\Portfolio;
use App\Service;
use App\Tender;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Model-driven /sitemap.xml — replaces the admin-only crawler tool
 * (Admin\SitemapController, which just crawls a given URL and writes a
 * static file) with one built straight from the content tables, so it
 * always matches what's actually published, in every active language.
 *
 * Listing/section pages share one route name across languages (same
 * permalink-driven pattern the hreflang <head> tags already use), so they
 * get real hreflang alternates. Individual Blog/Service/Portfolio/Tender
 * rows are independent per language — no cross-language slug mapping
 * exists for a specific post, so each gets its own single-language <url>
 * entry with no alternates rather than fabricated ones.
 */
class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::flexible('sitemap_xml', [3600 * 6, 3600 * 24], function () {
            return $this->build()->render();
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    private function build(): Sitemap
    {
        $sitemap = Sitemap::create();
        $languages = Language::where('status', 1)->get();

        $listingRoutes = [
            'front.index',
            'front.blogs',
            'front.services',
            'front.portfolios',
            'tenders',
        ];

        foreach ($listingRoutes as $routeName) {
            if (!\Illuminate\Support\Facades\Route::has($routeName)) {
                continue;
            }

            foreach ($languages as $lang) {
                try {
                    $url = route($routeName, ['locale' => $lang->code]);
                } catch (\Throwable $e) {
                    continue;
                }

                $tag = Url::create($url)->setChangeFrequency(
                    $routeName === 'front.index' ? Url::CHANGE_FREQUENCY_DAILY : Url::CHANGE_FREQUENCY_WEEKLY
                );

                foreach ($languages as $altLang) {
                    try {
                        $tag->addAlternate(route($routeName, ['locale' => $altLang->code]), $altLang->code);
                    } catch (\Throwable $e) {
                        // That language doesn't resolve this route — skip the alternate, keep the main URL.
                    }
                }

                $sitemap->add($tag);
            }
        }

        foreach ($languages as $lang) {
            $this->addDetailUrls($sitemap, $lang, Blog::where('language_id', $lang->id)->where('status', 1)->get(), 'front.blogdetails');
            $this->addDetailUrls($sitemap, $lang, Service::where('language_id', $lang->id)->where('status', 1)->get(), 'front.servicedetails');
            $this->addDetailUrls($sitemap, $lang, Portfolio::where('language_id', $lang->id)->where('is_published', 1)->where('is_archived', 0)->get(), 'front.portfoliodetails');
            $this->addDetailUrls($sitemap, $lang, Tender::where('language_id', $lang->id)->where('status', 1)->get(), 'tender_details');
        }

        return $sitemap;
    }

    private function addDetailUrls(Sitemap $sitemap, Language $lang, $items, string $routeName): void
    {
        if (!\Illuminate\Support\Facades\Route::has($routeName)) {
            return;
        }

        foreach ($items as $item) {
            if (empty($item->slug)) {
                continue;
            }

            try {
                $url = route($routeName, ['locale' => $lang->code, 'slug' => $item->slug]);
            } catch (\Throwable $e) {
                continue;
            }

            $tag = Url::create($url)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY);
            if (!empty($item->updated_at)) {
                $tag->setLastModificationDate($item->updated_at);
            }

            $sitemap->add($tag);
        }
    }
}
