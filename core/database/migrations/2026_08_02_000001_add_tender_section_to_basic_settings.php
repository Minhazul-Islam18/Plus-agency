<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTenderSectionToBasicSettings extends Migration
{
    public function up()
    {
        $settingsColumns = [
            'tender_section'       => fn(Blueprint $t) => $t->tinyInteger('tender_section')->default(1)->comment('1 - active, 0 - deactive'),
            'tender_section_title' => fn(Blueprint $t) => $t->string('tender_section_title', 255)->nullable(),
            'tender_section_text'  => fn(Blueprint $t) => $t->string('tender_section_text', 255)->nullable(),
        ];
        foreach ($settingsColumns as $col => $addFn) {
            if (!Schema::hasColumn('basic_settings', $col)) {
                Schema::table('basic_settings', $addFn);
            }
        }

        $extendedColumns = [
            'tender_section_bg'    => fn(Blueprint $t) => $t->string('tender_section_bg', 255)->nullable(),
            'tender_overlay_color' => fn(Blueprint $t) => $t->string('tender_overlay_color', 10)->default('000000')->nullable(),
            'tender_overlay_opacity' => fn(Blueprint $t) => $t->decimal('tender_overlay_opacity', 3, 2)->default(0.6)->nullable(),
        ];
        foreach ($extendedColumns as $col => $addFn) {
            if (!Schema::hasColumn('basic_settings_extended', $col)) {
                Schema::table('basic_settings_extended', $addFn);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('basic_settings', 'tender_section')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->dropColumn(['tender_section', 'tender_section_title', 'tender_section_text']);
            });
        }

        if (Schema::hasColumn('basic_settings_extended', 'tender_section_bg')) {
            Schema::table('basic_settings_extended', function (Blueprint $table) {
                $table->dropColumn(['tender_section_bg', 'tender_overlay_color', 'tender_overlay_opacity']);
            });
        }
    }
}
