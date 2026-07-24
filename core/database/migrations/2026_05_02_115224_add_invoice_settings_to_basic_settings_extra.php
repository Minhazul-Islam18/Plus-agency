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
            // How many times each secure tender download link may be opened.
            // Editable at admin/tender/settings; falls back to 3 when unset.
            $table->unsignedInteger('tender_max_downloads')->default(3)->after('invoice_footer_address');

            // Configurable, uniform per-order recovery cap shared across all 4
            // Find-My-Files methods (Order Number, OTP, Payment Reference,
            // Regenerate) — the limit, a master on/off switch, and one on/off
            // switch per method so admin can selectively exempt a method while
            // leaving the others capped. Editable at admin/tender/settings.
            $table->unsignedInteger('tender_max_regen_per_day')->default(3)->after('tender_max_downloads');
            $table->tinyInteger('tender_regen_cap_enabled')->default(1)->after('tender_max_regen_per_day');
            $table->tinyInteger('tender_regen_cap_order_number')->default(1)->after('tender_regen_cap_enabled');
            $table->tinyInteger('tender_regen_cap_otp')->default(1)->after('tender_regen_cap_order_number');
            $table->tinyInteger('tender_regen_cap_payref')->default(1)->after('tender_regen_cap_otp');
            $table->tinyInteger('tender_regen_cap_regenerate')->default(1)->after('tender_regen_cap_payref');
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_sign', 'invoice_watermark', 'invoice_footer_wavy', 'invoice_footer_address',
                'tender_max_downloads', 'tender_max_regen_per_day', 'tender_regen_cap_enabled',
                'tender_regen_cap_order_number', 'tender_regen_cap_otp', 'tender_regen_cap_payref',
                'tender_regen_cap_regenerate',
            ]);
        });
    }
};
