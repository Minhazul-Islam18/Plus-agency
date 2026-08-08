<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('basic_settings', 'intro_section_video_text')) {
                $table->text('intro_section_video_text')->nullable()->after('intro_section_video_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (Schema::hasColumn('basic_settings', 'intro_section_video_text')) {
                $table->dropColumn('intro_section_video_text');
            }
        });
    }
};
