<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminPanelSettingsTable extends Migration
{
    /**
     * Login page branding, one row per language (same pattern as
     * basic_settings) — platform name, tagline, feature list (variable
     * length per language) and copyright can all differ by language.
     * max_login_attempts lives here too for storage convenience, but is
     * only ever read from/written to the default language's row — a
     * lockout threshold has no meaningful per-language variant.
     */
    public function up()
    {
        Schema::create('admin_panel_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('language_id')->nullable();
            $table->string('login_logo')->nullable();
            $table->string('login_bg_image')->nullable();
            $table->string('platform_name')->nullable();
            $table->string('tagline')->default('Secure. Transparent. Efficient.');
            $table->json('features')->nullable();
            $table->string('copyright_text')->nullable();
            $table->unsignedInteger('max_login_attempts')->default(5);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_panel_settings');
    }
}
