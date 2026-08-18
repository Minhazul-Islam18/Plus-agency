<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Full schema for the Tender module in a single migration.
 *
 * Creates the tender tables and adds the tender-related columns to the shared
 * `basic_settings_extra` and `pages` tables. Every step is guarded, so this
 * runs cleanly on a fresh database and is a harmless no-op where objects
 * already exist (e.g. a database previously built from the split migrations).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tender_categories')) {
            Schema::create('tender_categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id');
                $table->string('name');
                $table->tinyInteger('status')->default(1);
                $table->integer('serial_number');
                $table->timestamps();
            });
        }

        // Some existing installs have this table stuck on latin1 (predates the
        // connection's utf8mb4 default) — that rejects emoji and 4-byte UTF-8
        // with a hard SQL error under strict mode. Convert it if found.
        if (Schema::hasTable('tender_categories') && DB::getDriverName() === 'mysql') {
            $collation = DB::selectOne(
                "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tender_categories'"
            );
            if ($collation && stripos($collation->TABLE_COLLATION, 'utf8mb4') !== 0) {
                DB::statement('ALTER TABLE `tender_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            }
        }

        if (!Schema::hasTable('tenders')) {
            Schema::create('tenders', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('language_id');
                $table->integer('tender_category_id');
                $table->string('country');
                $table->string('tender_code')->nullable();
                $table->string('title');
                $table->string('slug');
                $table->dateTime('submission_deadline')->nullable();
                $table->decimal('current_price', 15, 2)->nullable();
                $table->decimal('previous_price', 15, 2)->nullable();
                $table->text('summary')->nullable();
                $table->string('tender_image')->nullable();
                $table->string('video_link')->nullable();
                $table->longText('overview');
                // Set when the expert was picked from Team Members (source of the
                // autofilled fields below); null when entered as a custom expert.
                $table->unsignedBigInteger('expert_member_id')->nullable();
                $table->string('expert_name');
                $table->string('expert_position');
                $table->text('expert_details');
                $table->string('expert_whatsapp');
                $table->string('expert_email');
                $table->string('expert_image')->nullable();
                $table->tinyInteger('is_featured')->default(0);
                // 0 = Inactive (hidden from frontend, default for new tenders while
                // modules/prices are still being set up), 1 = Active (visible).
                $table->tinyInteger('status')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('tenders') && !Schema::hasColumn('tenders', 'expert_member_id')) {
            Schema::table('tenders', function (Blueprint $table) {
                $table->unsignedBigInteger('expert_member_id')->nullable()->after('overview');
            });
        }

        if (!Schema::hasTable('tender_modules')) {
            Schema::create('tender_modules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('tender_id');
                $table->string('name');
                $table->decimal('cost', 15, 2)->nullable();
                $table->text('summary');
                $table->string('tender_file')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tender_sections')) {
            Schema::create('tender_sections', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('tender_module_id');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tender_purchases')) {
            Schema::create('tender_purchases', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('tender_id');
                $table->integer('user_id')->nullable();
                $table->string('order_number');

                // Buyer
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email');
                $table->string('phone_number')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->string('company_name', 200)->nullable();
                $table->text('company_address')->nullable();
                // Sole duplicate-purchase key: a company cannot buy the same tender
                // twice under one registration number, regardless of email used.
                $table->string('company_registration_no', 100)->nullable();

                // Order lines
                $table->integer('qty')->default(1);
                $table->decimal('technical_proposal_fee', 15, 2)->default(0);
                $table->decimal('financial_proposal_fee', 15, 2)->default(0);
                $table->decimal('summary_fee', 15, 2)->default(0);
                $table->decimal('tender_notice_publication_fee', 15, 2)->default(0);
                $table->text('purchased_modules')->nullable();

                // Payment
                $table->string('currency_code')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('gateway_type')->nullable();
                $table->string('payment_status')->default('Pending');
                $table->timestamp('paid_at')->nullable();

                // Access control (admin can suspend a transaction's downloads)
                $table->string('access_status', 20)->default('active'); // active | suspended
                $table->string('suspend_reason', 255)->nullable();
                $table->timestamp('suspended_at')->nullable();

                // Documents
                $table->string('receipt')->nullable();
                $table->string('payment_reference', 100)->nullable();
                $table->string('invoice')->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tender_blacklists')) {
            Schema::create('tender_blacklists', function (Blueprint $table) {
                $table->id();
                // Registration number is the required identifier for a rule; email and
                // phone are optional extra identifiers a rule may also match on.
                $table->string('company_registration_no', 100)->nullable()->index();
                $table->string('email')->nullable()->index();
                $table->string('phone_number')->nullable()->index();
                $table->string('company_name')->nullable();
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }

        // ---- Shared table: basic_settings_extra (tender toggles + settings) ----
        if (Schema::hasTable('basic_settings_extra')) {
            $this->addColumn('basic_settings_extra', 'is_tender', function (Blueprint $t) {
                $t->tinyInteger('is_tender')->default(1)
                    ->comment('1 - activate all pages related to tenders, 0 - deactivate');
            });

            // Breadcrumb
            $this->addColumn('basic_settings_extra', 'tender_breadcrumb_bg', function (Blueprint $t) {
                $t->string('tender_breadcrumb_bg')->nullable()->after('is_tender');
            });
            $this->addColumn('basic_settings_extra', 'tender_breadcrumb_overlay_color', function (Blueprint $t) {
                $t->string('tender_breadcrumb_overlay_color', 20)->nullable()->after('tender_breadcrumb_bg');
            });
            $this->addColumn('basic_settings_extra', 'tender_breadcrumb_overlay_opacity', function (Blueprint $t) {
                $t->decimal('tender_breadcrumb_overlay_opacity', 3, 2)->nullable()->after('tender_breadcrumb_overlay_color');
            });

            // Download watermark
            $this->addColumn('basic_settings_extra', 'tender_watermark_enabled', function (Blueprint $t) {
                $t->tinyInteger('tender_watermark_enabled')->default(1)->after('tender_breadcrumb_overlay_opacity');
            });
            $this->addColumn('basic_settings_extra', 'tender_watermark_template', function (Blueprint $t) {
                $t->text('tender_watermark_template')->nullable()->after('tender_watermark_enabled');
            });
            $this->addColumn('basic_settings_extra', 'tender_watermark_opacity', function (Blueprint $t) {
                $t->decimal('tender_watermark_opacity', 3, 2)->default(0.30)->after('tender_watermark_template');
            });
            $this->addColumn('basic_settings_extra', 'tender_watermark_color', function (Blueprint $t) {
                $t->string('tender_watermark_color', 20)->default('FF0000')->after('tender_watermark_opacity');
            });
            $this->addColumn('basic_settings_extra', 'tender_watermark_font_size', function (Blueprint $t) {
                $t->smallInteger('tender_watermark_font_size')->default(24)->after('tender_watermark_color');
            });
            $this->addColumn('basic_settings_extra', 'tender_watermark_rotation', function (Blueprint $t) {
                $t->smallInteger('tender_watermark_rotation')->default(45)->after('tender_watermark_font_size');
            });

            // PDF encryption
            $this->addColumn('basic_settings_extra', 'tender_pdf_encrypt_enabled', function (Blueprint $t) {
                $t->tinyInteger('tender_pdf_encrypt_enabled')->default(0)->after('tender_watermark_rotation');
            });
            $this->addColumn('basic_settings_extra', 'tender_pdf_password', function (Blueprint $t) {
                $t->string('tender_pdf_password')->nullable()->after('tender_pdf_encrypt_enabled');
            });

            // Seed the default watermark template on any row missing one.
            $default = "{company}\nDownloaded by: {name}\nTender ID: {tender_code}\n{datetime}";
            DB::table('basic_settings_extra')
                ->where(function ($q) {
                    $q->whereNull('tender_watermark_template')->orWhere('tender_watermark_template', '');
                })
                ->update(['tender_watermark_template' => $default]);
        }

        // ---- Shared table: pages (special page types, e.g. Terms) ----
        if (Schema::hasTable('pages')) {
            $this->addColumn('pages', 'page_type', function (Blueprint $t) {
                // null/'' = Normal (unlimited). 'terms' | 'privacy' | 'legal_notice'
                // are special types: only one per language is allowed.
                $t->string('page_type', 50)->nullable()->index()->after('slug');
            });
        }

        // ---- Indexes (match the read patterns the module actually runs) ----
        $this->addIndex('tenders', ['language_id', 'is_featured'], 'idx_tenders_lang_featured');
        $this->addIndex('tenders', ['language_id', 'slug'], 'idx_tenders_lang_slug');
        $this->addIndex('tenders', ['tender_category_id'], 'idx_tenders_category');
        $this->addIndex('tender_modules', ['tender_id', 'status'], 'idx_tmodules_tender_status');
        $this->addIndex('tender_sections', ['tender_module_id'], 'idx_tsections_module');
        $this->addIndex('tender_purchases', ['tender_id'], 'idx_tpurchases_tender');
        $this->addIndex('tender_purchases', ['order_number'], 'idx_tpurchases_order');
        $this->addIndex('tender_purchases', ['email'], 'idx_tpurchases_email');
        $this->addIndex('tender_purchases', ['payment_status'], 'idx_tpurchases_pstatus');
        $this->addIndex('tender_purchases', ['tender_id', 'company_registration_no'], 'idx_tpurchases_regno');
        $this->addIndex('tender_categories', ['language_id', 'status'], 'idx_tcategories_lang_status');
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_sections');
        Schema::dropIfExists('tender_modules');
        Schema::dropIfExists('tender_purchases');
        Schema::dropIfExists('tender_blacklists');
        Schema::dropIfExists('tenders');
        Schema::dropIfExists('tender_categories');

        if (Schema::hasTable('basic_settings_extra')) {
            $cols = [
                'is_tender',
                'tender_breadcrumb_bg', 'tender_breadcrumb_overlay_color', 'tender_breadcrumb_overlay_opacity',
                'tender_watermark_enabled', 'tender_watermark_template', 'tender_watermark_opacity',
                'tender_watermark_color', 'tender_watermark_font_size', 'tender_watermark_rotation',
                'tender_pdf_encrypt_enabled', 'tender_pdf_password',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('basic_settings_extra', $col)) {
                    Schema::table('basic_settings_extra', fn (Blueprint $t) => $t->dropColumn($col));
                }
            }
        }

        if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'page_type')) {
            Schema::table('pages', fn (Blueprint $t) => $t->dropColumn('page_type'));
        }
    }

    /** Add a column only if it does not already exist. */
    private function addColumn(string $table, string $column, callable $definition): void
    {
        if (!Schema::hasColumn($table, $column)) {
            Schema::table($table, $definition);
        }
    }

    /** Add a named index only if the table exists and the index does not. */
    private function addIndex(string $table, array $columns, string $name): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
        if ($exists) {
            return;
        }
        Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
    }
};
