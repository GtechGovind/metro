<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->string('masterTxnId')->nullable();
            $table->string('pg_order_id')->nullable();
            $table->string('number');
            $table->integer("source")->nullable();
            $table->integer("destination")->nullable();
            $table->integer('type');
            $table->integer('count')->nullable();
            $table->double('fare');
            $table->integer("pg_id");
            $table->integer("operator");
            $table->integer("order_status");
            $table->integer("order_type");
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
        Schema::dropIfExists('sale_orders');
    }
}
