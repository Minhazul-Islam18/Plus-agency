<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes matching hot query paths that had none: blogs are filtered by
 * language_id/bcategory_id and looked up by slug on every public blog page;
 * admins are looked up by email since login accepts username OR email.
 * Guarded/idempotent, same pattern as 2024_01_01_000001_create_tender_module.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('blogs', ['language_id'], 'idx_blogs_language');
        $this->addIndex('blogs', ['bcategory_id'], 'idx_blogs_bcategory');
        $this->addIndex('blogs', ['slug'], 'idx_blogs_slug');

        $this->addIndex('admins', ['email'], 'idx_admins_email');
        $this->addIndex('admins', ['role_id'], 'idx_admins_role');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('blogs', 'idx_blogs_language');
        $this->dropIndexIfExists('blogs', 'idx_blogs_bcategory');
        $this->dropIndexIfExists('blogs', 'idx_blogs_slug');

        $this->dropIndexIfExists('admins', 'idx_admins_email');
        $this->dropIndexIfExists('admins', 'idx_admins_role');
    }

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

    private function dropIndexIfExists(string $table, string $name): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
        if (!$exists) {
            return;
        }
        Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
    }
};
