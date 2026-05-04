<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->string('invoice_sign', 100)->nullable()->after('is_tender');
            $table->string('invoice_watermark', 100)->nullable()->after('invoice_sign');
            $table->string('invoice_footer_wavy', 100)->nullable()->after('invoice_watermark');
            $table->text('invoice_footer_address')->nullable()->after('invoice_footer_wavy');
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->dropColumn(['invoice_sign', 'invoice_watermark', 'invoice_footer_wavy', 'invoice_footer_address']);
        });
    }
};
