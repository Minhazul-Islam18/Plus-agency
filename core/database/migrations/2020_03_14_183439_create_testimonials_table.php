<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestimonialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('testimonials')) {
            Schema::create('testimonials', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id')->default(0);
                $table->string('image', 255)->nullable();
                $table->text('comment')->nullable();
                $table->string('name', 50)->nullable();
                $table->string('rank', 50)->nullable();
                $table->integer('serial_number')->default(0);
            });
        }

        // Company logo (optional) + company website URL (optional) shown
        // next to a testimonial on the front end. Logo only ever renders if
        // uploaded; it's only ever clickable if a URL is also set.
        if (Schema::hasTable('testimonials') && !Schema::hasColumn('testimonials', 'company_logo')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->string('company_logo', 255)->nullable()->after('image');
            });
        }
        if (Schema::hasTable('testimonials') && !Schema::hasColumn('testimonials', 'company_url')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->string('company_url', 255)->nullable()->after('company_logo');
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
        Schema::dropIfExists('testimonials');
    }
}
