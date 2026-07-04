<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable audit trail of admin actions on tender purchases (money-affecting
 * operations: payment status, reference, suspend/reactivate, delete).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tender_audit_logs')) {
            return;
        }

        Schema::create('tender_audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('admin_name')->nullable();
            $table->string('action', 50)->index();
            $table->unsignedBigInteger('tender_purchase_id')->nullable()->index();
            $table->string('order_number')->nullable()->index();
            $table->string('description', 500)->nullable();
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_audit_logs');
    }
};
