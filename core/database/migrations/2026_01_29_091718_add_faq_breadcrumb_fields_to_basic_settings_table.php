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
        Schema::table('basic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings', 'faq_breadcrumb_bg')) {
                $table->string('faq_breadcrumb_bg')->nullable()->after('contact_breadcrumb_overlay_opacity');
            }
            if (!Schema::hasColumn('basic_settings', 'faq_breadcrumb_overlay_color')) {
                $table->string('faq_breadcrumb_overlay_color', 20)->nullable()->default('000000')->after('faq_breadcrumb_bg');
            }
            if (!Schema::hasColumn('basic_settings', 'faq_breadcrumb_overlay_opacity')) {
                $table->decimal('faq_breadcrumb_overlay_opacity', 3, 2)->nullable()->default(0.5)->after('faq_breadcrumb_overlay_color');
            }
            // FAQ page hero/banner content (admin FAQ Settings). TEXT, not
            // VARCHAR: basic_settings is already at MySQL's 65535-byte row limit
            // (a new varchar(255) fails with "Row size too large"); TEXT is stored
            // off-row so it doesn't count against it.
            if (!Schema::hasColumn('basic_settings', 'faq_hero_image')) {
                $table->text('faq_hero_image')->nullable();
            }
            if (!Schema::hasColumn('basic_settings', 'faq_intro_text')) {
                $table->text('faq_intro_text')->nullable();
            }
            if (!Schema::hasColumn('basic_settings', 'faq_contact_email')) {
                $table->text('faq_contact_email')->nullable();
            }
            if (!Schema::hasColumn('basic_settings', 'faq_whatsapp')) {
                $table->text('faq_whatsapp')->nullable();
            }
            if (!Schema::hasColumn('basic_settings', 'faq_frequent_max')) {
                $table->unsignedTinyInteger('faq_frequent_max')->default(5);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['faq_breadcrumb_bg', 'faq_breadcrumb_overlay_color', 'faq_breadcrumb_overlay_opacity', 'faq_hero_image', 'faq_intro_text', 'faq_contact_email', 'faq_whatsapp', 'faq_frequent_max']);
        });
    }
};
