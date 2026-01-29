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
            $table->string('blog_breadcrumb_bg')->nullable()->after('gallery_breadcrumb_overlay_opacity');
            $table->string('blog_breadcrumb_overlay_color', 20)->nullable()->default('000000')->after('blog_breadcrumb_bg');
            $table->decimal('blog_breadcrumb_overlay_opacity', 3, 2)->nullable()->default(0.5)->after('blog_breadcrumb_overlay_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['blog_breadcrumb_bg', 'blog_breadcrumb_overlay_color', 'blog_breadcrumb_overlay_opacity']);
        });
    }
};
