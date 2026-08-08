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
        Schema::table('languages', function (Blueprint $table) {
            if (!Schema::hasColumn('languages', 'status')) {
                $table->tinyInteger('status')->default(1)->after('rtl');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            if (Schema::hasColumn('languages', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
