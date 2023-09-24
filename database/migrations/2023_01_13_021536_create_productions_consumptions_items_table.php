<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionsConsumptionsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productions_consumptions_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_consumption_id');
            $table->foreign('production_consumption_id')->references('id')->on('productions_consumptions');
            $table->unsignedBigInteger('primary_product_id');
            $table->foreign('primary_product_id')->references('id')->on('primaries_products');
            $table->decimal('to_mixer', 10, 2);
            $table->decimal('remainder1', 10, 2);
            $table->decimal('remainder2', 10, 2);
            $table->decimal('consumption_production', 10, 2);
            $table->decimal('consumption_percentage', 10, 2);
            $table->decimal('theoretical_consumption', 10, 2);
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
        Schema::dropIfExists('productions_consumptions_items');
    }
}
