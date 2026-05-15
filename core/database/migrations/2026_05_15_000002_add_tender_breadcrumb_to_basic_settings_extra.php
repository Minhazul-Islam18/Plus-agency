<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTenderBreadcrumbToBasicSettingsExtra extends Migration
{
    public function up()
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->string('tender_breadcrumb_bg')->nullable()->after('is_tender');
            $table->string('tender_breadcrumb_overlay_color', 20)->nullable()->after('tender_breadcrumb_bg');
            $table->decimal('tender_breadcrumb_overlay_opacity', 3, 2)->nullable()->after('tender_breadcrumb_overlay_color');
        });
    }

    public function down()
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->dropColumn(['tender_breadcrumb_bg', 'tender_breadcrumb_overlay_color', 'tender_breadcrumb_overlay_opacity']);
        });
    }
}
