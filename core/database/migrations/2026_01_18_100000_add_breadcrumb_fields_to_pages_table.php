<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBreadcrumbFieldsToPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'breadcrumb_image')) {
                $table->string('breadcrumb_image', 100)->nullable();
            }
            if (!Schema::hasColumn('pages', 'breadcrumb_overlay_color')) {
                $table->string('breadcrumb_overlay_color', 20)->nullable();
            }
            if (!Schema::hasColumn('pages', 'breadcrumb_overlay_opacity')) {
                $table->decimal('breadcrumb_overlay_opacity', 3, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['breadcrumb_image', 'breadcrumb_overlay_color', 'breadcrumb_overlay_opacity']);
        });
    }
}
