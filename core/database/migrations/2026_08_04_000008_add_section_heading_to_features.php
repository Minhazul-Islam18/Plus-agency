<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSectionHeadingToFeatures extends Migration
{
    public function up()
    {
        $columns = [
            'feature_section_title'    => fn(Blueprint $t) => $t->string('feature_section_title', 255)->nullable(),
            'feature_section_subtitle' => fn(Blueprint $t) => $t->string('feature_section_subtitle', 255)->nullable(),
        ];
        foreach ($columns as $col => $addFn) {
            if (!Schema::hasColumn('basic_settings', $col)) {
                Schema::table('basic_settings', $addFn);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('basic_settings', 'feature_section_title')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->dropColumn(['feature_section_title', 'feature_section_subtitle']);
            });
        }
    }
}
