<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderSectionsTable extends Migration
{
    public function up()
    {
        Schema::create('tender_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tender_module_id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tender_sections');
    }
}
