<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Brand-new tables for the Portfolio module overhaul (see
 * ~/Documents/15-Portoflios/Recommandation sur Portofolios-*.docx):
 *
 * - portfolio_sectors: a manageable, per-language taxonomy (Énergie, Eau
 *   potable, Génie civil, ...) — distinct from the existing Service model
 *   (the firm's own service offerings). Mirrors bcategories/scategories.
 * - portfolio_documents: per-project downloadable attachments (PDF/DOC/
 *   DOCX/XLSX), mirroring portfolio_images exactly.
 * - portfolio_statuses: a manageable, per-language list (Ongoing/Pending/
 *   Completed, or whatever the admin wants) replacing the old hardcoded
 *   3-value `status` string enum on `portfolios` — same shape as
 *   portfolio_sectors (admin request: "Like Sector, Add Status module").
 */
class CreatePortfolioSectorsAndDocumentsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('portfolio_sectors')) {
            Schema::create('portfolio_sectors', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id')->default(0);
                $table->string('name', 255)->nullable();
                $table->tinyInteger('status')->default(1);
                $table->integer('serial_number')->default(0);
            });
        }

        // Subsectors — self-referencing (admin: "Sectors and Subsectors" —
        // Agriculture and Agri-food -> Beekeeping/Fishing/Seeds/...). A
        // subsector IS a portfolio_sectors row, just with parent_id set to
        // its sector's id; a top-level sector has parent_id NULL. Same
        // table/model reused rather than a separate one — subsectors need
        // nothing a sector row doesn't already have (name/status/serial).
        // icon is shown per top-level sector only (see the mockup) —
        // nullable so existing sector rows and every subsector just have
        // none.
        if (Schema::hasTable('portfolio_sectors')) {
            $sectorColumns = [
                'parent_id' => fn (Blueprint $t) => $t->unsignedBigInteger('parent_id')->nullable()->after('id'),
                'icon'      => fn (Blueprint $t) => $t->string('icon', 60)->nullable()->after('name'),
            ];
            foreach ($sectorColumns as $column => $definer) {
                if (!Schema::hasColumn('portfolio_sectors', $column)) {
                    Schema::table('portfolio_sectors', function (Blueprint $table) use ($definer) {
                        $definer($table);
                    });
                }
            }
        }

        if (!Schema::hasTable('portfolio_documents')) {
            Schema::create('portfolio_documents', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('portfolio_id')->nullable();
                $table->string('file', 255)->nullable();
                $table->string('original_name', 255)->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('portfolio_statuses')) {
            Schema::create('portfolio_statuses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id')->default(0);
                $table->string('name', 255)->nullable();
                $table->tinyInteger('status')->default(1);
                $table->integer('serial_number')->default(0);
            });
        }

        // portfolios.status_id — new FK-style column (no formal constraint,
        // matching sector_id's own style). The old `status` string column
        // is left untouched (no destructive migration) but no longer
        // written to by new code; see the one-time backfill this migration
        // also runs below so existing rows keep their current status
        // under the new system instead of ending up unset.
        if (Schema::hasTable('portfolios') && !Schema::hasColumn('portfolios', 'status_id')) {
            Schema::table('portfolios', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable()->after('status');
            });

            $this->seedStatusesAndBackfill();
        }

        // portfolios.subsector_id — cascading Sector -> Subsector on the
        // portfolio form (sector_id stays the top-level pick; this is the
        // more specific one, e.g. Agriculture -> Beekeeping). Nullable — a
        // portfolio can be tagged at sector level only, no subsector forced.
        if (Schema::hasTable('portfolios') && !Schema::hasColumn('portfolios', 'subsector_id')) {
            Schema::table('portfolios', function (Blueprint $table) {
                $table->unsignedBigInteger('subsector_id')->nullable()->after('sector_id');
            });
        }

        // Carousel/hero banner overlay — admin-editable copy for the LEFT
        // side (falls back to title/summary when empty, see
        // Portfolio::overlayTitle()/overlayDescription()) + a controllable
        // tint for both the left scrim and the right highlights panel.
        // Same shape across every image in this portfolio's own gallery —
        // it's a property of the project, not of any one photo.
        if (Schema::hasTable('portfolios')) {
            $overlayColumns = [
                // Project completion date — `start_date` already existed;
                // nothing on this table previously recorded when a project
                // ended. Admin-set, nullable (an ongoing project has none),
                // shown as "Date de fin" alongside Start Date in the admin
                // list.
                'end_date'            => fn (Blueprint $t) => $t->date('end_date')->nullable(),
                'overlay_title'       => fn (Blueprint $t) => $t->string('overlay_title', 300)->nullable(),
                'overlay_subtitle'    => fn (Blueprint $t) => $t->string('overlay_subtitle', 255)->nullable(),
                'overlay_description' => fn (Blueprint $t) => $t->text('overlay_description')->nullable(),
                // Hex without the '#' (matches how `country`/other short
                // codes are stored elsewhere) — nullable, code falls back
                // to the site's own near-black default.
                'overlay_color'       => fn (Blueprint $t) => $t->string('overlay_color', 6)->nullable(),
                // Stored as a 0-100 integer percent (matches the admin
                // slider directly) rather than a 0-1 float — avoids a
                // decimal column for a value that's only ever a whole
                // percent in the UI.
                'overlay_opacity'     => fn (Blueprint $t) => $t->unsignedTinyInteger('overlay_opacity')->nullable(),
                // Independent opacity for the LEFT-side radial "bloom"
                // behind the copy — the original overlay_opacity drives the
                // right icon panel + sector badge, and a bloom strong
                // enough to carry text over a busy photo is often heavier
                // than what looks right on the panel. Nullable: falls back
                // to overlay_opacity when unset (existing rows unchanged).
                'overlay_bloom_opacity' => fn (Blueprint $t) => $t->unsignedTinyInteger('overlay_bloom_opacity')->nullable(),
            ];
            foreach ($overlayColumns as $column => $definer) {
                if (!Schema::hasColumn('portfolios', $column)) {
                    Schema::table('portfolios', function (Blueprint $table) use ($definer) {
                        $definer($table);
                    });
                }
            }
        }

        // Right-panel "icon highlights" (points forts) — a short, admin-
        // sortable list per portfolio, icon (from the existing icon-picker
        // library) + a one-line label. Own table rather than a delimited
        // string column since it needs per-item ordering (serial_number),
        // same reasoning as portfolio_images/portfolio_documents above.
        if (!Schema::hasTable('portfolio_highlights')) {
            Schema::create('portfolio_highlights', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('portfolio_id');
                $table->string('icon', 60)->nullable();
                $table->string('label', 255)->nullable();
                $table->integer('serial_number')->default(0);
            });
        }

        // Partners multi-select — picks from the existing "Our Partners"
        // module (App\Partner, same table the homepage partners section
        // manages) instead of the old free-text `partners` column, which
        // is left in place untouched (no destructive migration) but no
        // longer written to by new code. Plain pivot, no serial_number —
        // selection order isn't meaningful here (unlike highlights above),
        // partners just render as an unordered set of logos.
        if (!Schema::hasTable('portfolio_partners')) {
            Schema::create('portfolio_partners', function (Blueprint $table) {
                $table->unsignedBigInteger('portfolio_id');
                $table->unsignedBigInteger('partner_id');
                $table->primary(['portfolio_id', 'partner_id']);
            });
        }
    }

    /**
     * One-time data migration: for every language that already has at
     * least one portfolio, create the 3 equivalent PortfolioStatus rows
     * (translated via the same __() keys the old hardcoded labels used —
     * see portfolio-identity-card.blade.php's old $statusLabels), then
     * point every existing portfolio at the matching new row by its old
     * `status` string value. Nothing is destructive and nothing changes
     * for a portfolio whose old `status` doesn't match one of the 3
     * known values — it's simply left with status_id null, same as any
     * newly created portfolio before an admin picks one.
     */
    private function seedStatusesAndBackfill(): void
    {
        // Guards the method itself, not just its one call site in up() —
        // that call is already behind !hasColumn('status_id'), but this
        // is a one-time DATA migration (unlike the schema checks above,
        // safe to repeat), so a second call from anywhere (a manual
        // tinker re-run, a future refactor of up()) would insert a
        // duplicate set of status rows per language instead of a no-op.
        if (DB::table('portfolio_statuses')->exists()) {
            return;
        }

        $labels = [
            'In Progress' => ['en' => 'Ongoing', 'fr' => 'En cours'],
            'Pending' => ['en' => 'Pending', 'fr' => 'En attente'],
            'Completed' => ['en' => 'Completed', 'fr' => 'Réalisé'],
        ];

        $languageIds = DB::table('portfolios')
            ->whereNotNull('language_id')
            ->distinct()
            ->pluck('language_id');

        foreach ($languageIds as $languageId) {
            $language = DB::table('languages')->where('id', $languageId)->first();
            $code = $language->code ?? 'en';

            $serial = 0;
            foreach ($labels as $oldValue => $translations) {
                $name = $translations[$code] ?? $translations['en'];

                $statusId = DB::table('portfolio_statuses')->insertGetId([
                    'language_id' => $languageId,
                    'name' => $name,
                    'status' => 1,
                    'serial_number' => $serial++,
                ]);

                DB::table('portfolios')
                    ->where('language_id', $languageId)
                    ->where('status', $oldValue)
                    ->update(['status_id' => $statusId]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('portfolio_documents');
        Schema::dropIfExists('portfolio_sectors');
        Schema::dropIfExists('portfolio_statuses');
        Schema::dropIfExists('portfolio_highlights');
    }
}
