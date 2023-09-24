<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsFinalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_finals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('stock', 10, 2);
            $table->enum('type', ['Institucional', 'Comercial']);
            $table->string('presentation');
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
        Schema::dropIfExists('products_final');
    }
}
