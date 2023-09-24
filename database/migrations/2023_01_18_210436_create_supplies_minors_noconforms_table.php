<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliesMinorsNoconformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplies_minors_noconforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplie_minor_id');
            $table->foreign('supplie_minor_id')->references('id')->on('supplies_minors');
            $table->decimal('quantity', 10, 2);
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
        Schema::dropIfExists('supplies_minors_noconforms');
    }
}
