<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBasicSettingsExtra extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (!Schema::hasTable('basic_settings_extra')) {
      Schema::create('basic_settings_extra', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->integer('language_id')->default(1);
        $table->tinyInteger('is_shop')->default(1);
        $table->tinyInteger('is_ticket')->default(1);

        // Auto compress+convert raster LFM uploads to WebP/AVIF. Editable at
        // admin/basicinfo. SVG and GIF are always skipped regardless of this
        // setting (vector needs no conversion, animated GIF would lose its
        // animation).
        $table->tinyInteger('image_convert_enabled')->default(0);
        $table->string('image_convert_format', 10)->default('webp');
        $table->unsignedTinyInteger('image_convert_quality')->default(80);

        // Auto-resize oversized LFM uploads (the generic file-manager path —
        // admin forms with their own display-size bound, like Logo/Partner/
        // Tender, call copy_resized_image() directly and aren't governed by
        // this). Editable at admin/basicinfo; read by
        // ResizeUploadedLfmFile as the default when a given upload's own
        // per-request toggle is absent.
        $table->tinyInteger('image_resize_enabled')->default(1);
        $table->unsignedInteger('image_resize_max_width')->default(1920);
        $table->unsignedInteger('image_resize_max_height')->default(1920);

        // LFM upload size caps (MB), read live by AppServiceProvider::boot()
        // to override config/lfm.php's static defaults. Editable at
        // admin/basicinfo instead of requiring a code deploy.
        $table->unsignedInteger('lfm_max_image_size_mb')->default(20);
        $table->unsignedInteger('lfm_max_file_size_mb')->default(50);

        // Cloudflare "Purge Cache" button (admin header). Editable at
        // admin/basicinfo instead of requiring a .env edit + deploy.
        $table->string('cloudflare_zone_id')->nullable();
        $table->string('cloudflare_api_token')->nullable();
      });
    }

    if (Schema::hasTable('basic_settings_extra') && !Schema::hasColumn('basic_settings_extra', 'lfm_max_image_size_mb')) {
      Schema::table('basic_settings_extra', function (Blueprint $table) {
        $table->unsignedInteger('lfm_max_image_size_mb')->default(20);
        $table->unsignedInteger('lfm_max_file_size_mb')->default(50);
      });
    }

    if (Schema::hasTable('basic_settings_extra') && !Schema::hasColumn('basic_settings_extra', 'cloudflare_zone_id')) {
      Schema::table('basic_settings_extra', function (Blueprint $table) {
        $table->string('cloudflare_zone_id')->nullable();
        $table->string('cloudflare_api_token')->nullable();
      });
    }

    if (Schema::hasTable('basic_settings_extra') && !Schema::hasColumn('basic_settings_extra', 'image_resize_enabled')) {
      Schema::table('basic_settings_extra', function (Blueprint $table) {
        $table->tinyInteger('image_resize_enabled')->default(1);
        $table->unsignedInteger('image_resize_max_width')->default(1920);
        $table->unsignedInteger('image_resize_max_height')->default(1920);
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
    Schema::dropIfExists('basic_settings_extra');
  }
}
