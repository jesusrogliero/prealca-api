<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsFinalsToWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_finals_to_warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_final_id');
            $table->foreign('product_final_id')->references('id')->on('products_finals');
            $table->unsignedBigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('products_finals_to_warehouses_states');
            $table->string('number_control')->nullable();
            $table->date('date')->nullable();
            $table->string('work_area')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->decimal('quantity');
            $table->string('description')->nullable();
            $table->string('guide_sunagro')->nullable();
            $table->unsignedBigInteger('production_order_id');
            $table->foreign('production_order_id')->references('id')->on('productions_orders');
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
        Schema::dropIfExists('products_finals_to_warehouses');
    }
}
