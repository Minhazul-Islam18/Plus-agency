<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDarkHeroSettingsColumns extends Migration
{
    public function up()
    {
        // basic_settings is already too wide (MySQL 65535-byte row-size
        // limit hit adding these here) — use a dedicated table instead,
        // which is also the more correct home for a genuinely separate
        // "Dark Hero Settings" admin page/feature.
        foreach (['dark_hero_title', 'dark_hero_rotating_titles', 'dark_hero_text'] as $col) {
            if (Schema::hasColumn('basic_settings', $col)) {
                Schema::table('basic_settings', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        if (!Schema::hasTable('dark_hero_settings')) {
            Schema::create('dark_hero_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('language_id');
                $table->string('title', 255)->nullable();
                $table->text('rotating_titles')->nullable()
                    ->comment('Extra headlines that flip-cycle after title, one per line');
                $table->string('text', 500)->nullable();
                $table->string('button_text', 255)->nullable();
                $table->string('button_url', 255)->nullable();
                $table->string('bg', 255)->nullable();
                $table->string('overlay_color', 10)->default('000000')->nullable();
                $table->decimal('overlay_opacity', 3, 2)->default(0.5)->nullable();
                $table->timestamps();

                $table->unique('language_id');
                $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('dark_hero_settings');
    }
}
