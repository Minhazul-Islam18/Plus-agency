<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings', 'cta_section_badge')) {
                $table->string('cta_section_badge')->nullable()->after('cta_section_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (Schema::hasColumn('basic_settings', 'cta_section_badge')) {
                $table->dropColumn('cta_section_badge');
            }
        });
    }
};
