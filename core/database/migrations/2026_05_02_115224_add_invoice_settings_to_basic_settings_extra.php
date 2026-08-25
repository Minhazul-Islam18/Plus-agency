<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings_extra', 'invoice_sign')) {
                $table->string('invoice_sign', 100)->nullable()->after('is_tender');
            }
            if (!Schema::hasColumn('basic_settings_extra', 'invoice_watermark')) {
                $table->string('invoice_watermark', 100)->nullable()->after('invoice_sign');
            }
            if (!Schema::hasColumn('basic_settings_extra', 'invoice_footer_wavy')) {
                $table->string('invoice_footer_wavy', 100)->nullable()->after('invoice_watermark');
            }
            if (!Schema::hasColumn('basic_settings_extra', 'invoice_footer_address')) {
                $table->text('invoice_footer_address')->nullable()->after('invoice_footer_wavy');
            }
            // How many times each secure tender download link may be opened.
            // Editable at admin/tender/settings; falls back to 3 when unset.
            if (!Schema::hasColumn('basic_settings_extra', 'tender_max_downloads')) {
                $table->unsignedInteger('tender_max_downloads')->default(3)->after('invoice_footer_address');
            }

            // Configurable, uniform per-order recovery cap shared across all 4
            // Find-My-Files methods (Order Number, OTP, Payment Reference,
            // Regenerate) — the limit, a master on/off switch, and one on/off
            // switch per method so admin can selectively exempt a method while
            // leaving the others capped. Editable at admin/tender/settings.
            if (!Schema::hasColumn('basic_settings_extra', 'tender_max_regen_per_day')) {
                $table->unsignedInteger('tender_max_regen_per_day')->default(3)->after('tender_max_downloads');
            }
            if (!Schema::hasColumn('basic_settings_extra', 'tender_regen_cap_enabled')) {
                $table->tinyInteger('tender_regen_cap_enabled')->default(1)->after('tender_max_regen_per_day');
            }
            if (!Schema::hasColumn('basic_settings_extra', 'tender_regen_cap_order_number')) {
                $table->tinyInteger('tender_regen_cap_order_number')->default(1)->after('tender_regen_cap_enabled');
            }
            if (!Schema::hasColumn('basic_settings_extra', 'tender_regen_cap_otp')) {
                $table->tinyInteger('tender_regen_cap_otp')->default(1)->after('tender_regen_cap_order_number');
            }
            if (!Schema::hasColumn('basic_settings_extra', 'tender_regen_cap_payref')) {
                $table->tinyInteger('tender_regen_cap_payref')->default(1)->after('tender_regen_cap_otp');
            }
            if (!Schema::hasColumn('basic_settings_extra', 'tender_regen_cap_regenerate')) {
                $table->tinyInteger('tender_regen_cap_regenerate')->default(1)->after('tender_regen_cap_payref');
            }

            // How long a Pending online order sits untouched before
            // NotifyIncompleteTenderPayments treats it as abandoned and
            // sends the resume-payment email. Editable at admin/tender/settings.
            if (!Schema::hasColumn('basic_settings_extra', 'tender_payment_session_timeout_minutes')) {
                $table->unsignedInteger('tender_payment_session_timeout_minutes')->default(5)->after('tender_regen_cap_regenerate');
            }

            // How long a resume-payment link (the "Complete Your Payment" /
            // "Resume Payment" email button) stays valid after being issued,
            // checked in TenderController::resumePurchase() against
            // tender_purchases.resume_token_issued_at. Editable at
            // admin/tender/settings.
            if (!Schema::hasColumn('basic_settings_extra', 'tender_payment_link_expiry_hours')) {
                $table->unsignedInteger('tender_payment_link_expiry_hours')->default(24)->after('tender_payment_session_timeout_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings_extra', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_sign', 'invoice_watermark', 'invoice_footer_wavy', 'invoice_footer_address',
                'tender_max_downloads', 'tender_max_regen_per_day', 'tender_regen_cap_enabled',
                'tender_regen_cap_order_number', 'tender_regen_cap_otp', 'tender_regen_cap_payref',
                'tender_regen_cap_regenerate', 'tender_payment_session_timeout_minutes',
                'tender_payment_link_expiry_hours',
            ]);
        });
    }
};
