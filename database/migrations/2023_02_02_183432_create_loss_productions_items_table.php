<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLossProductionsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loss_productions_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loss_production_id');
            $table->foreign('loss_production_id')->references('id')->on('loss_productions');
            $table->unsignedBigInteger('primary_product_id');
            $table->foreign('primary_product_id')->references('id')->on('primaries_products');
            $table->decimal('loss_quantity', 10, 2);
            $table->decimal('mixing_area_l1', 10, 2);
            $table->decimal('mixing_area_l2', 10, 2);
            $table->decimal('total', 10, 2);
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
        Schema::dropIfExists('loss_productions_items');
    }
}
