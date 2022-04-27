<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvisionalStickerOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provisional_sticker_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parkings_id');
            $table->integer('quantity');
            $table->integer('printed');
            $table->integer('initial_consecutive');
            $table->integer('final_consecutive');
            $table->text('misc')->nullable();
            $table->foreign('parkings_id')->references('id')->on('parkings');
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
        Schema::dropIfExists('provisional_sticker_orders');
    }
}
