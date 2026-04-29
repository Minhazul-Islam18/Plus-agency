<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentReferenceToTenderPurchasesTable extends Migration
{
    public function up()
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->string('payment_reference', 100)->nullable()->after('receipt');
        });
    }

    public function down()
    {
        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->dropColumn('payment_reference');
        });
    }
}
