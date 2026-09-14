<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editable-slug feature (Portfolio/Blog/Tender): when an admin changes an
 * existing item's slug, the OLD slug is recorded here pointing at the
 * item's id — the front controllers look old slugs up here and issue a
 * 301 to wherever that item's slug lives NOW (not a stored "new_slug"
 * string, which would rot into a redirect chain if the slug changes
 * again later — always resolving from the live row self-heals that).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('url_redirects')) {
            Schema::create('url_redirects', function (Blueprint $table) {
                $table->id();
                // 'portfolio' | 'blog' | 'tender' — which table target_id points into.
                $table->string('module', 40);
                $table->string('old_slug', 500);
                $table->unsignedBigInteger('target_id');
                $table->timestamp('created_at')->nullable();

                $table->unique(['module', 'old_slug']);
                $table->index(['module', 'target_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('url_redirects');
    }
};
