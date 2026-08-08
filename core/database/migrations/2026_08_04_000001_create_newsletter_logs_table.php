<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsletterLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('newsletter_logs')) {
            return;
        }

        Schema::create('newsletter_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject');
            $table->longText('message');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            // Nullable: admin account could be deleted later, the log entry
            // (what was sent, to how many people) should still stand on its own.
            $table->unsignedInteger('sent_by')->nullable();
            $table->string('sent_by_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('newsletter_logs');
    }
}
