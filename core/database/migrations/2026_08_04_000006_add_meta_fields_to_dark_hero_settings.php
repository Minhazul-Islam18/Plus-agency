<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaFieldsToDarkHeroSettings extends Migration
{
    public function up()
    {
        $columns = [
            'meta_left'  => fn(Blueprint $t) => $t->string('meta_left', 120)->nullable()
                ->comment('Small meta line under the CTA, e.g. a location'),
            'meta_right' => fn(Blueprint $t) => $t->string('meta_right', 120)->nullable()
                ->comment('Small meta line under the CTA, e.g. an established year'),
        ];
        foreach ($columns as $col => $addFn) {
            if (!Schema::hasColumn('dark_hero_settings', $col)) {
                Schema::table('dark_hero_settings', $addFn);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('dark_hero_settings', 'meta_left')) {
            Schema::table('dark_hero_settings', function (Blueprint $table) {
                $table->dropColumn(['meta_left', 'meta_right']);
            });
        }
    }
}
