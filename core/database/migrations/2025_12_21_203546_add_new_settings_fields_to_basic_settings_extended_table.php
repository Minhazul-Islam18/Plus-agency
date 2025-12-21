<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewSettingsFieldsToBasicSettingsExtendedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basic_settings_extended', function (Blueprint $table) {
            $table->string('service_section_bg', 255)->nullable();
            $table->string('service_overlay_color', 10)->default('000000')->nullable();
            $table->decimal('service_overlay_opacity', 3, 2)->default(0.6)->nullable();
            $table->string('approach_section_bg', 255)->nullable();
            $table->string('approach_overlay_color', 10)->default('000000')->nullable();
            $table->decimal('approach_overlay_opacity', 3, 2)->default(0.6)->nullable();
            $table->string('statistics_overlay_color', 10)->default('000000')->nullable();
            $table->decimal('statistics_overlay_opacity', 3, 2)->default(0.6)->nullable();
            $table->string('portfolio_section_bg', 255)->nullable();
            $table->string('portfolio_overlay_color', 10)->default('000000')->nullable();
            $table->decimal('portfolio_overlay_opacity', 3, 2)->default(0.6)->nullable();
            $table->string('cta_overlay_color', 10)->default('000000')->nullable();
            $table->decimal('cta_overlay_opacity', 3, 2)->default(0.6)->nullable();
            $table->string('pricing_bg', 50)->nullable();
            $table->string('blog_overlay_color', 20)->nullable();
            $table->decimal('blog_overlay_opacity', 3, 2)->nullable();
            $table->string('testimonial_section_bg', 255)->nullable();
            $table->string('testimonial_overlay_color', 10)->default('000000')->nullable();
            $table->decimal('testimonial_overlay_opacity', 3, 2)->default(0.6)->nullable();
            $table->string('blog_bg', 50)->nullable();
            $table->string('partner_overlay_color', 20)->nullable();
            $table->decimal('partner_overlay_opacity', 3, 2)->nullable();
            $table->string('partner_bg', 50)->nullable();
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
            $table->dropColumn([
                // 'blog_bg','service_section_bg', 'service_overlay_color', 'service_overlay_opacity', 'statistics_overlay_color', 'statistics_overlay_opacity', 'approach_section_bg', 'approach_overlay_color', 'approach_overlay_opacity', 'portfolio_section_bg', 'portfolio_overlay_color', 'portfolio_overlay_opacity', 'testimonial_section_bg', 'testimonial_overlay_color', 'testimonial_overlay_opacity', 'blog_overlay_color', 'blog_overlay_opacity', 'pricing_bg', 'cta_overlay_color', 'cta_overlay_opacity',
                'blog_bg',
                'partner_overlay_color',
                'partner_overlay_opacity',
                'partner_bg'
            ]);
        });
    }
}
