<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->string('company_name', 200)->nullable()->after('city');
            $table->text('company_address')->nullable()->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'company_address']);
        });
    }
};
