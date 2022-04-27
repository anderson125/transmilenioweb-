<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parkings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->bigInteger('capacity');
            $table->tinyInteger('active')->default(1);
            $table->unsignedBigInteger('type_parkings_id');
            $table->bigInteger('bike_count')->default('0');
            $table->foreign('type_parkings_id')->references('id')->on('type_parkings');
            $table->unsignedBigInteger('stations_id');
            $table->foreign('stations_id')->references('id')->on('stations');
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
        Schema::dropIfExists('parkings');
    }
}
