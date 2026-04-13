<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecureTokensTable extends Migration
{
    public function up()
    {
        Schema::create('secure_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id');
            $table->string('email_hash');
            $table->string('token_hash')->unique();
            $table->timestamp('issued_at');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('max_downloads')->default(3);
            $table->unsignedTinyInteger('download_count')->default(0);
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->string('device_hash')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();

            $table->index('token_hash');
            $table->index(['order_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('secure_tokens');
    }
}
