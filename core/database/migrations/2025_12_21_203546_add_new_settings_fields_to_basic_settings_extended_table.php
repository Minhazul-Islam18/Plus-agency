<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewSettingsFieldsToBasicSettingsExtendedTable extends Migration
{
    public function up()
    {
        $columns = [
            'service_section_bg'         => fn(Blueprint $t) => $t->string('service_section_bg', 255)->nullable(),
            'service_overlay_color'       => fn(Blueprint $t) => $t->string('service_overlay_color', 10)->default('000000')->nullable(),
            'service_overlay_opacity'     => fn(Blueprint $t) => $t->decimal('service_overlay_opacity', 3, 2)->default(0.6)->nullable(),
            'approach_section_bg'         => fn(Blueprint $t) => $t->string('approach_section_bg', 255)->nullable(),
            'approach_overlay_color'      => fn(Blueprint $t) => $t->string('approach_overlay_color', 10)->default('000000')->nullable(),
            'approach_overlay_opacity'    => fn(Blueprint $t) => $t->decimal('approach_overlay_opacity', 3, 2)->default(0.6)->nullable(),
            'statistics_overlay_color'    => fn(Blueprint $t) => $t->string('statistics_overlay_color', 10)->default('000000')->nullable(),
            'statistics_overlay_opacity'  => fn(Blueprint $t) => $t->decimal('statistics_overlay_opacity', 3, 2)->default(0.6)->nullable(),
            'portfolio_section_bg'        => fn(Blueprint $t) => $t->string('portfolio_section_bg', 255)->nullable(),
            'portfolio_overlay_color'     => fn(Blueprint $t) => $t->string('portfolio_overlay_color', 10)->default('000000')->nullable(),
            'portfolio_overlay_opacity'   => fn(Blueprint $t) => $t->decimal('portfolio_overlay_opacity', 3, 2)->default(0.6)->nullable(),
            'cta_overlay_color'           => fn(Blueprint $t) => $t->string('cta_overlay_color', 10)->default('000000')->nullable(),
            'cta_overlay_opacity'         => fn(Blueprint $t) => $t->decimal('cta_overlay_opacity', 3, 2)->default(0.6)->nullable(),
            'pricing_bg'                  => fn(Blueprint $t) => $t->string('pricing_bg', 50)->nullable(),
            'blog_overlay_color'          => fn(Blueprint $t) => $t->string('blog_overlay_color', 20)->nullable(),
            'blog_overlay_opacity'        => fn(Blueprint $t) => $t->decimal('blog_overlay_opacity', 3, 2)->nullable(),
            'testimonial_section_bg'      => fn(Blueprint $t) => $t->string('testimonial_section_bg', 255)->nullable(),
            'testimonial_overlay_color'   => fn(Blueprint $t) => $t->string('testimonial_overlay_color', 10)->default('000000')->nullable(),
            'testimonial_overlay_opacity' => fn(Blueprint $t) => $t->decimal('testimonial_overlay_opacity', 3, 2)->default(0.6)->nullable(),
            'blog_bg'                     => fn(Blueprint $t) => $t->string('blog_bg', 50)->nullable(),
            'partner_overlay_color'       => fn(Blueprint $t) => $t->string('partner_overlay_color', 20)->nullable(),
            'partner_overlay_opacity'     => fn(Blueprint $t) => $t->decimal('partner_overlay_opacity', 3, 2)->nullable(),
            'partner_bg'                  => fn(Blueprint $t) => $t->string('partner_bg', 50)->nullable(),
        ];

        foreach ($columns as $col => $addFn) {
            if (!Schema::hasColumn('basic_settings_extended', $col)) {
                Schema::table('basic_settings_extended', $addFn);
            }
        }
    }

    public function down()
    {
        Schema::table('basic_settings_extended', function (Blueprint $table) {
            $table->dropColumn(['blog_bg', 'partner_overlay_color', 'partner_overlay_opacity', 'partner_bg']);
        });
    }
}
