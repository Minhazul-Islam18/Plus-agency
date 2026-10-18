<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHeroSliderOverlayFields extends Migration
{
    /**
     * The dark-theme hero slider's scrim (.dark-hslider-scrim, a 2-layer
     * gradient over the slide image) was a fixed rgb(9,13,22) with no admin
     * control at all — separate from the existing hero_overlay_color/
     * opacity pair, which only ever affected the light theme's static/
     * video/parallax/particles/water hero partials, never this one.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basic_settings_extended', function (Blueprint $table) {
            $table->string('hero_slider_overlay_color', 20)->nullable();
            $table->decimal('hero_slider_overlay_opacity', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('basic_settings_extended', function (Blueprint $table) {
            $table->dropColumn(['hero_slider_overlay_color', 'hero_slider_overlay_opacity']);
        });
    }
}
