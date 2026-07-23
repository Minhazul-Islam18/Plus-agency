<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusAndReplyToContactMessagesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * Guarded per-column so this is safe to run regardless of partial prior
   * state (e.g. an earlier interrupted attempt already added reply_message).
   *
   * @return void
   */
  public function up()
  {
    Schema::table('contact_messages', function (Blueprint $table) {
      if (!Schema::hasColumn('contact_messages', 'status')) {
        $table->string('status')->default('pending')->after('message');
      }
      if (!Schema::hasColumn('contact_messages', 'reply_message')) {
        $table->text('reply_message')->nullable()->after('mail_sent');
      }
      if (!Schema::hasColumn('contact_messages', 'replied_at')) {
        $table->timestamp('replied_at')->nullable()->after('reply_message');
      }
      if (!Schema::hasColumn('contact_messages', 'replied_by')) {
        $table->string('replied_by')->nullable()->after('replied_at');
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
      $table->dropColumn(['status', 'reply_message', 'replied_at', 'replied_by']);
    });
  }
}
