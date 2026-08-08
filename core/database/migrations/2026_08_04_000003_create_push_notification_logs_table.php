<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePushNotificationLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('push_notification_logs')) {
            return;
        }

        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            // 'general' - broadcast to every active subscriber, no login needed.
            // 'personal' - targeted at a hand-picked subset of subscribers.
            $table->string('notification_type', 20)->default('general');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('sent_by')->nullable();
            $table->string('sent_by_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_notification_logs');
    }
}
