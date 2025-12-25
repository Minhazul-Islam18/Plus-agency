<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactBreadcrumbFieldsToBasicSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->string('contact_breadcrumb_bg', 50)->nullable()->after('contact_subtitle');
            $table->string('contact_breadcrumb_overlay_color', 20)->nullable()->after('contact_breadcrumb_bg');
            $table->decimal('contact_breadcrumb_overlay_opacity', 3, 2)->nullable()->after('contact_breadcrumb_overlay_color');
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
            $table->dropColumn(['contact_breadcrumb_bg', 'contact_breadcrumb_overlay_color', 'contact_breadcrumb_overlay_opacity']);
        });
    }
}
