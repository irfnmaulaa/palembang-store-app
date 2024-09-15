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
            $table->foreignIdFor(\App\Models\Transaction::class)->constrained();
            $table->foreignIdFor(\App\Models\Product::class)->constrained();
            $table->integer('quantity');
            $table->text('note');
            $table->integer('from_stock');
            $table->integer('to_stock');
            $table->tinyInteger('duplicate_count');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
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
