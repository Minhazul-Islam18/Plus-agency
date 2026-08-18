<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('partners')) {
            Schema::create('partners', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id')->default(0);
                $table->string('name', 255)->nullable();
                $table->string('image', 255)->nullable();
                $table->string('url', 255)->nullable();
                $table->integer('serial_number')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('partners') && !Schema::hasColumn('partners', 'name')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->string('name', 255)->nullable()->after('language_id');
            });
        }

        if (Schema::hasTable('partners') && !Schema::hasColumn('partners', 'status')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('serial_number');
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
        Schema::dropIfExists('partners');
    }
}
