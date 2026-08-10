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
        Schema::table('basic_settings_extended', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings_extended', 'is_resend')) {
                $table->tinyInteger('is_resend')->default(0);
            }
            if (!Schema::hasColumn('basic_settings_extended', 'resend_api_key')) {
                $table->string('resend_api_key', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_settings_extended', function (Blueprint $table) {
            if (Schema::hasColumn('basic_settings_extended', 'is_resend')) {
                $table->dropColumn('is_resend');
            }
            if (Schema::hasColumn('basic_settings_extended', 'resend_api_key')) {
                $table->dropColumn('resend_api_key');
            }
        });
    }
};
