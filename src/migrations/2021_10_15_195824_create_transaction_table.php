<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_zaincash', function (Blueprint $table) {
            $table->id();
            $table->string("transactionId")->index();
            $table->enum("status" , ['pending', 'paid', 'failed'])->default("pending");
            $table->string("serviceType")->nullable();
            $table->bigInteger("amount");
            $table->string("extras")->default("{}");
            $table->string('applicant_ip')->nullable();
            $table->string('orderId')->nullable();
            $table->string('operationId')->nullable();
            $table->timestamp("paid_at")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_zaincash');
    }
}
