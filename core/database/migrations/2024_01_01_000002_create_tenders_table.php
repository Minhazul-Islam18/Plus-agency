<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTendersTable extends Migration
{
    public function up()
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('language_id');
            $table->integer('tender_category_id');
            $table->string('country');
            $table->string('tender_code')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->dateTime('submission_deadline')->nullable();
            $table->decimal('current_price', 15, 2)->nullable();
            $table->decimal('previous_price', 15, 2)->nullable();
            $table->text('summary')->nullable();
            $table->string('tender_image')->nullable();
            $table->string('video_link')->nullable();
            $table->longText('overview');
            $table->string('expert_name');
            $table->string('expert_position');
            $table->text('expert_details');
            $table->string('expert_whatsapp');
            $table->string('expert_email');
            $table->string('expert_image')->nullable();
            $table->tinyInteger('is_featured')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenders');
    }
}
