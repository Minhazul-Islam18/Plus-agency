<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropStatusFromContactMessagesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * Pending/Replied is derived from replied_at, not a separately tracked
   * status — a contact inbox has no approve/reject workflow to track.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('contact_messages', function (Blueprint $table) {
      if (Schema::hasColumn('contact_messages', 'status')) {
        $table->dropColumn('status');
      }
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('contact_messages', function (Blueprint $table) {
      $table->string('status')->default('pending')->after('message');
    });
  }
}
