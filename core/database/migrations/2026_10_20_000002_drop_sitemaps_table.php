<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropSitemapsTable extends Migration
{
    /**
     * The old admin-only "crawl a URL, write a static sitemap file" tool
     * (Admin\SitemapController) is fully removed — superseded by the
     * model-driven /sitemap.xml (Front\SitemapController). This table only
     * ever backed that old tool's saved-crawl list, 0 rows on this install.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('sitemaps');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('sitemaps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sitemap_url', 255)->nullable();
            $table->string('filename', 255)->nullable();
            $table->timestamps();
        });
    }
}
