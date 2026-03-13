<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderModulesTable extends Migration
{
    public function up()
    {
        Schema::create('tender_modules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tender_id');
            $table->string('name');
            $table->decimal('cost', 15, 2)->nullable();
            $table->text('summary');
            $table->string('tender_file')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tender_modules');
    }
}
