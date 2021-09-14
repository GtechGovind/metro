<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_orders', function (Blueprint $table) {
            $table->id();
            $table->string('refund_order_no');
            $table->string('order_no');
            $table->string('masterTxnId');
            $table->string('pg_order_id')->nullable();
            $table->string('number');
            $table->string('refund_charges');
            $table->string('refund_amount');
            $table->string('pg_id');
            $table->integer("operator");
            $table->string('refund_status')->nullable();
            $table->timestamp('timestamp')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refund_orders');
    }
}
