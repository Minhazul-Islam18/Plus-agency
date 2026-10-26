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
            // One repeater row per flip-cycling headline — {"text": "...",
            // "accent"?: "..."}. `accent` is the exact substring of `text`
            // to highlight in the accent gradient (admin free text has no
            // markup for "which word is the accent", so it's a sibling
            // field per row rather than guessed) — case-sensitive, must
            // match verbatim or that row just renders plain white, never a
            // rendering error. First row shows initially, further rows
            // cycle in after it.
            $table->json('titles')->nullable();
            $table->string('text', 500)->nullable();
            $table->string('button_text', 255)->nullable();
            $table->string('button_url', 255)->nullable();
            // Bare filenames, same convention as every other admin upload
            // (sliders/, pages/, etc.) — video under FRONT_IMG_PATH's own
            // 'hero-video/' subdir, poster alongside it.
            // 'local' (default, existing uploaded MP4) or 'youtube' — the
            // YouTube player is never requested until the visitor clicks the
            // poster (see hero-video.blade.php), so adding the option never
            // costs an upfront request on 'local' pages.
            $table->string('video_source', 20)->default('local');
            $table->string('video_file', 255)->nullable();
            $table->string('youtube_url', 500)->nullable();
            $table->string('poster_image', 255)->nullable();
            $table->string('overlay_color', 20)->nullable();
            $table->decimal('overlay_opacity', 8, 2)->nullable();
            // Default ON: the single biggest performance lever an admin has
            // here — most mobile visitors on cellular data, autoplay often
            // blocked anyway. Poster-only on mobile unless explicitly opted
            // out of.
            $table->boolean('disable_on_mobile')->default(1);
            // The small orbiting "SCROLL TO EXPLORE" prompt bottom-center —
            // was hardcoded in the partial, admin had no way to change or
            // translate it. Null falls back to that same default string.
            $table->string('scroll_text', 100)->nullable();
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
