<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailedStickerOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detailed_sticker_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parkings_id');
            $table->unsignedBigInteger('bicies_id');
            $table->unsignedBigInteger('users_id');
            $table->tinyInteger('active');
            $table->foreign('parkings_id')->references('id')->on('parkings');
            $table->foreign('bicies_id')->references('id')->on('bicies');
            $table->foreign('users_id')->references('id')->on('users');
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
        Schema::dropIfExists('detailed_sticker_orders');
    }
}
