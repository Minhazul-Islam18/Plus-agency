<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('access_logs')) {

            return;

        }

        Schema::create('access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type'); // LINK_REQUESTED, LINK_SENT, LINK_CLICKED, DOWNLOAD_SUCCESS, DOWNLOAD_FAILED, RATE_LIMIT_TRIGGERED
            $table->string('order_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('device_hash')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('email_hash')->nullable();
            $table->float('risk_score')->default(0);
            $table->string('result')->nullable(); // ORDER_NOT_FOUND, EMAIL_MISMATCH, INVALID_STATUS, OK, etc.
            $table->timestamp('created_at'); // immutable — no updated_at

            $table->index('event_type');
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_logs');
    }
}
