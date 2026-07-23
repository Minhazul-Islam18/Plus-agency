<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactMessagesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('contact_messages', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('email');
      $table->string('subject');
      $table->text('message');
      $table->tinyInteger('mail_sent')->default(0);
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
    Schema::dropIfExists('contact_messages');
  }
}
