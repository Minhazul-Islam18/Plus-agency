<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id')->default(0);
                $table->string('name', 255)->nullable();
                $table->string('title', 255)->nullable();
                $table->string('subtitle', 255)->nullable();
                $table->string('slug', 255)->nullable();
                $table->binary('body')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->integer('serial_number')->default(0);
                $table->text('meta_keywords')->nullable();
                $table->text('meta_description')->nullable();
                $table->timestamps();
            });
        }

        // Last-modified-by admin, for the "Last Modified By" column on the
        // Custom Pages list — set on every genuine content change, both the
        // metadata form (PageController::store/update) and the pagebuilder's
        // own autosave (PageBuilderController::save). updated_at (already a
        // standard Eloquent timestamp, bumped by every ->save() call above)
        // covers "Last Modified" with no schema change needed.
        if (Schema::hasTable('pages') && !Schema::hasColumn('pages', 'updated_by_admin_id')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by_admin_id')->nullable()->after('serial_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
