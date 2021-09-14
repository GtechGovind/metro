<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileQrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_qrs', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->string('masterTxnId');
            $table->string('slave_qr_code');
            $table->bigInteger('number');
            $table->integer('source')->nullable();
            $table->integer('destination')->nullable();
            $table->integer('type');
            $table->string('qr_direction');
            $table->text('qr_code_data');
            $table->string('qr_status');
            $table->dateTime('slave_expiry_date');
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
        Schema::dropIfExists('mobile_qrs');
    }
}
