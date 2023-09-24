<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchesItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatches_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_final_id');
            $table->foreign('product_final_id')->references('id')->on('products_finals');
            $table->unsignedBigInteger('dispatch_id');
            $table->foreign('dispatch_id')->references('id')->on('dispatches');
            $table->decimal('quantity');
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
        Schema::dropIfExists('dispatches_items');
    }
}
