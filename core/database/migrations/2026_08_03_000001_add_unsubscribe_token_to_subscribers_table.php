<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddUnsubscribeTokenToSubscribersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('subscribers', 'unsubscribe_token')) {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->string('unsubscribe_token', 64)->nullable()->after('email');
            });
        }

        // Backfill existing rows (added nullable so this migration doesn't
        // fail if it's ever re-run against a partially-populated table).
        \DB::table('subscribers')->whereNull('unsubscribe_token')->orWhere('unsubscribe_token', '')
            ->orderBy('id')->get(['id'])->each(function ($row) {
                \DB::table('subscribers')->where('id', $row->id)->update(['unsubscribe_token' => Str::random(48)]);
            });

        if (!$this->hasUnique('subscribers', 'subscribers_unsubscribe_token_unique')) {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->unique('unsubscribe_token');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('subscribers', 'unsubscribe_token')) {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->dropUnique('subscribers_unsubscribe_token_unique');
                $table->dropColumn('unsubscribe_token');
            });
        }
    }

    private function hasUnique(string $table, string $indexName): bool
    {
        return \DB::table('information_schema.statistics')
            ->where('table_schema', \DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
}
