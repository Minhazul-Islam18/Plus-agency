<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwilioFieldsToBasicExtrasTable extends Migration
{
    public function up()
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings_extra', 'twilio_status')) {
                $table->tinyInteger('twilio_status')->default(0);
            }
            if (!Schema::hasColumn('basic_settings_extra', 'twilio_account_sid')) {
                $table->string('twilio_account_sid', 100)->nullable();
            }
            if (!Schema::hasColumn('basic_settings_extra', 'twilio_auth_token')) {
                $table->string('twilio_auth_token', 100)->nullable();
            }
            if (!Schema::hasColumn('basic_settings_extra', 'twilio_from_number')) {
                $table->string('twilio_from_number', 30)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->dropColumn(['twilio_status', 'twilio_account_sid', 'twilio_auth_token', 'twilio_from_number']);
        });
    }
}
