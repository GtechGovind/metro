<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('passes', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->string('masterTxnId');
            $table->string('pass_type');
            $table->string('number');
            $table->double('price');
            $table->integer('source')->nullable();
            $table->integer('destination')->nullable();
            $table->double('balance');
            $table->integer('trips')->nullable();
            $table->integer('operator_id');
            $table->integer('pass_status');
            $table->dateTime('travel_date');
            $table->dateTime('master_expiry');
            $table->dateTime('grace_expiry');
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
        Schema::dropIfExists('passes');
    }
}
