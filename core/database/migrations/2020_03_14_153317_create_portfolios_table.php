<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortfoliosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('portfolios')) {
            Schema::create('portfolios', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id')->default(0);
                $table->string('title', 300)->nullable();
                $table->string('slug', 300)->nullable();
                $table->string('start_date', 255)->nullable();
                $table->string('submission_date', 255)->nullable();
                $table->string('client_name', 255)->nullable();
                $table->text('tags')->nullable();
                $table->string('featured_image', 255)->nullable();
                $table->integer('service_id')->nullable();
                $table->binary('content')->nullable();
                $table->string('status', 20)->nullable();
                $table->integer('serial_number')->default(0);
                $table->text('meta_keywords')->nullable();
                $table->text('meta_description')->nullable();
                $table->timestamps();
            });
        }

        // Title limit raised from 255 to 300 chars — slug is derived
        // directly from title (slug_create(), no truncation), so it must
        // stay wide enough to hold the same length.
        if (Schema::hasTable('portfolios')) {
            Schema::table('portfolios', function (Blueprint $table) {
                $table->string('title', 300)->nullable()->change();
                $table->string('slug', 300)->nullable()->change();
            });
        }

        // Portfolio module overhaul ("evidence of competence" redesign, see
        // ~/Documents/15-Portoflios/Recommandation sur Portofolios-*.docx):
        // structured reference fields on top of the old image+title+article
        // shape. sector_id/country/year/summary/partners are simple scalars;
        // the six problematique..impact fields are plain newline-delimited
        // text (one bullet per non-empty line on render) — matches the admin
        // mockup's plain <textarea> inputs, no rich text needed here.
        // is_published/is_archived are new, independent of the existing
        // `status` column (which already holds "In Progress"/"Completed" —
        // that's the mockup's "Statut", just gaining a third "Pending" value
        // in code, no schema change needed for that part).
        $newColumns = [
            'sector_id'            => fn (Blueprint $t) => $t->unsignedBigInteger('sector_id')->nullable(),
            'country'              => fn (Blueprint $t) => $t->string('country', 2)->nullable(),
            'year'                 => fn (Blueprint $t) => $t->string('year', 10)->nullable(),
            'summary'              => fn (Blueprint $t) => $t->text('summary')->nullable(),
            'partners'             => fn (Blueprint $t) => $t->string('partners', 255)->nullable(),
            'problematique'        => fn (Blueprint $t) => $t->text('problematique')->nullable(),
            'mission_ica'          => fn (Blueprint $t) => $t->text('mission_ica')->nullable(),
            'expertise_mobilisee'  => fn (Blueprint $t) => $t->text('expertise_mobilisee')->nullable(),
            'solution_approche'    => fn (Blueprint $t) => $t->text('solution_approche')->nullable(),
            'resultat_statut'      => fn (Blueprint $t) => $t->text('resultat_statut')->nullable(),
            'impact'               => fn (Blueprint $t) => $t->text('impact')->nullable(),
            'client_logo'          => fn (Blueprint $t) => $t->string('client_logo', 255)->nullable(),
            'is_published'         => fn (Blueprint $t) => $t->tinyInteger('is_published')->default(1),
            'is_archived'          => fn (Blueprint $t) => $t->tinyInteger('is_archived')->default(0),
            // Admin-chosen icon (Font Awesome class, e.g. "fas fa-bullseye")
            // per "evidence of competence" block — picked from the same
            // icon-picker library already used elsewhere in the admin
            // (Approach points, Statistics, Features, ...). Nullable so
            // existing rows fall back to the old hardcoded default icon.
            'problematique_icon'       => fn (Blueprint $t) => $t->string('problematique_icon', 60)->nullable(),
            'mission_ica_icon'         => fn (Blueprint $t) => $t->string('mission_ica_icon', 60)->nullable(),
            'expertise_mobilisee_icon' => fn (Blueprint $t) => $t->string('expertise_mobilisee_icon', 60)->nullable(),
            'solution_approche_icon'   => fn (Blueprint $t) => $t->string('solution_approche_icon', 60)->nullable(),
            'resultat_statut_icon'     => fn (Blueprint $t) => $t->string('resultat_statut_icon', 60)->nullable(),
            'impact_icon'              => fn (Blueprint $t) => $t->string('impact_icon', 60)->nullable(),
        ];
        foreach ($newColumns as $column => $definer) {
            if (!Schema::hasColumn('portfolios', $column)) {
                Schema::table('portfolios', function (Blueprint $table) use ($definer) {
                    $definer($table);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portfolios');
    }
}
