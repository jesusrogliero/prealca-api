<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receiver_id');
            $table->foreign('receiver_id')->references('id')->on('receivers');
            $table->unsignedBigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('dispatches_states');
            $table->string('sica_code');
            $table->string('guide_sada');
            $table->decimal('total', 10, 2);
            $table->string('observation');
            $table->string('drive_name');
            $table->string('drive_identity');
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
        Schema::dropIfExists('dispatches');
    }
}
