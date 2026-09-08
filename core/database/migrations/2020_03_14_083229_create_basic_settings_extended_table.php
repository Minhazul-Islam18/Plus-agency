<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBasicSettingsExtendedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('basic_settings_extended')) {
        Schema::create('basic_settings_extended', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('language_id')->nullable();
            $table->string('pricing_title', 255)->nullable();
            $table->string('pricing_subtitle', 255)->nullable();
            $table->tinyInteger('pricing_section')->default(1)->comment('0 - deactive, 1 - active');
            $table->tinyInteger('is_order_package')->default(1)->comment('0 - deactive, 1 - active');
            $table->tinyInteger('is_packages')->default(1);
            $table->tinyInteger('cookie_alert_status')->default(1);
            $table->binary('cookie_alert_text')->nullable();
            $table->string('cookie_alert_button_text', 255)->nullable();
            $table->string('order_mail', 255)->nullable();
            $table->tinyInteger('is_career')->default(1);
            $table->tinyInteger('is_calendar')->default(1);
            $table->string('career_title', 255)->nullable();
            $table->string('career_subtitle', 255)->nullable();
            $table->string('event_calendar_title', 255)->nullable();
            $table->string('event_calendar_subtitle', 255)->nullable();
            $table->string('default_language_direction', 20)->default('ltr')->comment('ltr / rtl');
            $table->text('home_meta_keywords')->nullable();
            $table->text('home_meta_description')->nullable();
            $table->text('services_meta_keywords')->nullable();
            $table->text('services_meta_description')->nullable();
            $table->text('packages_meta_keywords')->nullable();
            $table->text('packages_meta_description')->nullable();
            $table->text('portfolios_meta_keywords')->nullable();
            $table->text('portfolios_meta_description')->nullable();
            $table->text('team_meta_keywords')->nullable();
            $table->text('team_meta_description')->nullable();
            $table->text('career_meta_keywords')->nullable();
            $table->text('career_meta_description')->nullable();
            $table->text('calendar_meta_keywords')->nullable();
            $table->text('calendar_meta_description')->nullable();
            $table->text('gallery_meta_keywords')->nullable();
            $table->text('gallery_meta_description')->nullable();
            $table->text('faq_meta_keywords')->nullable();
            $table->text('faq_meta_description')->nullable();
            $table->text('blogs_meta_keywords')->nullable();
            $table->text('blogs_meta_description')->nullable();
            $table->text('contact_meta_keywords')->nullable();
            $table->text('contact_meta_description')->nullable();
            $table->text('quote_meta_keywords')->nullable();
            $table->text('quote_meta_description')->nullable();
            $table->tinyInteger('is_facebook_pexel')->default(0);
            $table->text('facebook_pexel_script')->nullable();
            $table->timestamps();
        });
        }

        // Per-section Owl Carousel autoplay speed (ms) for the homepage's
        // carousel-driven sections — see admin/home/sections. Global (not
        // per-language), since the carousels themselves aren't.
        $speedColumns = [
            'tender_carousel_speed',
            'blog_carousel_speed',
            'portfolio_carousel_speed',
            'testimonial_carousel_speed',
            'team_carousel_speed',
            'partner_carousel_speed',
            // Single-project details page's thumbnail gallery (dwell time
            // per image before it auto-advances) — see
            // front/portfolio-details.blade.php. Not one of the homepage
            // carousels above, so kept separate from admin/home/sections.
            'portfolio_details_gallery_speed',
            // Approach section's 3-visible vertical autoplay carousel (see
            // front/default/partials/dark/approach.blade.php + dark-approach-fx.js).
            'approach_carousel_speed',
        ];
        foreach ($speedColumns as $column) {
            if (!Schema::hasColumn('basic_settings_extended', $column)) {
                // Matches each field's pre-existing hardcoded value: the
                // homepage carousels were 4500ms, the details-page gallery
                // was 7000ms (slower — needs enough dwell time to read
                // on-image text, not just glance at a card).
                $default = $column === 'portfolio_details_gallery_speed' ? 7000 : 4500;
                Schema::table('basic_settings_extended', function (Blueprint $table) use ($column, $default) {
                    $table->unsignedInteger($column)->default($default);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('basic_settings_extended');
    }
}
