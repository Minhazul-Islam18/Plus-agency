<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per company identity (keyed by normalized registration number),
 * derived from tender_purchases. Backs the admin "Users Management" list.
 * Soft-deleting a company never touches tender_purchases — each purchase
 * already carries its own full snapshot of the buyer's details.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tender_companies')) {
            return;
        }

        Schema::create('tender_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_name', 200)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('company_registration_no', 100)->unique();
            $table->string('email')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->timestamp('first_purchase_at')->nullable();
            $table->timestamp('last_purchase_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_companies');
    }
};
