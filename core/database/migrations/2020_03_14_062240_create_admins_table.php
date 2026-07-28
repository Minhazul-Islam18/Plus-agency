<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('role_id')->nullable();
            $table->string('username', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('activation_token_hash', 64)->nullable()->unique();
            $table->timestamp('activation_expires_at')->nullable();
            $table->string('temp_password', 255)->nullable();
            $table->timestamp('temp_password_expires_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_at')->nullable();
            $table->rememberToken();
            $table->tinyInteger('status')->default(1)->comment('0 - deactive, 1 - active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
