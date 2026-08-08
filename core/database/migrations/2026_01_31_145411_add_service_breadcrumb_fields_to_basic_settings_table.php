<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings', 'service_breadcrumb_bg')) {
                $table->string('service_breadcrumb_bg')->nullable()->after('gallery_breadcrumb_overlay_opacity');
            }
            if (!Schema::hasColumn('basic_settings', 'service_breadcrumb_overlay_color')) {
                $table->string('service_breadcrumb_overlay_color', 20)->nullable()->default('000000')->after('service_breadcrumb_bg');
            }
            if (!Schema::hasColumn('basic_settings', 'service_breadcrumb_overlay_opacity')) {
                $table->decimal('service_breadcrumb_overlay_opacity', 3, 2)->nullable()->default(0.5)->after('service_breadcrumb_overlay_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['service_breadcrumb_bg', 'service_breadcrumb_overlay_color', 'service_breadcrumb_overlay_opacity']);
        });
    }
};
