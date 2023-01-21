<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_id')->nullable();
            $table->string('invoice_status')->nullable();
            $table->bigInteger('payment_id')->nullable();
            $table->string('item_name')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->bigInteger('unit_price')->nullable();
            $table->bigInteger('invoice_value')->nullable();
            $table->longText('Result')->nullable();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('plan_id')->references('id')->on('plans');
            $table->foreignId('ads_jobs_id')->nullable()->references('id')->on('adsjobs');
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
        Schema::dropIfExists('orders');
    }
}
