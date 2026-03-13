<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsTenderToBasicSettingsExtra extends Migration
{
    public function up()
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->tinyInteger('is_tender')->default(1)->after('is_course_rating')
                ->comment('1 - activate all pages related to tenders, 0 - deactivate');
        });
    }

    public function down()
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->dropColumn('is_tender');
        });
    }
}
