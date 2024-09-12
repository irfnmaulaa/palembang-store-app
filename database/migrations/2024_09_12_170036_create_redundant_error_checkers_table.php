<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedundantErrorCheckersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redundant_error_checkers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Product::class)->constrained();
            $table->unsignedBigInteger('from_transaction_product_id');
            $table->foreign('from_transaction_product_id')->references('id')->on('transaction_products');
            $table->unsignedBigInteger('to_transaction_product_id');
            $table->foreign('to_transaction_product_id')->references('id')->on('transaction_products');
            $table->integer('expected_current_stock');
            $table->integer('actual_current_stock');
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
        Schema::dropIfExists('redundant_error_checkers');
    }
}
