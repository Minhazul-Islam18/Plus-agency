<?php

use App\BasicExtended;
use App\BasicExtra;
use App\BasicSetting;
use App\Language;

if (!function_exists('convertHtml') ) {
    function convertHtml($content) {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $be = BasicExtended::firstOrFail();
        $version = $be->theme_version;

        $content = str_replace("{base_url}", url('/'), $content);

        // service category
        $content = str_replace("[pagebuilder-service-category][/pagebuilder-service-category]", serviceCategorySection($currentLang, $version), $content);

        // services
        $content = str_replace("[pagebuilder-services][/pagebuilder-services]", servicesSection($currentLang, $version), $content);

        // portfolios
        $content = str_replace("[pagebuilder-portfolios][/pagebuilder-portfolios]", portfoliosSection($currentLang, $version), $content);

        // team
        $content = str_replace("[pagebuilder-team][/pagebuilder-team]", teamSection($currentLang, $version), $content);

        // statistics
        $content = str_replace("[pagebuilder-statistics][/pagebuilder-statistics]", statisticsSection($currentLang, $version), $content);

        // testimonial
        $content = str_replace("[pagebuilder-testimonial][/pagebuilder-testimonial]", testimonialSection($currentLang, $version), $content);

        // blogs
        $content = str_replace("[pagebuilder-blogs][/pagebuilder-blogs]", blogsSection($currentLang, $version), $content);

        // partner
        $content = str_replace("[pagebuilder-partner][/pagebuilder-partner]", partnerSection($currentLang, $version), $content);

        // approach
        $content = str_replace("[pagebuilder-approach][/pagebuilder-approach]", approachSection($currentLang, $version), $content);

        // faq
        $content = str_replace("[pagebuilder-faq][/pagebuilder-faq]", faqSection($currentLang), $content);

        // Newsletter
        $content = str_replace("[pagebuilder-newsletter-section][/pagebuilder-newsletter-section]", newsletterSection($currentLang, $version), $content);

        return $content;
    }
}

if (!function_exists('replaceBaseUrl') ) {
    function replaceBaseUrl($content) {
        // Every save site-wide ran this token through clean() (HTMLPurifier)
        // AFTER inserting it into an <img src>, and Purifier percent-encodes
        // "{"/"}" as invalid URI characters — so every already-saved image
        // is stuck as "%7Bbase_url%7D/..." in the DB, not the literal
        // "{base_url}" this used to look for. Handling both forms fixes
        // existing rows immediately; the controllers were also fixed to
        // stop corrupting new saves (clean() now runs before the token is
        // inserted, not after).
        $content = str_replace(["%7Bbase_url%7D", "{base_url}"], url('/'), $content);
        return $content;
    }
}

if (!function_exists('faqSection')) {
    function faqSection($currentLang)
    {

        if (!empty($currentLang->faqs)) {
            $faqs = $currentLang->faqs()->orderBy('serial_number', 'ASC')->get();
        } else {
            $faqs = [];
        }

        $faqSec = "<div class='row' data-gjs-draggable='false' data-gjs-editable='false' data-gjs-removable='false' data-gjs-propagate=" . '["removable","editable","draggable"]' . ">
        <div class='col-lg-6'>
        <div class='accordion' id='accordionExample1'>";

        for ($i = 0; $i < ceil(count($faqs) / 2); $i++) {
            $faqSec .= "<div class='card'>
            <div class='card-header' id='heading" . $faqs[$i]->id . "'>
            <h2 class='mb-0'>
            <button class='btn btn-link collapsed btn-block text-left' type='button' data-toggle='collapse' data-target='#collapse" . $faqs[$i]->id . "' aria-expanded='false' aria-controls='collapse" . $faqs[$i]->id . "'>" .
            convertUtf8($faqs[$i]->question)
            . "</button>
            </h2>
            </div>
            <div id='collapse" . $faqs[$i]->id . "' class='collapse' aria-labelledby='heading" . $faqs[$i]->id . "' data-parent='#accordionExample1'>
            <div class='card-body'>" .
            convertUtf8($faqs[$i]->answer) .
            "</div>
            </div>
            </div>";
        }

        $faqSec .= "</div>
        </div>
        <div class='col-lg-6'>
        <div class='accordion' id='accordionExample2'>";
        for ($i = ceil(count($faqs) / 2); $i < count($faqs); $i++) {
            $faqSec .= "<div class='card'>
            <div class='card-header' id='heading" . $faqs[$i]->id . "'>
            <h2 class='mb-0'>
            <button class='btn btn-link collapsed btn-block text-left' type='button' data-toggle='collapse' data-target='#collapse" . $faqs[$i]->id . "' aria-expanded='false' aria-controls='collapse" . $faqs[$i]->id . "'>" . convertUtf8($faqs[$i]->question) .
            "</button>
            </h2>
            </div>
            <div id='collapse" . $faqs[$i]->id . "' class='collapse' aria-labelledby='heading" . $faqs[$i]->id . "' data-parent='#accordionExample2'>
            <div class='card-body'>" .
            convertUtf8($faqs[$i]->answer) .
            "</div>
            </div>
            </div>";
        }
        $faqSec .= "</div>
        </div>
        </div>";

        return $faqSec;
    }
}


if (!function_exists('serviceCategorySection')) {

    function serviceCategorySection($currentLang, $version) {

        if (!empty($currentLang->scategories)) {
            $scats = $currentLang->scategories()->where('status', 1)->where('feature', 1)->orderBy('serial_number', 'ASC')->get();
        } else {
            $scats = [];
        }

        $scatsec = "";
        if ($version == 'default' || $version == 'dark') {
            $scatsec = "<div class='row'>";
            foreach ($scats as $key => $scategory) {
                $scatsec .= "<div class='col-xl-3 col-lg-4 col-sm-6'>
                <div class='single-category'>";
                if (!empty($scategory->image)) {
                    $scatsec .= "<div class='img-wrapper'>
                    <img class='lazy' data-src='" . url(FRONT_IMG_PATH . "service_category_icons/$scategory->image") . "' alt=''>
                    </div>";
                }
                $scatsec .= "<div class='text'>
                <h4>" . convertUtf8($scategory->name) . "</h4>
                <p>";
                if (strlen($scategory->short_text) > 112) {
                    $scatsec .= mb_substr($scategory->short_text,0,112,'utf-8') . "<span style='display: none;'>" . mb_substr($scategory->short_text,112,null,'utf-8') . "</span>
                    <a href='#' class='see-more'>" . __('see more') . "...</a>";
                } else {
                    $scatsec .= $scategory->short_text;
                }
                $scatsec .= "</p>
                <a href='" . route('front.services', ['category' => $scategory->id]) . "' class='readmore'>" . __('View Services') . "</a>
                </div>
                </div>
                </div>";
            }
            $scatsec .= "</div>";
        }

        return $scatsec;
    }
}

if (!function_exists('servicesSection')) {
    function servicesSection($currentLang, $version) {

        if (!empty($currentLang->services)) {
            $services = $currentLang->services()->where('feature', 1)->orderBy('serial_number', 'ASC')->get();
        } else {
            $services = [];
        }

        $servicesSec = "";
        if ($version == 'default' || $version == 'dark') {
            $servicesSec = "<div class='row'>";
            foreach ($services as $service) {
                $servicesSec .= "<div class='col-lg-4 col-md-6 col-sm-8'>
                <div class='services-item mt-30'>
                <div class='services-thumb'>
                <img class='lazy' data-src='" . url(FRONT_IMG_PATH . 'services/' . $service->main_image) . "' alt='service' />
                </div>
                <div class='services-content'>
                <a class='title'";
                if ($service->details_page_status == 1) {
                    $servicesSec .= "href='" . route('front.servicedetails', [$service->slug]) . "'";
                }
                $servicesSec .= "><h4>" . convertUtf8($service->title) . "</h4></a>
                <p>";
                if (strlen($service->summary) > 120) {
                    $servicesSec .= mb_substr($service->summary,0,120,'utf-8') . "<span style='display: none;'>" . mb_substr($service->summary,120,null,'utf-8') . "</span>
                    <a href='#' class='see-more'>" . __('see more') . "...</a>";
                } else {
                    $servicesSec .= $service->summary;
                }
                $servicesSec .= "</p>";
                if ($service->details_page_status == 1) {
                    $servicesSec .= "<a href='" . route('front.servicedetails', [$service->slug]) . "'>" . __('Read More') . " <i class='fas fa-plus'></i></a>";
                }
                $servicesSec .= "</div>
                </div>
                </div>";
            }
            $servicesSec .= "</div>";
        }

        return $servicesSec;
    }
}

if (!function_exists('portfoliosSection')) {
    function portfoliosSection($currentLang, $version) {
        if (!empty($currentLang->portfolios)) {
            $portfolios = $currentLang->portfolios()->where('feature', 1)->orderBy('serial_number', 'ASC')->get();
        } else {
            $portfolios = [];
        }

        $portfoliosSec = "";
        if ($version == 'default' || $version == 'dark') {
            $portfoliosSec .= "<div class='case-carousel owl-carousel owl-theme'>";
            foreach ($portfolios as $key => $portfolio) {
                $portfoliosSec .= "<div class='single-case single-case-bg-1 lazy' data-bg='" . url(FRONT_IMG_PATH . 'portfolios/featured/' . $portfolio->featured_image) . "'>
                <div class='outer-container'>
                <div class='inner-container'>
                <h4>";
                    $portfoliosSec .= strlen($portfolio->title) > 36 ? mb_substr($portfolio->title, 0, 36, 'utf-8') . '...' : $portfolio->title . "</h4>";
                if (!empty($portfolio->service)) {
                    $portfoliosSec .= "<p>" . convertUtf8($portfolio->service->title) . "</p>";
                }

                $portfoliosSec .= "<a href='" . route('front.portfoliodetails', [$portfolio->slug]) . "' class='readmore-btn'><span>" . __('Read More') . "</span></a>;

                </div>
                </div>
                </div>";
            }
            $portfoliosSec .= "</div>";
        }

        return $portfoliosSec;
    }
}

if (!function_exists('teamSection')) {
    function teamSection($currentLang, $version) {
        if (!empty($currentLang->members)) {
            $members = $currentLang->members()->where('feature', 1)->get();
        } else {
            $members = [];
        }

        $teamSec = "";
        if ($version == 'default' || $version == 'dark') {
            $teamSec .= "<div class='team-carousel common-carousel owl-carousel owl-theme'>";
            foreach ($members as $key => $member) {
                $teamSec .= "<div class='single-team-member'>
                <div class='team-img-wrapper'>
                <img class='lazy' data-src='" . url(FRONT_IMG_PATH . 'members/' . $member->image) . "' alt=''>
                <div class='social-accounts'>
                <ul class='social-account-lists'>";
                if (!empty($member->facebook)) {
                    $teamSec .= "<li class='single-social-account'><a href='" . $member->facebook . "'><i class='fab fa-facebook-f'></i></a></li>";
                }
                if (!empty($member->twitter)) {
                    $teamSec .= "<li class='single-social-account'><a href='" . $member->twitter . "'><i class='fab fa-twitter'></i></a></li>";
                }
                if (!empty($member->linkedin)) {
                    $teamSec .= "<li class='single-social-account'><a href='" . $member->linkedin . "'><i class='fab fa-linkedin-in'></i></a></li>";
                }
                if (!empty($member->instagram)) {
                    $teamSec .= "<li class='single-social-account'><a href='" . $member->instagram . "'><i class='fab fa-instagram'></i></a></li>";
                }
                $teamSec .= "</ul>
                </div>
                </div>
                <div class='member-info'>
                <h5 class='member-name'>" . convertUtf8($member->name) . "</h5>
                <small>" . convertUtf8($member->rank) . "</small>
                </div>
                </div>";
            }
            $teamSec .= "</div>";
        }

        return $teamSec;
    }
}

if (!function_exists('statisticsSection')) {
    function statisticsSection($currentLang, $version) {
        if (!empty($currentLang->statistics)) {
            $statistics = $currentLang->statistics()->orderBy('serial_number', 'ASC')->get();
        } else {
            $statistics = [];
        }

        $statisticSec = "";
        if ($version == 'default' || $version == 'dark') {
            $bs = BasicSetting::firstOrFail();

            $statisticSec .= "<div class='row no-gutters'>";
            foreach ($statistics as $key => $statistic) {
                $statisticSec .= "<div class='col-lg-3 col-md-6'>
                <div class='round' data-value='1' data-number='" . convertUtf8($statistic->quantity) . "' data-size='200' data-thickness='6' data-fill='{&quot;color&quot;: &quot;#" . $bs->base_color . "&quot;}'>
                <strong></strong>
                <h5><i class='" . $statistic->icon . "'></i> " . convertUtf8($statistic->title) . "</h5>
                </div>
                </div>";
            }
            $statisticSec .= "</div>";
        }

        return $statisticSec;
    }
}

if (!function_exists('testimonialSection')) {
    function testimonialSection($currentLang, $version) {
        if (!empty($currentLang->testimonials)) {
            $testimonials = $currentLang->testimonials()->orderBy('serial_number', 'ASC')->get();
        } else {
            $testimonials = [];
        }

        $testimonialSec = "";
        if ($version == 'default' || $version == 'dark') {
            $testimonialSec .= "<div class='testimonial-carousel owl-carousel owl-theme'>";
            foreach ($testimonials as $key => $testimonial) {
                $testimonialSec .= "<div class='single-testimonial'>
                <div class='img-wrapper'><img class='lazy' data-src='" . url(FRONT_IMG_PATH . 'testimonials/' . $testimonial->image) . "' alt=''></div>
                <div class='client-desc'>
                <p class='comment'>" . convertUtf8($testimonial->comment) . "</p>
                <h6 class='name'>" . convertUtf8($testimonial->name) . "</h6>
                <p class='rank'>" . convertUtf8($testimonial->rank) . "</p>
                </div>
                </div>";
            }
            $testimonialSec .= "</div>";
        }

        return $testimonialSec;
    }
}


if (!function_exists('blogsSection')) {
    function blogsSection($currentLang, $version) {
        if (!empty($currentLang->blogs)) {
            $blogs = $currentLang->blogs()->orderBy('serial_number', 'ASC')->get();
        } else {
            $blogs = [];
        }

        $blogSec = "";
        if ($version == 'default' || $version == 'dark') {
            $blogSec .= "<div class='blog-carousel common-carousel owl-carousel owl-theme'>";
            foreach ($blogs as $key => $blog) {
                $blogSec .= "<div class='single-blog'>
                <div class='blog-img-wrapper'>
                <img src='" . url(FRONT_IMG_PATH . 'blogs/' . $blog->main_image) . "' alt=''>
                </div>
                <div class='blog-txt'>";

                $blogDate = \Carbon\Carbon::parse($blog->created_at)->locale("$currentLang->code");
                $blogDate = $blogDate->translatedFormat('jS F, Y');


                $blogSec .= "<p class='date'><small>" . __('By') .  " <span class='username'>" . __('Admin') . "</span></small> | <small>" . $blogDate . "</small> </p>

                <h4 class='blog-title'><a href='" . route('front.blogdetails', [$blog->slug]) . "'>" . (strlen($blog->title) > 40 ? mb_substr($blog->title, 0, 40, 'utf-8') . '...' : $blog->title) . "</a></h4>


                <p class='blog-summary'>" . (strlen(strip_tags($blog->content)) > 100 ? mb_substr(strip_tags($blog->content), 0, 100, 'utf-8') . '...' : strip_tags($blog->content)) . "</p>


                <a href='" . route('front.blogdetails', [$blog->slug]) . "' class='readmore-btn'><span>" . __('Read More') . "</span></a>

                </div>
                </div>";
            }
            $blogSec .= "</div>";
        }

        return $blogSec;
    }
}

if (!function_exists('approachSection')) {
    function approachSection($currentLang, $version) {
        if (!empty($currentLang->points)) {
            $points = $currentLang->points()->orderBy('serial_number', 'ASC')->get();
        } else {
            $points = [];
        }

        $approachsec = "";
        if ($version == 'default' || $version == 'dark') {
            $approachsec .= "<ul class='approach-lists'>";
            foreach ($points as $key => $point) {
                $approachsec .= "<li class='single-approach' data-gjs-draggable='false' data-gjs-editable='false' data-gjs-removable='false' data-gjs-propagate=" . '["removable","editable","draggable"]' . ">
                <div class='approach-icon-wrapper'><i class='" . $point->icon . "'></i></div>
                <div class='approach-text'>
                <h4>" . convertUtf8($point->title) . "</h4>
                <p>";
                if (strlen($point->short_text) > 150) {
                    $approachsec .= mb_substr($point->short_text,0,150,'utf-8') . "<span style='display: none;'>" . mb_substr($point->short_text,150,null,'utf-8') . "</span>
                    <a href='#' class='see-more'>" . __('see more') . "...</a>";
                } else {
                    $approachsec .= $point->short_text;
                }
                $approachsec .= "</p>
                </div>
                </li>";
            }
            $approachsec .= "</ul>";
        }

        return $approachsec;
    }
}

if (!function_exists('partnerSection')) {
    function partnerSection($currentLang, $version) {
        if (!empty($currentLang->partners)) {
            $partners = $currentLang->partners()->orderBy('serial_number', 'ASC')->get();
        } else {
            $partners = [];
        }

        $partnerSec = "";
        if ($version == 'default' || $version == 'dark') {
            $partnerSec .= "<div class='partner-carousel common-carousel owl-carousel owl-theme'>";
            foreach ($partners as $key => $partner) {
                $partnerSec .= "<a class='single-partner-item d-block' href='" . $partner->url . "' target='_blank'>
                <div class='outer-container'>
                <div class='inner-container'>
                <img class='lazy' data-src='" . url(FRONT_IMG_PATH . 'partners/' . $partner->image) . "' alt=''>
                </div>
                </div>
                </a>";
            }
            $partnerSec .= "</div>";
        }

        return $partnerSec;
    }
}



if (!function_exists('newsletterSection')) {
    function newsletterSection($currentLang, $version) {
        $bs = $currentLang->basic_setting;

        $newsletterSec = '';
        $csrf = csrf_field();
        
        $newsletterSec = "<div class='newsletter-form'>
            <form class='footer-newsletter' id='footerSubscribeForm' action='" . route('front.subscribe') . "' method='post'>"
            . $csrf . 
            "<div class='form_group'>
                    <input type='email' class='form_control' placeholder='" . __('Enter Email Address') . "' name='email' value='' required>
                    <button class='main-btn'>" . __('Subscribe') . "</button>
                </div>
            </form>
        </div>";

        return $newsletterSec;
    }
}


