<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productions_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('formula_id');
            $table->foreign('formula_id')->references('id')->on('formulas');
            $table->unsignedBigInteger('product_final_id');
            $table->foreign('product_final_id')->references('id')->on('products_finals');
            $table->decimal('quantity', 10, 2);
            $table->unsignedBigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('productions_orders_states');
            $table->string('issued_by');
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
        Schema::dropIfExists('productions_orders');
    }
}
