<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEyebrowToDarkHeroSettings extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('dark_hero_settings', 'eyebrow')) {
            Schema::table('dark_hero_settings', function (Blueprint $table) {
                $table->string('eyebrow', 120)->nullable()->after('title');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('dark_hero_settings', 'eyebrow')) {
            Schema::table('dark_hero_settings', function (Blueprint $table) {
                $table->dropColumn(['eyebrow']);
            });
        }
    }
}
