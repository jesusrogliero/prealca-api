<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsumptionsSuppliesMinorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumptions_supplies_minors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supply_minor_id');
            $table->foreign('supply_minor_id')->references('id')->on('supplies_minors');
            $table->unsignedBigInteger('consumption_id');
            $table->foreign('consumption_id')->references('id')->on('productions_consumptions');
            $table->integer('number_packages');
            $table->decimal('consumption', 10, 2);
            $table->decimal('consumption_bags', 10, 2);
            $table->decimal('envoplast_consumption', 10, 2);
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
        Schema::dropIfExists('consumptions_supplies_minors');
    }
}
