<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // basic_settings is already at MySQL's 65535-byte row-size ceiling —
        // any new column here must be TEXT, never VARCHAR (even a single
        // extra VARCHAR(255) pushes it over).
        Schema::table('basic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings', 'google_analytics_measurement_id')) {
                $table->text('google_analytics_measurement_id')->nullable()->after('google_analytics_script');
            }
            if (!Schema::hasColumn('basic_settings', 'gtm_container_id')) {
                $table->text('gtm_container_id')->nullable()->after('google_analytics_measurement_id');
            }
            if (!Schema::hasColumn('basic_settings', 'google_site_verification')) {
                $table->text('google_site_verification')->nullable()->after('gtm_container_id');
            }
            if (!Schema::hasColumn('basic_settings', 'bing_site_verification')) {
                $table->text('bing_site_verification')->nullable()->after('google_site_verification');
            }
            if (!Schema::hasColumn('basic_settings', 'default_social_image')) {
                $table->text('default_social_image')->nullable()->after('favicon');
            }
        });

        Schema::table('basic_settings_extended', function (Blueprint $table) {
            // NOT named facebook_pixel_script — the existing
            // facebook_pexel_script (typo baked in) is left untouched.
            if (!Schema::hasColumn('basic_settings_extended', 'facebook_pixel_id')) {
                $table->text('facebook_pixel_id')->nullable()->after('facebook_pexel_script');
            }
            if (!Schema::hasColumn('basic_settings_extended', 'facebook_capi_access_token')) {
                $table->text('facebook_capi_access_token')->nullable()->after('facebook_pixel_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn([
                'google_analytics_measurement_id',
                'gtm_container_id',
                'google_site_verification',
                'bing_site_verification',
                'default_social_image',
            ]);
        });

        Schema::table('basic_settings_extended', function (Blueprint $table) {
            $table->dropColumn(['facebook_pixel_id', 'facebook_capi_access_token']);
        });
    }
};
