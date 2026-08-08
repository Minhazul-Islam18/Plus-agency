<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHeroRotatingTitlesToBasicSettings extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('basic_settings', 'hero_rotating_titles')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->text('hero_rotating_titles')->nullable()->after('hero_section_title')
                    ->comment('Dark theme only: extra headlines that flip-cycle after hero_section_title, one per line');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('basic_settings', 'hero_rotating_titles')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->dropColumn(['hero_rotating_titles']);
            });
        }
    }
}
