<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUnusedSocialLoginAndWidgetFields extends Migration
{
    /**
     * Facebook Login / Google Login were admin fields with no actual
     * front-end login flow ever wired up (no Socialite, no route, no
     * button) — pure dead data. Tawk.to, Disqus, Appzi Feedback and
     * AddThis were real raw-script widgets rendered on the front end, but
     * the user asked to remove them outright, admin fields and all.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['tawk_to_script', 'is_tawkto', 'is_disqus', 'disqus_script', 'is_appzi', 'appzi_script', 'is_addthis', 'addthis_script']);
        });

        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->dropColumn(['is_facebook_login', 'facebook_app_id', 'facebook_app_secret', 'is_google_login', 'google_client_id', 'google_client_secret']);
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
            $table->text('tawk_to_script')->nullable();
            $table->tinyInteger('is_tawkto')->default(1);
            $table->tinyInteger('is_disqus')->default(1);
            $table->text('disqus_script')->nullable();
            $table->tinyInteger('is_appzi')->default(0)->comment('0 - deactive, 1 - active');
            $table->text('appzi_script')->nullable();
            $table->tinyInteger('is_addthis')->default(0)->comment('0 - deactive, 1 - active');
            $table->text('addthis_script')->nullable();
        });

        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->tinyInteger('is_facebook_login')->default(1)->comment('1 - Active, 0 - Deactive');
            $table->string('facebook_app_id', 100)->nullable();
            $table->string('facebook_app_secret', 100)->nullable();
            $table->tinyInteger('is_google_login')->default(1)->comment('1 - Active, 0 - Deactive');
            $table->string('google_client_id', 150)->nullable();
            $table->string('google_client_secret', 70)->nullable();
        });
    }
}
