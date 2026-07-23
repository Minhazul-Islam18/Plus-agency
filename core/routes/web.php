<?php

use Illuminate\Support\Facades\Route;
use App\Permalink;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::fallback(function () {
    return view('errors.404');
});

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth:admin', 'setLfmPath']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
    Route::post('summernote/upload', 'Admin\SummernoteController@uploadFileManager')->name('lfm.summernote.upload');
});

Route::get('/backup', 'Front\FrontendController@backup');


/*=======================================================
******************** Front Routes **********************
=======================================================*/

Route::post('/push', 'Front\PushController@store');

Route::group(['middleware' => 'setlang'], function () {
    Route::get('/', 'Front\FrontendController@index')->name('front.index');

    Route::post('/payment/instructions', 'Front\FrontendController@paymentInstruction')->name('front.payment.instructions');


    Route::post('/sendmail', 'Front\FrontendController@sendmail')->name('front.sendmail')->middleware('throttle:3,10');
    Route::post('/subscribe', 'Front\FrontendController@subscribe')->name('front.subscribe');


    Route::get('/team', 'Front\FrontendController@team')->name('front.team');
    Route::get('/gallery', 'Front\FrontendController@gallery')->name('front.gallery');
    Route::get('/faq', 'Front\FrontendController@faq')->name('front.faq');

    // change language routes
    Route::get('/changelanguage/{lang}', 'Front\FrontendController@changeLanguage')->name('changeLanguage');

    // client feedback route
    Route::get('/feedback', 'Front\FeedbackController@feedback')->name('feedback');
    Route::post('/store_feedback', 'Front\FeedbackController@storeFeedback')->name('store_feedback');
});

Route::group(['middleware' => ['web', 'setlang']], function () {
    Route::post('/login', 'User\LoginController@login')->name('user.login.submit');

    Route::get('/login/facebook', 'User\LoginController@redirectToFacebook')->name('front.facebook.login');
    Route::get('/login/facebook/callback', 'User\LoginController@handleFacebookCallback')->name('front.facebook.callback');

    Route::get('/login/google', 'User\LoginController@redirectToGoogle')->name('front.google.login');
    Route::get('/login/google/callback', 'User\LoginController@handleGoogleCallback')->name('front.google.callback');

    Route::get('/register', 'User\RegisterController@registerPage')->name('user-register');
    Route::post('/register/submit', 'User\RegisterController@register')->name('user-register-submit');
    Route::get('/register/verify/{token}', 'User\RegisterController@token')->name('user-register-token');
    Route::get('/forgot', 'User\ForgotController@showforgotform')->name('user-forgot');
    Route::post('/forgot', 'User\ForgotController@forgot')->name('user-forgot-submit');
});

/** Health probe for uptime monitors / load balancers **/
Route::get('/health', 'HealthController')->name('health')->middleware('throttle:60,1');

/** Tender Frontend Routes **/
Route::post('/tender/purchase/submit', 'Front\TenderController@purchase')->name('tender.purchase.submit')->middleware('throttle:10,1');
Route::get('/tender/purchase/complete', 'Front\TenderController@purchaseComplete')->name('tender.purchase.complete');
Route::post('/tender/paid-modules', 'Front\TenderController@paidModules')->name('tender.paid_modules')->middleware('throttle:30,1');

// Tender online payment gateways
Route::post('/tender/payment/stripe',           'Payment\Tender\StripeController@process')->name('tender.payment.stripe');
Route::post('/tender/payment/razorpay',         'Payment\Tender\RazorpayController@redirect')->name('tender.payment.razorpay');
Route::post('/tender/payment/razorpay/notify',  'Payment\Tender\RazorpayController@notify')->name('tender.razorpay.notify');
Route::get('/tender/payment/razorpay/cancel',   'Payment\Tender\RazorpayController@cancel')->name('tender.razorpay.cancel');
Route::post('/tender/payment/moneroo',          'Payment\Tender\MonerooController@redirect')->name('tender.payment.moneroo');
Route::get('/tender/payment/moneroo/notify',    'Payment\Tender\MonerooController@notify')->name('tender.moneroo.notify');
Route::get('/tender/payment/moneroo/cancel',    'Payment\Tender\MonerooController@cancel')->name('tender.moneroo.cancel');

/** Static fallback for dynamic permalink routes needed by FMF views **/
Route::get('/contact', 'Front\FrontendController@contact')->name('front.contact');

/** Find My Files — Secure File Recovery **/
Route::get('/find-my-files', 'Front\FindMyFilesController@index')->name('find_my_files');
Route::post('/find-my-files/request-link', 'Front\FindMyFilesController@requestLink')->name('find_my_files.request_link')->middleware('throttle:5,1');
Route::get('/find-my-files/link-sent', 'Front\FindMyFilesController@linkSent')->name('find_my_files.link_sent');
Route::get('/find-my-files/security-verification', 'Front\FindMyFilesController@securityInfo')->name('find_my_files.security_info');
Route::get('/find-my-files/download', 'Front\FindMyFilesController@download')->name('find_my_files.download')->middleware('throttle:30,1');
Route::get('/find-my-files/download/stream', 'Front\FindMyFilesController@downloadStream')->name('find_my_files.stream')->middleware('throttle:20,1');

/** Find My Files — OTP Method (brute-force + SMS/email cost sensitive) **/
Route::post('/find-my-files/otp/request', 'Front\FindMyFilesController@requestOtp')->name('find_my_files.otp_request')->middleware('throttle:3,1');
Route::post('/find-my-files/otp/verify',  'Front\FindMyFilesController@verifyOtp')->name('find_my_files.otp_verify')->middleware('throttle:6,1');
Route::post('/find-my-files/otp/resend',  'Front\FindMyFilesController@resendOtp')->name('find_my_files.otp_resend')->middleware('throttle:3,1');

/** Find My Files — Payment Reference Method **/
Route::post('/find-my-files/payment-ref', 'Front\FindMyFilesController@requestByPaymentRef')->name('find_my_files.payment_ref')->middleware('throttle:6,1');

/** Find My Files — Expired Link Regenerate **/
Route::post('/find-my-files/regenerate', 'Front\FindMyFilesController@requestRegenerate')->name('find_my_files.regenerate')->middleware('throttle:5,1');








Route::group(['middleware' => ['web', 'setlang']], function () {
    Route::get('/login', 'User\LoginController@showLoginForm')->name('user.login');
    Route::post('/login', 'User\LoginController@login')->name('user.login.submit');
    Route::get('/register', 'User\RegisterController@registerPage')->name('user-register');
    Route::post('/register/submit', 'User\RegisterController@register')->name('user-register-submit');
    Route::get('/register/verify/{token}', 'User\RegisterController@token')->name('user-register-token');
    Route::get('/forgot', 'User\ForgotController@showforgotform')->name('user-forgot');
    Route::post('/forgot', 'User\ForgotController@forgot')->name('user-forgot-submit');
});


Route::group(['prefix' => 'user', 'middleware' => ['auth', 'userstatus', 'setlang']], function () {
    // Summernote image upload
    Route::post('/summernote/upload', 'User\SummernoteController@upload')->name('user.summernote.upload');

    Route::get('/dashboard', 'User\UserController@index')->name('user-dashboard');
    Route::get('/reset', 'User\UserController@resetform')->name('user-reset');
    Route::post('/reset', 'User\UserController@reset')->name('user-reset-submit');
    Route::get('/profile', 'User\UserController@profile')->name('user-profile');
    Route::post('/profile', 'User\UserController@profileupdate')->name('user-profile-update');
    Route::get('/logout', 'User\LoginController@logout')->name('user-logout');
});

/*=======================================================
******************** Admin Routes **********************
=======================================================*/

Route::group(['prefix' => 'admin', 'middleware' => 'guest:admin'], function () {
    Route::post('/login', 'Admin\LoginController@authenticate')->name('admin.auth');

    Route::get('/mail-form', 'Admin\ForgetController@mailForm')->name('admin.forget.form');
    Route::post('/sendmail', 'Admin\ForgetController@sendmail')->name('admin.forget.mail');
});


Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin', 'checkstatus', 'setLfmPath']], function () {

    // RTL check
    Route::get('/rtlcheck/{langid}', 'Admin\LanguageController@rtlcheck')->name('admin.rtlcheck');

    // Summernote image upload
    Route::post('/summernote/upload', 'Admin\SummernoteController@upload')->name('admin.summernote.upload');

    // Admin logout Route
    Route::get('/logout', 'Admin\LoginController@logout')->name('admin.logout');

    Route::group(['middleware' => 'checkpermission:Dashboard'], function () {
        // Admin Dashboard Routes
        Route::get('/dashboard', 'Admin\DashboardController@dashboard')->name('admin.dashboard');
    });


    // Admin Profile Routes
    Route::get('/changePassword', 'Admin\ProfileController@changePass')->name('admin.changePass');
    Route::post('/profile/updatePassword', 'Admin\ProfileController@updatePassword')->name('admin.updatePassword');
    Route::get('/profile/edit', 'Admin\ProfileController@editProfile')->name('admin.editProfile');
    Route::post('/propic/update', 'Admin\ProfileController@updatePropic')->name('admin.propic.update');
    Route::post('/profile/update', 'Admin\ProfileController@updateProfile')->name('admin.updateProfile');


    Route::group(['middleware' => 'checkpermission:Theme & Home'], function () {
        // Admin Home Version Setting Routes
        Route::get('/home-settings', 'Admin\BasicController@homeSettings')->name('admin.homeSettings');
        Route::post('/homeSettings/post', 'Admin\BasicController@updateHomeSettings')->name('admin.homeSettings.update');
    });


    Route::group(['middleware' => 'checkpermission:Basic Settings'], function () {

        // Admin File Manager Routes
        Route::get('/file-manager', 'Admin\BasicController@fileManager')->name('admin.file-manager');

        // Admin Logo Routes
        Route::get('/logo', 'Admin\BasicController@logo')->name('admin.logo');
        Route::post('/logo/post', 'Admin\BasicController@updatelogo')->name('admin.logo.update');


        // Admin preloader Routes
        Route::get('/preloader', 'Admin\BasicController@preloader')->name('admin.preloader');
        Route::post('/preloader/post', 'Admin\BasicController@updatepreloader')->name('admin.preloader.update');


        // Admin Scripts Routes
        Route::get('/feature/settings', 'Admin\BasicController@featuresettings')->name('admin.featuresettings');
        Route::post('/feature/settings/update', 'Admin\BasicController@updatefeatrue')->name('admin.featuresettings.update');

        // Admin Basic Information Routes
        Route::get('/basicinfo', 'Admin\BasicController@basicinfo')->name('admin.basicinfo');
        Route::post('/basicinfo/{langid}/post', 'Admin\BasicController@updatebasicinfo')->name('admin.basicinfo.update');
        Route::post('/basicinfo/post', 'Admin\BasicController@updatebasicinfo')->name('admin.basicinfo.update.default');

        // Admin Email Settings Routes
        Route::get('/mail-from-admin', 'Admin\EmailController@mailFromAdmin')->name('admin.mailFromAdmin');
        Route::post('/mail-from-admin/update', 'Admin\EmailController@updateMailFromAdmin')->name('admin.mailfromadmin.update');
        Route::get('/mail-to-admin', 'Admin\EmailController@mailToAdmin')->name('admin.mailToAdmin');
        Route::post('/mail-to-admin/update', 'Admin\EmailController@updateMailToAdmin')->name('admin.mailtoadmin.update');
        Route::get('/email-templates', 'Admin\EmailController@templates')->name('admin.email.templates');
        Route::get('/email-template/{id}/edit', 'Admin\EmailController@editTemplate')->name('admin.email.editTemplate');
        Route::post('/emailtemplate/{id}/update', 'Admin\EmailController@templateUpdate')->name('admin.email.templateUpdate');

        // Admin Email Settings Routes
        Route::get('/mail-from-admin', 'Admin\EmailController@mailFromAdmin')->name('admin.mailFromAdmin');
        Route::post('/mail-from-admin/update', 'Admin\EmailController@updateMailFromAdmin')->name('admin.mailfromadmin.update');
        Route::get('/mail-to-admin', 'Admin\EmailController@mailToAdmin')->name('admin.mailToAdmin');
        Route::post('/mail-to-admin/update', 'Admin\EmailController@updateMailToAdmin')->name('admin.mailtoadmin.update');


        // Admin Support Routes
        Route::get('/support', 'Admin\BasicController@support')->name('admin.support');
        Route::post('/support/{langid}/post', 'Admin\BasicController@updatesupport')->name('admin.support.update');


        // Admin Page Heading Routes
        Route::get('/heading', 'Admin\BasicController@heading')->name('admin.heading');
        Route::post('/heading/{langid}/update', 'Admin\BasicController@updateheading')->name('admin.heading.update');


        // Admin Scripts Routes
        Route::get('/script', 'Admin\BasicController@script')->name('admin.script');
        Route::post('/script/update', 'Admin\BasicController@updatescript')->name('admin.script.update');

        // Admin Social Routes
        Route::get('/social', 'Admin\SocialController@index')->name('admin.social.index');
        Route::post('/social/store', 'Admin\SocialController@store')->name('admin.social.store');
        Route::get('/social/{id}/edit', 'Admin\SocialController@edit')->name('admin.social.edit');
        Route::post('/social/update', 'Admin\SocialController@update')->name('admin.social.update');
        Route::post('/social/delete', 'Admin\SocialController@delete')->name('admin.social.delete');
        Route::post('/social/status', 'Admin\SocialController@status')->name('admin.social.status');

        // Admin SEO Information Routes
        Route::get('/seo', 'Admin\BasicController@seo')->name('admin.seo');
        Route::post('/seo/{langid}/update', 'Admin\BasicController@updateseo')->name('admin.seo.update');


        // Admin Maintanance Mode Routes
        Route::get('/maintainance', 'Admin\BasicController@maintainance')->name('admin.maintainance');
        Route::post('/maintainance/{langid}/update', 'Admin\BasicController@updatemaintainance')->name('admin.maintainance.update');

        // Admin Section Customization Routes
        Route::get('/sections', 'Admin\BasicController@sections')->name('admin.sections.index');
        Route::post('/sections/update', 'Admin\BasicController@updatesections')->name('admin.sections.update');

        // Admin Offer Banner Routes
        Route::get('/announcement', 'Admin\BasicController@announcement')->name('admin.announcement');
        Route::post('/announcement/{langid}/update', 'Admin\BasicController@updateannouncement')->name('admin.announcement.update');


        // Admin Section Customization Routes
        Route::get('/sections', 'Admin\BasicController@sections')->name('admin.sections.index');
        Route::post('/sections/update', 'Admin\BasicController@updatesections')->name('admin.sections.update');


        // Admin Section Customization Routes
        Route::get('/sections', 'Admin\BasicController@sections')->name('admin.sections.index');
        Route::post('/sections/update', 'Admin\BasicController@updatesections')->name('admin.sections.update');

        // Admin Cookie Alert Routes
        Route::get('/cookie-alert', 'Admin\BasicController@cookiealert')->name('admin.cookie.alert');
        Route::post('/cookie-alert/{langid}/update', 'Admin\BasicController@updatecookie')->name('admin.cookie.update');


        // Admin Payment Gateways
        Route::get('/gateways', 'Admin\GatewayController@index')->name('admin.gateway.index');
        Route::post('/stripe/update', 'Admin\GatewayController@stripeUpdate')->name('admin.stripe.update');
        Route::post('/paypal/update', 'Admin\GatewayController@paypalUpdate')->name('admin.paypal.update');
        Route::post('/paystack/update', 'Admin\GatewayController@paystackUpdate')->name('admin.paystack.update');
        Route::post('/paytm/update', 'Admin\GatewayController@paytmUpdate')->name('admin.paytm.update');
        Route::post('/flutterwave/update', 'Admin\GatewayController@flutterwaveUpdate')->name('admin.flutterwave.update');
        Route::post('/instamojo/update', 'Admin\GatewayController@instamojoUpdate')->name('admin.instamojo.update');
        Route::post('/mollie/update', 'Admin\GatewayController@mollieUpdate')->name('admin.mollie.update');
        Route::post('/razorpay/update', 'Admin\GatewayController@razorpayUpdate')->name('admin.razorpay.update');
        Route::post('/mercadopago/update', 'Admin\GatewayController@mercadopagoUpdate')->name('admin.mercadopago.update');
        Route::post('/payumoney/update', 'Admin\GatewayController@payumoneyUpdate')->name('admin.payumoney.update');
        Route::post('/moneroo/update', 'Admin\GatewayController@monerooUpdate')->name('admin.moneroo.update');
        Route::get('/offline/gateways', 'Admin\GatewayController@offline')->name('admin.gateway.offline');
        Route::post('/offline/gateway/store', 'Admin\GatewayController@store')->name('admin.gateway.offline.store');
        Route::post('/offline/gateway/update', 'Admin\GatewayController@update')->name('admin.gateway.offline.update');
        Route::post('/offline/status', 'Admin\GatewayController@status')->name('admin.offline.status');
        Route::post('/offline/gateway/delete', 'Admin\GatewayController@delete')->name('admin.offline.gateway.delete');


        // Admin Language Routes
        Route::get('/languages', 'Admin\LanguageController@index')->name('admin.language.index');
        Route::get('/language/{id}/edit', 'Admin\LanguageController@edit')->name('admin.language.edit');
        Route::get('/language/{id}/edit/keyword', 'Admin\LanguageController@editKeyword')->name('admin.language.editKeyword');
        Route::post('/language/store', 'Admin\LanguageController@store')->name('admin.language.store');
        Route::post('/language/upload', 'Admin\LanguageController@upload')->name('admin.language.upload');
        Route::post('/language/{id}/uploadUpdate', 'Admin\LanguageController@uploadUpdate')->name('admin.language.uploadUpdate');
        Route::post('/language/{id}/default', 'Admin\LanguageController@default')->name('admin.language.default');
        Route::post('/language/{id}/delete', 'Admin\LanguageController@delete')->name('admin.language.delete');
        Route::post('/language/update', 'Admin\LanguageController@update')->name('admin.language.update');
        Route::post('/language/{id}/update/keyword', 'Admin\LanguageController@updateKeyword')->name('admin.language.updateKeyword');
        Route::post('/language/status', 'Admin\LanguageController@status')->name('admin.language.status');


        // Admin Sitemap Routes
        Route::get('/sitemap', 'Admin\SitemapController@index')->name('admin.sitemap.index');
        Route::post('/sitemap/store', 'Admin\SitemapController@store')->name('admin.sitemap.store');
        Route::get('/sitemap/{id}/update', 'Admin\SitemapController@update')->name('admin.sitemap.update');
        Route::post('/sitemap/{id}/delete', 'Admin\SitemapController@delete')->name('admin.sitemap.delete');
        Route::post('/sitemap/download', 'Admin\SitemapController@download')->name('admin.sitemap.download');

        // Admin Database Backup
        Route::get('/backup', 'Admin\BackupController@index')->name('admin.backup.index');
        Route::post('/backup/store', 'Admin\BackupController@store')->name('admin.backup.store');
        Route::post('/backup/{id}/delete', 'Admin\BackupController@delete')->name('admin.backup.delete');
        Route::post('/backup/download', 'Admin\BackupController@download')->name('admin.backup.download');


        // Admin Cache Clear Routes
        Route::get('/cache-clear', 'Admin\CacheController@clear')->name('admin.cache.clear');
    });


    Route::group(['middleware' => 'checkpermission:Content Management'], function () {
        // Admin Hero Section (Static Version) Routes
        Route::get('/herosection/static', 'Admin\HerosectionController@static')->name('admin.herosection.static');
        Route::post('/herosection/{langid}/update', 'Admin\HerosectionController@update')->name('admin.herosection.update');


        // Admin Hero Section (Slider Version) Routes
        Route::get('/herosection/sliders', 'Admin\SliderController@index')->name('admin.slider.index');
        Route::post('/herosection/slider/store', 'Admin\SliderController@store')->name('admin.slider.store');
        Route::get('/herosection/slider/{id}/edit', 'Admin\SliderController@edit')->name('admin.slider.edit');
        Route::post('/herosection/sliderupdate', 'Admin\SliderController@update')->name('admin.slider.update');
        Route::post('/herosection/slider/delete', 'Admin\SliderController@delete')->name('admin.slider.delete');


        // Admin Hero Section (Video Version) Routes
        Route::get('/herosection/video', 'Admin\HerosectionController@video')->name('admin.herosection.video');
        Route::post('/herosection/video/{langid}/update', 'Admin\HerosectionController@videoupdate')->name('admin.herosection.video.update');


        // Admin Hero Section (Parallax Version) Routes
        Route::get('/herosection/parallax', 'Admin\HerosectionController@parallax')->name('admin.herosection.parallax');
        Route::post('/herosection/parallax/update', 'Admin\HerosectionController@parallaxupdate')->name('admin.herosection.parallax.update');


        // Admin Feature Routes
        Route::get('/features', 'Admin\FeatureController@index')->name('admin.feature.index');
        Route::post('/feature/store', 'Admin\FeatureController@store')->name('admin.feature.store');
        Route::get('/feature/{id}/edit', 'Admin\FeatureController@edit')->name('admin.feature.edit');
        Route::post('/feature/update', 'Admin\FeatureController@update')->name('admin.feature.update');
        Route::post('/feature/delete', 'Admin\FeatureController@delete')->name('admin.feature.delete');
        Route::post('/feature/status', 'Admin\FeatureController@status')->name('admin.feature.status');

        // Admin Intro Section Routes
        Route::get('/introsection', 'Admin\IntrosectionController@index')->name('admin.introsection.index');
        Route::post('/introsection/{langid}/update', 'Admin\IntrosectionController@update')->name('admin.introsection.update');

        // Admin Service Section Routes
        Route::get('/servicesection', 'Admin\ServicesectionController@index')->name('admin.servicesection.index');
        Route::post('/servicesection/{langid}/update', 'Admin\ServicesectionController@update')->name('admin.servicesection.update');

        // Admin Approach Section Routes
        Route::get('/approach', 'Admin\ApproachController@index')->name('admin.approach.index');
        Route::post('/approach/store', 'Admin\ApproachController@store')->name('admin.approach.point.store');
        Route::get('/approach/{id}/pointedit', 'Admin\ApproachController@pointedit')->name('admin.approach.point.edit');
        Route::post('/approach/{langid}/update', 'Admin\ApproachController@update')->name('admin.approach.update');
        Route::post('/approach/pointupdate', 'Admin\ApproachController@pointupdate')->name('admin.approach.point.update');
        Route::post('/approach/pointdelete', 'Admin\ApproachController@pointdelete')->name('admin.approach.pointdelete');


        // Admin Statistic Section Routes
        Route::get('/statistics', 'Admin\StatisticsController@index')->name('admin.statistics.index');
        Route::post('/statistics/{langid}/upload', 'Admin\StatisticsController@upload')->name('admin.statistics.upload');
        Route::post('/statistics/store', 'Admin\StatisticsController@store')->name('admin.statistics.store');
        Route::get('/statistics/{id}/edit', 'Admin\StatisticsController@edit')->name('admin.statistics.edit');
        Route::post('/statistics/update', 'Admin\StatisticsController@update')->name('admin.statistics.update');
        Route::post('/statistics/delete', 'Admin\StatisticsController@delete')->name('admin.statistics.delete');
        Route::post('/statistics/{langid}/deletebg', 'Admin\StatisticsController@deletebg')->name('admin.statistics.deletebg');


        // Admin Call to Action Section Routes
        Route::get('/cta', 'Admin\CtaController@index')->name('admin.cta.index');
        Route::post('/cta/{langid}/update', 'Admin\CtaController@update')->name('admin.cta.update');

        // Admin Portfolio Section Routes
        Route::get('/portfoliosection', 'Admin\PortfoliosectionController@index')->name('admin.portfoliosection.index');
        Route::post('/portfoliosection/{langid}/update', 'Admin\PortfoliosectionController@update')->name('admin.portfoliosection.update');

        // Admin Testimonial Routes
        Route::get('/testimonials', 'Admin\TestimonialController@index')->name('admin.testimonial.index');
        Route::get('/testimonial/create', 'Admin\TestimonialController@create')->name('admin.testimonial.create');
        Route::post('/testimonial/store', 'Admin\TestimonialController@store')->name('admin.testimonial.store');
        Route::get('/testimonial/{id}/edit', 'Admin\TestimonialController@edit')->name('admin.testimonial.edit');
        Route::post('/testimonial/update', 'Admin\TestimonialController@update')->name('admin.testimonial.update');
        Route::post('/testimonial/delete', 'Admin\TestimonialController@delete')->name('admin.testimonial.delete');
        Route::post('/testimonialtext/{langid}/update', 'Admin\TestimonialController@textupdate')->name('admin.testimonialtext.update');

        // Admin Blog Section Routes
        Route::get('/blogsection', 'Admin\BlogsectionController@index')->name('admin.blogsection.index');
        Route::post('/blogsection/{langid}/update', 'Admin\BlogsectionController@update')->name('admin.blogsection.update');

        // Admin Partner Routes
        Route::get('/partners', 'Admin\PartnerController@index')->name('admin.partner.index');
        Route::post('/partner/store', 'Admin\PartnerController@store')->name('admin.partner.store');
        Route::get('/partner/{id}/edit', 'Admin\PartnerController@edit')->name('admin.partner.edit');
        Route::post('/partner/update', 'Admin\PartnerController@update')->name('admin.partner.update');
        Route::post('/partner/delete', 'Admin\PartnerController@delete')->name('admin.partner.delete');
        Route::post('/partner/{langid}/section-update', 'Admin\PartnerController@sectionUpdate')->name('admin.partner.section.update');

        // Admin Member Routes
        Route::get('/members', 'Admin\MemberController@index')->name('admin.member.index');
        Route::get('/member/create', 'Admin\MemberController@create')->name('admin.member.create');
        Route::post('/member/store', 'Admin\MemberController@store')->name('admin.member.store');
        Route::get('/member/{id}/edit', 'Admin\MemberController@edit')->name('admin.member.edit');
        Route::post('/member/update', 'Admin\MemberController@update')->name('admin.member.update');
        Route::post('/member/delete', 'Admin\MemberController@delete')->name('admin.member.delete');
        Route::post('/teamtext/{langid}/update', 'Admin\MemberController@textupdate')->name('admin.teamtext.update');
        Route::post('/member/feature', 'Admin\MemberController@feature')->name('admin.member.feature');



        // Admin Footer Logo Text Routes
        Route::get('/footers', 'Admin\FooterController@index')->name('admin.footer.index');
        Route::post('/footer/{langid}/update', 'Admin\FooterController@update')->name('admin.footer.update');


        // Admin Ulink Routes
        Route::get('/ulinks', 'Admin\UlinkController@index')->name('admin.ulink.index');
        Route::get('/ulink/create', 'Admin\UlinkController@create')->name('admin.ulink.create');
        Route::post('/ulink/store', 'Admin\UlinkController@store')->name('admin.ulink.store');
        Route::get('/ulink/{id}/edit', 'Admin\UlinkController@edit')->name('admin.ulink.edit');
        Route::post('/ulink/update', 'Admin\UlinkController@update')->name('admin.ulink.update');
        Route::post('/ulink/delete', 'Admin\UlinkController@delete')->name('admin.ulink.delete');


        // Service Settings Route
        Route::get('/service/settings', 'Admin\ServiceController@settings')->name('admin.service.settings');
        Route::post('/service/updateSettings/{langid}/update', 'Admin\ServiceController@updateSettings')->name('admin.service.updateSettings');
        Route::post('/service/{langid}/delete_breadcrumb_bg', 'Admin\ServiceController@deleteBreadcrumbBg')->name('admin.service.delete_breadcrumb_bg');


        // Admin Service Category Routes
        Route::get('/scategorys', 'Admin\ScategoryController@index')->name('admin.scategory.index');
        Route::post('/scategory/store', 'Admin\ScategoryController@store')->name('admin.scategory.store');
        Route::get('/scategory/{id}/edit', 'Admin\ScategoryController@edit')->name('admin.scategory.edit');
        Route::post('/scategory/update', 'Admin\ScategoryController@update')->name('admin.scategory.update');
        Route::post('/scategory/delete', 'Admin\ScategoryController@delete')->name('admin.scategory.delete');
        Route::post('/scategory/bulk-delete', 'Admin\ScategoryController@bulkDelete')->name('admin.scategory.bulk.delete');
        Route::post('/scategory/feature', 'Admin\ScategoryController@feature')->name('admin.scategory.feature');

        // Admin Services Routes
        Route::get('/services', 'Admin\ServiceController@index')->name('admin.service.index');
        Route::post('/service/store', 'Admin\ServiceController@store')->name('admin.service.store');
        Route::get('/service/{id}/edit', 'Admin\ServiceController@edit')->name('admin.service.edit');
        Route::post('/service/update', 'Admin\ServiceController@update')->name('admin.service.update');
        Route::post('/service/delete', 'Admin\ServiceController@delete')->name('admin.service.delete');
        Route::post('/service/bulk-delete', 'Admin\ServiceController@bulkDelete')->name('admin.service.bulk.delete');
        Route::get('/service/{langid}/getcats', 'Admin\ServiceController@getcats')->name('admin.service.getcats');
        Route::post('/service/feature', 'Admin\ServiceController@feature')->name('admin.service.feature');
        Route::post('/service/sidebar', 'Admin\ServiceController@sidebar')->name('admin.service.sidebar');


        // Admin Portfolio Routes
        Route::get('/portfolios', 'Admin\PortfolioController@index')->name('admin.portfolio.index');
        Route::get('/portfolio/create', 'Admin\PortfolioController@create')->name('admin.portfolio.create');
        Route::post('/portfolio/sliderstore', 'Admin\PortfolioController@sliderstore')->name('admin.portfolio.sliderstore');
        Route::post('/portfolio/sliderrmv', 'Admin\PortfolioController@sliderrmv')->name('admin.portfolio.sliderrmv');
        Route::post('/portfolio/store', 'Admin\PortfolioController@store')->name('admin.portfolio.store');
        Route::get('/portfolio/{id}/edit', 'Admin\PortfolioController@edit')->name('admin.portfolio.edit');
        Route::get('/portfolio/{id}/images', 'Admin\PortfolioController@images')->name('admin.portfolio.images');
        Route::post('/portfolio/sliderupdate', 'Admin\PortfolioController@sliderupdate')->name('admin.portfolio.sliderupdate');
        Route::post('/portfolio/update', 'Admin\PortfolioController@update')->name('admin.portfolio.update');
        Route::post('/portfolio/delete', 'Admin\PortfolioController@delete')->name('admin.portfolio.delete');
        Route::post('/portfolio/bulk-delete', 'Admin\PortfolioController@bulkDelete')->name('admin.portfolio.bulk.delete');
        Route::get('portfolio/{id}/getservices', 'Admin\PortfolioController@getservices')->name('admin.portfolio.getservices');
        Route::post('/portfolio/feature', 'Admin\PortfolioController@feature')->name('admin.portfolio.feature');
        Route::get('/portfolio/settings', 'Admin\PortfolioController@settings')->name('admin.portfolio.settings');
        Route::post('/portfolio/{langid}/update_settings', 'Admin\PortfolioController@updateSettings')->name('admin.portfolio.update_settings');
        Route::post('/portfolio/{langid}/delete_breadcrumb_bg', 'Admin\PortfolioController@deleteBreadcrumbBg')->name('admin.portfolio.delete_breadcrumb_bg');

        // Admin Blog Category Routes
        Route::get('/bcategorys', 'Admin\BcategoryController@index')->name('admin.bcategory.index');
        Route::post('/bcategory/store', 'Admin\BcategoryController@store')->name('admin.bcategory.store');
        Route::post('/bcategory/update', 'Admin\BcategoryController@update')->name('admin.bcategory.update');
        Route::post('/bcategory/delete', 'Admin\BcategoryController@delete')->name('admin.bcategory.delete');
        Route::post('/bcategory/bulk-delete', 'Admin\BcategoryController@bulkDelete')->name('admin.bcategory.bulk.delete');


        // Admin Blog Settings Routes
        Route::get('/blog/settings', 'Admin\BlogSettingsController@settings')->name('admin.blog.settings');
        Route::post('/blog/{langid}/update_settings', 'Admin\BlogSettingsController@updateSettings')->name('admin.blog.update_settings');
        Route::post('/blog/{langid}/delete_breadcrumb_bg', 'Admin\BlogSettingsController@deleteBreadcrumbBg')->name('admin.blog.delete_breadcrumb_bg');

        // Admin Blog Routes
        Route::get('/blogs', 'Admin\BlogController@index')->name('admin.blog.index');
        Route::post('/blog/store', 'Admin\BlogController@store')->name('admin.blog.store');
        Route::get('/blog/{id}/edit', 'Admin\BlogController@edit')->name('admin.blog.edit');
        Route::post('/blog/update', 'Admin\BlogController@update')->name('admin.blog.update');
        Route::post('/blog/delete', 'Admin\BlogController@delete')->name('admin.blog.delete');
        Route::post('/blog/bulk-delete', 'Admin\BlogController@bulkDelete')->name('admin.blog.bulk.delete');
        Route::get('/blog/{langid}/getcats', 'Admin\BlogController@getcats')->name('admin.blog.getcats');
        Route::post('/blog/sidebar', 'Admin\BlogController@sidebar')->name('admin.blog.sidebar');


        // Admin Blog Archive Routes
        Route::get('/archives', 'Admin\ArchiveController@index')->name('admin.archive.index');
        Route::post('/archive/store', 'Admin\ArchiveController@store')->name('admin.archive.store');
        Route::post('/archive/update', 'Admin\ArchiveController@update')->name('admin.archive.update');
        Route::post('/archive/delete', 'Admin\ArchiveController@delete')->name('admin.archive.delete');


        // Admin Gallery Settings Routes
        Route::get('/gallery/settings', 'Admin\GalleryCategoryController@settings')->name('admin.gallery.settings');
        Route::post('/gallery/{langid}/update_settings', 'Admin\GalleryCategoryController@updateSettings')->name('admin.gallery.update_settings');
        Route::post('/gallery/{langid}/delete_breadcrumb_bg', 'Admin\GalleryCategoryController@deleteBreadcrumbBg')->name('admin.gallery.delete_breadcrumb_bg');

        // Admin Gallery Category Routes
        Route::get('/gallery/categories', 'Admin\GalleryCategoryController@index')->name('admin.gallery.categories');
        Route::post('/gallery/store_category', 'Admin\GalleryCategoryController@store')->name('admin.gallery.store_category');
        Route::post('/gallery/update_category', 'Admin\GalleryCategoryController@update')->name('admin.gallery.update_category');
        Route::post('/gallery/delete_category', 'Admin\GalleryCategoryController@delete')->name('admin.gallery.delete_category');
        Route::post('/gallery/bulk_delete_category', 'Admin\GalleryCategoryController@bulkDelete')->name('admin.gallery.bulk_delete_category');

        // Admin Gallery Routes
        Route::get('/gallery', 'Admin\GalleryController@index')->name('admin.gallery.index');
        Route::get('/gallery/{langId}/get_categories', 'Admin\GalleryController@getCategories');
        Route::post('/gallery/store', 'Admin\GalleryController@store')->name('admin.gallery.store');
        Route::get('/gallery/{id}/edit', 'Admin\GalleryController@edit')->name('admin.gallery.edit');
        Route::post('/gallery/update', 'Admin\GalleryController@update')->name('admin.gallery.update');
        Route::post('/gallery/delete', 'Admin\GalleryController@delete')->name('admin.gallery.delete');
        Route::post('/gallery/bulk-delete', 'Admin\GalleryController@bulkDelete')->name('admin.gallery.bulk.delete');
        Route::post('/gallery/status', 'Admin\GalleryController@status')->name('admin.gallery.status');


        // Admin FAQ Settings Routes
        Route::get('/faq/settings', 'Admin\FAQCategoryController@settings')->name('admin.faq.settings');
        Route::post('/faq/{langid}/update_settings', 'Admin\FAQCategoryController@updateSettings')->name('admin.faq.update_settings');
        Route::post('/faq/{langid}/delete_breadcrumb_bg', 'Admin\FAQCategoryController@deleteBreadcrumbBg')->name('admin.faq.delete_breadcrumb_bg');

        // Admin FAQ Category Routes
        Route::get('/faq/categories', 'Admin\FAQCategoryController@index')->name('admin.faq.categories');
        Route::post('/faq/store_category', 'Admin\FAQCategoryController@store')->name('admin.faq.store_category');
        Route::post('/faq/update_category', 'Admin\FAQCategoryController@update')->name('admin.faq.update_category');
        Route::post('/faq/delete_category', 'Admin\FAQCategoryController@delete')->name('admin.faq.delete_category');
        Route::post('/faq/bulk_delete_category', 'Admin\FAQCategoryController@bulkDelete')->name('admin.faq.bulk_delete_category');

        // Admin FAQ Routes
        Route::get('/faqs', 'Admin\FaqController@index')->name('admin.faq.index');
        Route::get('/faq/create', 'Admin\FaqController@create')->name('admin.faq.create');
        Route::get('/faq/{langId}/get_categories', 'Admin\FaqController@getCategories');
        Route::post('/faq/store', 'Admin\FaqController@store')->name('admin.faq.store');
        Route::get('/faq/{id}/edit', 'Admin\FaqController@edit')->name('admin.faq.edit');
        Route::post('/faq/update', 'Admin\FaqController@update')->name('admin.faq.update');
        Route::post('/faq/delete', 'Admin\FaqController@delete')->name('admin.faq.delete');
        Route::post('/faq/bulk-delete', 'Admin\FaqController@bulkDelete')->name('admin.faq.bulk.delete');
        Route::post('/faq/status', 'Admin\FaqController@status')->name('admin.faq.status');


        // Admin Contact Routes
        Route::get('/contact', 'Admin\ContactController@index')->name('admin.contact.index');
        Route::post('/contact/{langid}/post', 'Admin\ContactController@update')->name('admin.contact.update');
        Route::post('/contact/{langid}/delete-bg', 'Admin\ContactController@deleteContactBg')->name('admin.contact.deletebg');
    });



    Route::group(['middleware' => 'checkpermission:Menu Builder'], function () {
        // Mega Menus Management Routes
        Route::get('/megamenus', 'Admin\MenuBuilderController@megamenus')->name('admin.megamenus');
        Route::get('/megamenus/edit', 'Admin\MenuBuilderController@megaMenuEdit')->name('admin.megamenu.edit');
        Route::post('/megamenus/update', 'Admin\MenuBuilderController@megaMenuUpdate')->name('admin.megamenu.update');

        // Menus Builder Management Routes
        Route::get('/menu-builder', 'Admin\MenuBuilderController@index')->name('admin.menu_builder.index');
        Route::post('/menu-builder/update', 'Admin\MenuBuilderController@update')->name('admin.menu_builder.update');

        // Permalinks Routes
        Route::get('/permalinks', 'Admin\MenuBuilderController@permalinks')->name('admin.permalinks.index');
        Route::post('/permalinks/update', 'Admin\MenuBuilderController@permalinksUpdate')->name('admin.permalinks.update');
    });


    Route::group(['middleware' => 'checkpermission:Announcement Popup'], function () {
        Route::get('popups', 'Admin\PopupController@index')->name('admin.popup.index');
        Route::get('popup/types', 'Admin\PopupController@types')->name('admin.popup.types');
        Route::get('popup/{id}/edit', 'Admin\PopupController@edit')->name('admin.popup.edit');
        Route::get('popup/create', 'Admin\PopupController@create')->name('admin.popup.create');
        Route::post('popup/store', 'Admin\PopupController@store')->name('admin.popup.store');
        Route::post('popup/delete', 'Admin\PopupController@delete')->name('admin.popup.delete');
        Route::post('popup/bulk-delete', 'Admin\PopupController@bulkDelete')->name('admin.popup.bulk.delete');
        Route::post('popup/status', 'Admin\PopupController@status')->name('admin.popup.status');
        Route::post('popup/update', 'Admin\PopupController@update')->name('admin.popup.update');
    });







    Route::group(['middleware' => 'checkpermission:Pages'], function () {
        // Menu Manager Routes
        Route::get('/pages', 'Admin\PageController@index')->name('admin.page.index');
        Route::get('/page/settings', 'Admin\PageController@settings')->name('admin.page.settings');
        Route::post('/page/update-settings', 'Admin\PageController@updateSettings')->name('admin.page.updateSettings');
        Route::get('/page/create', 'Admin\PageController@create')->name('admin.page.create');
        Route::post('/page/store', 'Admin\PageController@store')->name('admin.page.store');
        Route::get('/page/{menuID}/edit', 'Admin\PageController@edit')->name('admin.page.edit');
        Route::post('/page/update', 'Admin\PageController@update')->name('admin.page.update');
        Route::post('/page/delete', 'Admin\PageController@delete')->name('admin.page.delete');
        Route::post('/page/bulk-delete', 'Admin\PageController@bulkDelete')->name('admin.page.bulk.delete');
        Route::post('/page/{id}/delete-breadcrumb', 'Admin\PageController@deleteBreadcrumbImage')->name('admin.page.deleteBreadcrumb');
        Route::post('/upload/pagebuilder', 'Admin\PageController@uploadPbImage')->name('admin.pb.upload');
        Route::post('/remove/img/pagebuilder', 'Admin\PageController@removePbImage')->name('admin.pb.remove');
        Route::post('/upload/tui/pagebuilder', 'Admin\PageController@uploadPbTui')->name('admin.pb.tui.upload');
    });


    // Page Builder Routes
    Route::get('/pagebuilder/content', 'Admin\PageBuilderController@content')->name('admin.pagebuilder.content');
    Route::post('/pagebuilder/save', 'Admin\PageBuilderController@save')->name('admin.pagebuilder.save');





    Route::group(['middleware' => 'checkpermission:Tender Management'], function () {
        // Admin Tender Category Routes
        Route::get('/tender_categories', 'Admin\TenderCategoryController@index')->name('admin.tender_category.index');
        Route::post('/tender_category/store', 'Admin\TenderCategoryController@store')->name('admin.tender_category.store');
        Route::post('/tender_category/update', 'Admin\TenderCategoryController@update')->name('admin.tender_category.update');
        Route::post('/tender_category/delete', 'Admin\TenderCategoryController@delete')->name('admin.tender_category.delete');
        Route::post('/tender_category/bulk_delete', 'Admin\TenderCategoryController@bulkDelete')->name('admin.tender_category.bulk_delete');

        // Admin Tender Routes
        Route::get('/tenders', 'Admin\TenderController@index')->name('admin.tender.index');
        Route::get('/tender/create', 'Admin\TenderController@create')->name('admin.tender.create');
        Route::get('/tender/{langId}/get_categories', 'Admin\TenderController@getCategories');
        Route::post('/tender/store', 'Admin\TenderController@store')->name('admin.tender.store');
        Route::get('/tender/{id}/edit', 'Admin\TenderController@edit')->name('admin.tender.edit');
        Route::post('/tender/update', 'Admin\TenderController@update')->name('admin.tender.update');
        Route::post('/tender/delete', 'Admin\TenderController@delete')->name('admin.tender.delete');
        Route::post('/tender/bulk_delete', 'Admin\TenderController@bulkDelete')->name('admin.tender.bulk_delete');
        Route::post('/tender/featured', 'Admin\TenderController@featured')->name('admin.tender.featured');
        Route::get('/tender/purchase-log', 'Admin\TenderController@purchaseLog')->name('admin.tender.purchaseLog');
        Route::post('/tender/purchase/payment-status', 'Admin\TenderController@purchasePaymentStatus')->name('admin.tender.purchasePaymentStatus');
        Route::post('/tender/purchase/update-reference', 'Admin\TenderController@purchaseUpdateReference')->name('admin.tender.purchaseUpdateReference');
        Route::post('/tender/purchase/suspend', 'Admin\TenderController@purchaseSuspend')->name('admin.tender.purchaseSuspend');
        Route::post('/tender/purchase/delete', 'Admin\TenderController@purchaseDelete')->name('admin.tender.purchaseDelete');
        Route::post('/tender/purchase/bulk_delete', 'Admin\TenderController@purchaseBulkOrderDelete')->name('admin.tender.purchaseBulkOrderDelete');
        Route::get('/tender/purchase/{id}/invoice', 'Admin\TenderController@invoiceDownload')->name('admin.tender.invoiceDownload');
        Route::post('/tender/purchase/{id}/generate-invoice', 'Admin\TenderController@purchaseGenerateInvoice')->name('admin.tender.purchaseGenerateInvoice');

        // Admin Tender Blacklist Routes
        Route::get('/tender/blacklist', 'Admin\TenderBlacklistController@index')->name('admin.tender.blacklist');
        Route::post('/tender/blacklist/store', 'Admin\TenderBlacklistController@store')->name('admin.tender.blacklist.store');
        Route::post('/tender/blacklist/from-purchase', 'Admin\TenderBlacklistController@storeFromPurchase')->name('admin.tender.blacklist.fromPurchase');
        Route::post('/tender/blacklist/update', 'Admin\TenderBlacklistController@update')->name('admin.tender.blacklist.update');
        Route::post('/tender/blacklist/delete', 'Admin\TenderBlacklistController@destroy')->name('admin.tender.blacklist.delete');

        // Admin Tender Module Routes
        // One-off: move tender module files into secure storage (no terminal needed).
        // Visit ?cleanup=1 only AFTER confirming a real download works. Remove this
        // route once the migration is done.
        Route::get('/tender/migrate-files', 'Admin\TenderModuleController@migrateFiles')->name('admin.tender.migrate_files');

        Route::get('/tender/{id}/modules', 'Admin\TenderModuleController@index')->name('admin.tender.module.index');
        Route::post('/tender/module/store', 'Admin\TenderModuleController@store')->name('admin.tender.module.store');
        Route::post('/tender/module/update', 'Admin\TenderModuleController@update')->name('admin.tender.module.update');
        Route::post('/tender/module/delete', 'Admin\TenderModuleController@delete')->name('admin.tender.module.delete');
        Route::post('/tender/module/bulk_delete', 'Admin\TenderModuleController@bulkDelete')->name('admin.tender.module.bulk_delete');
        Route::post('/tender/module/status', 'Admin\TenderModuleController@status')->name('admin.tender.module.status');

        // Admin Tender Section Routes
        Route::get('/tender/module/{id}/sections', 'Admin\TenderSectionController@index')->name('admin.tender.module.section.index');
        Route::post('/tender/module/section/store', 'Admin\TenderSectionController@store')->name('admin.tender.module.section.store');
        Route::post('/tender/module/section/update', 'Admin\TenderSectionController@update')->name('admin.tender.module.section.update');
        Route::post('/tender/module/section/delete', 'Admin\TenderSectionController@delete')->name('admin.tender.module.section.delete');
        Route::post('/tender/module/section/bulk_delete', 'Admin\TenderSectionController@bulkDelete')->name('admin.tender.module.section.bulk_delete');

        Route::get('/tender/settings', 'Admin\TenderController@settings')->name('admin.tender.settings');
        Route::post('/tender/settings', 'Admin\TenderController@updateSettings')->name('admin.tender.updateSettings');
        Route::post('/tender/settings/delete-breadcrumb-bg', 'Admin\TenderController@deleteTenderBreadcrumbBg')->name('admin.tender.deleteTenderBreadcrumbBg');

        // PDF watermark self-test (no terminal needed — runs the seeder in-browser)
        Route::get('/tender/watermark-test', 'Admin\TenderController@watermarkTest')->name('admin.tender.watermarkTest');
        Route::get('/tender/watermark-test/cleanup', 'Admin\TenderController@watermarkTestCleanup')->name('admin.tender.watermarkTestCleanup');

        // Admin Tender Enroll Report Routes
        Route::get('/tender/enrolls/report', 'Admin\TenderController@report')->name('admin.tender.enrolls.report');
        Route::get('/tender/export/report', 'Admin\TenderController@exportReport')->name('admin.tender.enrolls.export');
    });



    Route::group(['middleware' => 'checkpermission:Users Management'], function () {
        // Register User start
        Route::get('register/users', 'Admin\RegisterUserController@index')->name('admin.register.user');
        Route::post('register/users/ban', 'Admin\RegisterUserController@userban')->name('register.user.ban');
        Route::post('register/users/email', 'Admin\RegisterUserController@emailStatus')->name('register.user.email');
        Route::get('register/user/details/{id}', 'Admin\RegisterUserController@view')->name('register.user.view');
        Route::post('register/user/delete', 'Admin\RegisterUserController@delete')->name('register.user.delete');
        Route::post('register/user/bulk-delete', 'Admin\RegisterUserController@bulkDelete')->name('register.user.bulk.delete');
        Route::get('register/user/{id}/changePassword', 'Admin\RegisterUserController@changePass')->name('register.user.changePass');
        Route::post('register/user/updatePassword', 'Admin\RegisterUserController@updatePassword')->name('register.user.updatePassword');
        //Register User end

        // Admin Push Notification Routes
        Route::get('/pushnotification/settings', 'Admin\PushController@settings')->name('admin.pushnotification.settings');
        Route::post('/pushnotification/update/settings', 'Admin\PushController@updateSettings')->name('admin.pushnotification.updateSettings');
        Route::get('/pushnotification/send', 'Admin\PushController@send')->name('admin.pushnotification.send');
        Route::post('/push', 'Admin\PushController@push')->name('admin.pushnotification.push');


        // Admin Subscriber Routes
        Route::get('/subscribers', 'Admin\SubscriberController@index')->name('admin.subscriber.index');
        Route::get('/mailsubscriber', 'Admin\SubscriberController@mailsubscriber')->name('admin.mailsubscriber');
        Route::post('/subscribers/sendmail', 'Admin\SubscriberController@subscsendmail')->name('admin.subscribers.sendmail');
        Route::post('/subscriber/delete', 'Admin\SubscriberController@delete')->name('admin.subscriber.delete');
        Route::post('/subscriber/bulk-delete', 'Admin\SubscriberController@bulkDelete')->name('admin.subscriber.bulk.delete');
    });







    Route::group(['middleware' => 'checkpermission:Role Management'], function () {
        // Admin Roles Routes
        Route::get('/roles', 'Admin\RoleController@index')->name('admin.role.index');
        Route::post('/role/store', 'Admin\RoleController@store')->name('admin.role.store');
        Route::post('/role/update', 'Admin\RoleController@update')->name('admin.role.update');
        Route::post('/role/delete', 'Admin\RoleController@delete')->name('admin.role.delete');
        Route::get('role/{id}/permissions/manage', 'Admin\RoleController@managePermissions')->name('admin.role.permissions.manage');
        Route::post('role/permissions/update', 'Admin\RoleController@updatePermissions')->name('admin.role.permissions.update');
    });

    Route::group(['middleware' => 'checkpermission:Users Management'], function () {
        // Admin Users Routes
        Route::get('/users', 'Admin\UserController@index')->name('admin.user.index');
        Route::post('/user/store', 'Admin\UserController@store')->name('admin.user.store');
        Route::get('/user/{id}/edit', 'Admin\UserController@edit')->name('admin.user.edit');
        Route::post('/user/update', 'Admin\UserController@update')->name('admin.user.update');
        Route::post('/user/delete', 'Admin\UserController@delete')->name('admin.user.delete');
    });



    Route::group(['middleware' => 'checkpermission:Client Feedbacks'], function () {
        // Admin View Client Feedbacks Routes
        Route::get('/feedbacks', 'Admin\FeedbackController@feedbacks')->name('admin.client_feedbacks');
        Route::post('/delete_feedback', 'Admin\FeedbackController@deleteFeedback')->name('admin.delete_feedback');
        Route::post('/feedback/bulk-delete', 'Admin\FeedbackController@bulkDelete')->name('admin.feedback.bulk.delete');
    });

    Route::group(['middleware' => 'checkpermission:Contact Messages'], function () {
        // Admin View Contact Messages Routes
        Route::get('/contact-messages', 'Admin\ContactMessageController@index')->name('admin.contact_messages');
        Route::post('/contact-message/delete', 'Admin\ContactMessageController@delete')->name('admin.delete_contact_message');
        Route::post('/contact-message/bulk-delete', 'Admin\ContactMessageController@bulkDelete')->name('admin.contact_message.bulk.delete');
        Route::post('/contact-message/approve', 'Admin\ContactMessageController@approve')->name('admin.contact_message.approve');
        Route::post('/contact-message/reject', 'Admin\ContactMessageController@reject')->name('admin.contact_message.reject');
        Route::post('/contact-message/reply', 'Admin\ContactMessageController@reply')->name('admin.contact_message.reply');
    });
});



// Dynamic Routes
Route::group(['middleware' => ['setlang']], function () {

    try { $wdPermalinks = Permalink::where('details', 1)->get(); } catch (\Exception $e) { $wdPermalinks = collect(); }
    foreach ($wdPermalinks as $pl) {
        $type = $pl->type;
        $permalink = $pl->permalink;

        if ($type == 'service_details') {
            Route::get("$permalink/{slug}", 'Front\FrontendController@servicedetails')->name('front.servicedetails');
        } elseif ($type == 'portfolio_details') {
            Route::get("$permalink/{slug}", 'Front\FrontendController@portfoliodetails')->name('front.portfoliodetails');
        } elseif ($type == 'tender_details') {
            Route::get("$permalink/{slug}", 'Front\TenderController@tenderDetails')->name('tender_details');
        } elseif ($type == 'blog_details') {
            Route::get("$permalink/{slug}", 'Front\FrontendController@blogdetails')->name('front.blogdetails');
        }
    }
});

// Dynamic Routes
Route::group(['middleware' => ['setlang']], function () {

    try { $wdPermalinks = Permalink::where('details', 0)->get(); } catch (\Exception $e) { $wdPermalinks = collect(); }
    foreach ($wdPermalinks as $pl) {
        $type = $pl->type;
        $permalink = $pl->permalink;


        if ($type == 'services') {
            $action = 'Front\FrontendController@services';
            $routeName = 'front.services';
        } elseif ($type == 'portfolios') {
            $action = 'Front\FrontendController@portfolios';
            $routeName = 'front.portfolios';
        } elseif ($type == 'team') {
            $action = 'Front\FrontendController@team';
            $routeName = 'front.team';
        } elseif ($type == 'tenders') {
            $action = 'Front\TenderController@tenders';
            $routeName = 'tenders';
        } elseif ($type == 'find_my_files') {
            $action = 'Front\FindMyFilesController@index';
            $routeName = 'find_my_files';
        } elseif ($type == 'gallery') {
            $action = 'Front\FrontendController@gallery';
            $routeName = 'front.gallery';
        } elseif ($type == 'faq') {
            $action = 'Front\FrontendController@faq';
            $routeName = 'front.faq';
        } elseif ($type == 'blogs') {
            $action = 'Front\FrontendController@blogs';
            $routeName = 'front.blogs';
        } elseif ($type == 'contact') {
            $action = 'Front\FrontendController@contact';
            $routeName = 'front.contact';
        } elseif ($type == 'login') {
            $action = 'User\LoginController@showLoginForm';
            $routeName = 'user.login';
        } elseif ($type == 'register') {
            $action = 'User\RegisterController@registerPage';
            $routeName = 'user-register';
        } elseif ($type == 'forget_password') {
            $action = 'User\ForgotController@showforgotform';
            $routeName = 'user-forgot';
        } elseif ($type == 'admin_login') {
            $action = 'Admin\LoginController@login';
            $routeName = 'admin.login';
            Route::get("$permalink", "$action")->name("$routeName")->middleware('guest:admin');
            continue;
        }

        Route::get("$permalink", "$action")->name("$routeName");
    }
});


// Dynamic Page Routes
Route::group(['middleware' => 'setlang'], function () {
    Route::get('/{slug}', 'Front\FrontendController@dynamicPage')->name('front.dynamicPage');
});
