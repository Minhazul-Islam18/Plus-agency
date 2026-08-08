<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeviceBrowserStatusToGuestsTable extends Migration
{
    public function up()
    {
        $columns = [
            'device'  => fn(Blueprint $t) => $t->string('device', 50)->nullable(),
            'browser' => fn(Blueprint $t) => $t->string('browser', 50)->nullable(),
            // 1 - active, 0 - inactive (subscription expired/revoked, detected
            // when a send attempt gets a "gone" response from the push service).
            'status'  => fn(Blueprint $t) => $t->tinyInteger('status')->default(1),
        ];

        foreach ($columns as $col => $addFn) {
            if (!Schema::hasColumn('guests', $col)) {
                Schema::table('guests', $addFn);
            }
        }
    }

    public function down()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['device', 'browser', 'status']);
        });
    }
}
