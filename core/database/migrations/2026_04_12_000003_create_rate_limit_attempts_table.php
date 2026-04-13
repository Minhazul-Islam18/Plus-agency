<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRateLimitAttemptsTable extends Migration
{
    public function up()
    {
        Schema::create('rate_limit_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->unique(); // ip:xxx | email:hash | device:hash
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('blocked_until')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->index('key');
            $table->index('blocked_until');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_limit_attempts');
    }
}
