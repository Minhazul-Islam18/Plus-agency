<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('members')) {
            Schema::create('members', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id')->default(0);
                $table->string('name', 50)->nullable();
                $table->string('rank', 50)->nullable();
                $table->longText('details')->nullable();
                $table->string('image', 255)->nullable();
                $table->string('facebook', 255)->nullable();
                $table->string('twitter', 255)->nullable();
                $table->string('whatsapp', 255)->nullable();
                $table->string('linkedin', 255)->nullable();
                $table->string('email', 255)->nullable();
            });
        }

        if (Schema::hasTable('members') && !Schema::hasColumn('members', 'email')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('email', 255)->nullable()->after('whatsapp');
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
        Schema::dropIfExists('members');
    }
}
