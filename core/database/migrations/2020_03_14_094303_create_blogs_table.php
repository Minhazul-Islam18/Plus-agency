<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('language_id')->default(0);
            $table->integer('bcategory_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->string('main_image', 255)->nullable();
            $table->binary('content')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('serial_number')->default(0);
            // Admin who published the post (front-end "By ..." byline) — set
            // once at creation (BlogController@store) and never reassigned
            // on edit, same as pages.updated_by_admin_id's belongsTo pattern.
            $table->unsignedBigInteger('admin_id')->nullable();
            // Activate/deactivate switch on the admin list (replaces the old
            // per-row Sidebar select there — Sidebar moved into the
            // create/edit forms as a radio group, same as services.sidebar).
            $table->tinyInteger('status')->default(1)->comment('1 - active, 0 - inactive');
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
        Schema::dropIfExists('blogs');
    }
}
