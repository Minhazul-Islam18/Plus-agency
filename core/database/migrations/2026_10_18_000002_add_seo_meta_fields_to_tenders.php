<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeoMetaFieldsToTenders extends Migration
{
    /**
     * Tender never had the base meta_keywords/meta_description fields
     * Blog/Portfolio/Service already had — adding those here too, alongside
     * the same meta_title/og_image pair the other three content types got.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('og_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->dropColumn(['meta_keywords', 'meta_description', 'meta_title', 'og_image']);
        });
    }
}
