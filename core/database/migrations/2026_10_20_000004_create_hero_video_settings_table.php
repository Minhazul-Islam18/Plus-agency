<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeroVideoSettingsTable extends Migration
{
    /**
     * Dedicated table, same reasoning as dark_hero_settings (basic_settings
     * is already near MySQL's 65535-byte row-size ceiling) and the same
     * "genuinely separate admin page/feature" shape — a new, dark-theme-only
     * background-video hero option, alongside the existing Static/Slider.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hero_video_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->string('eyebrow', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('rotating_titles')->nullable()
                ->comment('Extra headlines that flip-cycle after title, one per line');
            $table->string('text', 500)->nullable();
            $table->string('button_text', 255)->nullable();
            $table->string('button_url', 255)->nullable();
            $table->string('meta_left', 255)->nullable();
            $table->string('meta_right', 255)->nullable();
            // Bare filenames, same convention as every other admin upload
            // (sliders/, pages/, etc.) — video under FRONT_IMG_PATH's own
            // 'hero-video/' subdir, poster alongside it.
            $table->string('video_file', 255)->nullable();
            $table->string('poster_image', 255)->nullable();
            $table->string('overlay_color', 20)->nullable();
            $table->decimal('overlay_opacity', 8, 2)->nullable();
            // Default ON: the single biggest performance lever an admin has
            // here — most mobile visitors on cellular data, autoplay often
            // blocked anyway. Poster-only on mobile unless explicitly opted
            // out of.
            $table->boolean('disable_on_mobile')->default(1);
            $table->timestamps();

            $table->unique('language_id');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hero_video_settings');
    }
}
