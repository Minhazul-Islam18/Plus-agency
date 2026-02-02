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
            $table->string('portfolio_breadcrumb_bg')->nullable()->after('gallery_breadcrumb_overlay_opacity');
            $table->string('portfolio_breadcrumb_overlay_color', 20)->nullable()->default('000000')->after('portfolio_breadcrumb_bg');
            $table->decimal('portfolio_breadcrumb_overlay_opacity', 3, 2)->nullable()->default(0.5)->after('portfolio_breadcrumb_overlay_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['portfolio_breadcrumb_bg', 'portfolio_breadcrumb_overlay_color', 'portfolio_breadcrumb_overlay_opacity']);
        });
    }
};
