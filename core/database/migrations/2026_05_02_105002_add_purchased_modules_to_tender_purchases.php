<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->text('purchased_modules')->nullable()->after('tender_notice_publication_fee');
        });
    }

    public function down(): void
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->dropColumn('purchased_modules');
        });
    }
};
