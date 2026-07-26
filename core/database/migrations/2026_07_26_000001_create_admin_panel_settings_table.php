<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminPanelSettingsTable extends Migration
{
    /**
     * Singleton settings row for the admin panel itself (guard-global, not
     * per-language like BasicExtra/BasicExtended) — login page branding and
     * the login-lockout threshold.
     */
    public function up()
    {
        Schema::create('admin_panel_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
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
