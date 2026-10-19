<?php

namespace App\Http\Controllers\Front;

use App\Blog;
use App\Gallery;
use App\Http\Controllers\Controller;
use App\Language;
use App\Page;
use App\Portfolio;
use App\Service;
use App\Tender;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Model-driven /sitemap.xml, built straight from the content tables so it
 * always matches what's actually published, in every active language.
 * (The old admin-only "crawl a URL, write a static file" tool this
 * replaced — Admin\SitemapController — has since been removed entirely.)
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
            'front.team',
            'front.gallery',
            'front.faq',
            'front.contact',
        ];

        $defaultLangCode = optional($languages->firstWhere('is_default', 1))->code;

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

                $lastmod = $this->listingLastModificationDate($routeName, $lang);
                if (!empty($lastmod)) {
                    $tag->setLastModificationDate($lastmod);
                }

                foreach ($languages as $altLang) {
                    try {
                        $tag->addAlternate(route($routeName, ['locale' => $altLang->code]), $altLang->code);
                    } catch (\Throwable $e) {
                        // That language doesn't resolve this route — skip the alternate, keep the main URL.
                    }
                }

                // Same x-default signal the <head> hreflang block already emits
                // (front/default/layout.blade.php) — must never disagree with it.
                if (!empty($defaultLangCode)) {
                    try {
                        $tag->addAlternate(route($routeName, ['locale' => $defaultLangCode]), 'x-default');
                    } catch (\Throwable $e) {
                        // no-op
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
            $this->addDetailUrls($sitemap, $lang, Page::where('language_id', $lang->id)->where('status', 1)->get(), 'front.dynamicPage');
        }

        return $sitemap;
    }

    /**
     * Real freshness signal for a listing page — the most recent updated_at
     * among the content it actually lists, never the current request time.
     * A page with no backing content model (e.g. the static Contact page)
     * gets no <lastmod> at all rather than a fabricated one.
     */
    private function listingLastModificationDate(string $routeName, Language $lang): ?\Illuminate\Support\Carbon
    {
        switch ($routeName) {
            case 'front.index':
                return collect([
                    Blog::where('language_id', $lang->id)->where('status', 1)->max('updated_at'),
                    Service::where('language_id', $lang->id)->where('status', 1)->max('updated_at'),
                    Portfolio::where('language_id', $lang->id)->where('is_published', 1)->where('is_archived', 0)->max('updated_at'),
                    Tender::where('language_id', $lang->id)->where('status', 1)->max('updated_at'),
                ])->filter()->map(fn ($d) => \Illuminate\Support\Carbon::parse($d))->sortDesc()->first();

            case 'front.blogs':
                $d = Blog::where('language_id', $lang->id)->where('status', 1)->max('updated_at');
                break;

            case 'front.services':
                $d = Service::where('language_id', $lang->id)->where('status', 1)->max('updated_at');
                break;

            case 'front.portfolios':
                $d = Portfolio::where('language_id', $lang->id)->where('is_published', 1)->where('is_archived', 0)->max('updated_at');
                break;

            case 'tenders':
                $d = Tender::where('language_id', $lang->id)->where('status', 1)->max('updated_at');
                break;

            case 'front.gallery':
                $d = Gallery::where('language_id', $lang->id)->max('updated_at');
                break;

            // 'front.team' / 'front.faq': the members/faqs tables carry no
            // created_at/updated_at columns at all — no real freshness
            // signal exists, so no <lastmod> is emitted rather than a
            // fabricated one.
            default:
                return null;
        }

        return !empty($d) ? \Illuminate\Support\Carbon::parse($d) : null;
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
