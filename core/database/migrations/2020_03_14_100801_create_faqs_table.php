<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('language_id')->default(0);
            $table->string('question', 255)->nullable();
            $table->text('answer')->nullable();
            $table->integer('serial_number')->default(0);
            // Most-viewed tracking (FAQ page's "Questions les plus consultées" panel):
            // views_count = first-open clicks, is_frequent = currently on the panel,
            // promoted_at = when it joined it (oldest is evicted first when the panel is full).
            $table->unsignedInteger('views_count')->default(0);
            $table->boolean('is_frequent')->default(0);
            $table->timestamp('promoted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faqs');
    }
}
