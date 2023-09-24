<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNonconformingProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nonconforming_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('primary_product_id');
            $table->foreign('primary_product_id')->references('id')->on('primaries_products');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('observation')->nullable();
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
        Schema::dropIfExists('nonconforming_products');
    }
}
