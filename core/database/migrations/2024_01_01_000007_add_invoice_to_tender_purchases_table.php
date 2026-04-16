<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceToTenderPurchasesTable extends Migration
{
    public function up()
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->string('invoice')->nullable()->after('receipt');
        });
    }

    public function down()
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->dropColumn('invoice');
        });
    }
}
