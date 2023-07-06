<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recurring_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->date('end_date');
            $table->date('period');          
            $table->integer('vender_id');
            $table->float('policy_number',15,2)->default('0.00');
            $table->float('tax_id',15,2)->default('0.00');
            $table->float('amount',15,2)->default('0.00');
            $table->integer('account_id');
            $table->text('description');
            $table->integer('category_id');
            $table->string('recurring')->nullable();
            $table->integer('payment_method');
            $table->string('reference');
            $table->integer('created_by')->default('0');
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
        Schema::dropIfExists('recurring_payments');
    }
};
