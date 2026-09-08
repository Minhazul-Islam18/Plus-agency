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
    }
}
