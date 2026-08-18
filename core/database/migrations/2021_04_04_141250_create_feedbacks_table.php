<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbacksTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('feedbacks', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('email');
      $table->string('subject');
      $table->unsignedMediumInteger('rating');
      $table->text('feedback');
      // Read/unread state for the admin inbox: unread rows highlight orange,
      // read rows green; sidebar badge counts unread. Set true when an admin
      // opens the Show modal (Admin\FeedbackController@markRead).
      $table->boolean('is_read')->default(false);
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
    Schema::dropIfExists('feedbacks');
  }
}
