<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLossProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loss_productions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consumption_id');
            $table->foreign('consumption_id')->references('id')->on('productions_consumptions');
            $table->decimal('packing_area', 10, 2);
            $table->decimal('lab', 10, 2);
            $table->decimal('hopper_auger', 10, 2);
            $table->decimal('total_recovered', 10, 2);
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
        Schema::dropIfExists('loss_productions');
    }
}
