<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlobalAndPagewiseSeoFields extends Migration
{
    /**
     * Three tiers of fallback, all added here:
     *  1. basic_settings: a truly global meta_title/description/keywords —
     *     the last-resort fallback when nothing more specific is set.
     *  2. basic_settings_extended / basic_settings_extra: per-listing-page
     *     meta_title + og_image, alongside the meta_keywords/description
     *     pairs those tables already had for Home/Blog/Portfolio/Services
     *     (and, new for Tenders, the full set — it had none at all).
     *  3. pages: meta_title + og_image, to match the meta_keywords/
     *     meta_description the custom Pages module already had.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
        });

        Schema::table('basic_settings_extended', function (Blueprint $table) {
            $table->text('home_meta_title')->nullable();
            $table->text('home_og_image')->nullable();
            $table->text('blogs_meta_title')->nullable();
            $table->text('blogs_og_image')->nullable();
            $table->text('portfolios_meta_title')->nullable();
            $table->text('portfolios_og_image')->nullable();
            $table->text('services_meta_title')->nullable();
            $table->text('services_og_image')->nullable();
        });

        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->text('tender_meta_keywords')->nullable();
            $table->text('tender_meta_description')->nullable();
            $table->text('tender_meta_title')->nullable();
            $table->text('tender_og_image')->nullable();
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->text('meta_title')->nullable();
            $table->text('og_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::table('basic_settings_extended', function (Blueprint $table) {
            $table->dropColumn([
                'home_meta_title', 'home_og_image',
                'blogs_meta_title', 'blogs_og_image',
                'portfolios_meta_title', 'portfolios_og_image',
                'services_meta_title', 'services_og_image',
            ]);
        });

        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->dropColumn(['tender_meta_keywords', 'tender_meta_description', 'tender_meta_title', 'tender_og_image']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'og_image']);
        });
    }
}
