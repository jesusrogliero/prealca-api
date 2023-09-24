<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('state_id')->default(1);
            $table->foreign('state_id')->references('id')->on('purchases_orders_states');
            $table->unsignedBigInteger('provider_id');
            $table->foreign('provider_id')->references('id')->on('providers');
            $table->string('nro_sada_guide');
            $table->integer('total_products')->default(0);
            $table->decimal('total_load', 10, 2)->default(0);
            $table->decimal('total_nonconforming', 10, 2)->default(0);
            $table->string('observations')->nullable();
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
        Schema::dropIfExists('purchases_orders');
    }
}
