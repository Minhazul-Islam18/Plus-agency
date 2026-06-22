<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaidAtToTenderPurchasesTable extends Migration
{
    public function up()
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down()
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
}
