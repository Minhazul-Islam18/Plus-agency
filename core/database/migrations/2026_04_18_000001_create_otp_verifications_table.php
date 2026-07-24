<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpVerificationsTable extends Migration
{
    public function up()
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('session_token')->unique();
            $table->string('email_hash', 64)->index();
            // Nullable: email-channel verifications (Find My Files Method 4) have
            // no phone at all. channel tells verify/resend which delivery path a
            // given record used.
            $table->string('phone_hash', 64)->nullable()->index();
            $table->string('channel', 10)->default('sms');
            $table->string('otp_hash', 64);
            $table->string('order_id', 50)->nullable()->index();
            // All order numbers a single OTP verification covers (multi-tender).
            // order_id stays = the first, for logging / back-compat.
            $table->json('order_ids')->nullable();
            $table->timestamp('expires_at');
            $table->tinyInteger('attempts')->default(0);
            $table->timestamp('last_resend_at')->nullable();
            $table->enum('status', ['pending', 'verified', 'expired', 'exhausted'])->default('pending')->index();
            $table->string('ip', 45);
            $table->string('device_hash', 64);
            $table->string('masked_phone', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_verifications');
    }
}
