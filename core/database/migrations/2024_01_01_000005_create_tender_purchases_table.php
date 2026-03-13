<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderPurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('tender_purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tender_id');
            $table->integer('user_id')->nullable();
            $table->string('order_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('technical_proposal_fee', 15, 2)->default(0);
            $table->decimal('financial_proposal_fee', 15, 2)->default(0);
            $table->decimal('summary_fee', 15, 2)->default(0);
            $table->decimal('tender_notice_publication_fee', 15, 2)->default(0);
            $table->string('currency_code')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('gateway_type')->nullable();
            $table->string('payment_status')->default('Pending');
            $table->string('receipt')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tender_purchases');
    }
}
