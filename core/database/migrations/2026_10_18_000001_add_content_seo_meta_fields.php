<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContentSeoMetaFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // meta_title: an explicit SEO <title> override, separate from the
        // content's own public title (Yoast/RankMath-style "SEO Title").
        // og_image: a per-item social-share image override — stores a bare
        // filename under the same subdir convention as each model's own
        // main/featured image (main_image / featured_image), not a full path.
        Schema::table('blogs', function (Blueprint $table) {
            $table->text('meta_title')->nullable();
            $table->text('og_image')->nullable();
        });

        Schema::table('portfolios', function (Blueprint $table) {
            $table->text('meta_title')->nullable();
            $table->text('og_image')->nullable();
        });

        Schema::table('services', function (Blueprint $table) {
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
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'og_image']);
        });

        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'og_image']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'og_image']);
        });
    }
}
