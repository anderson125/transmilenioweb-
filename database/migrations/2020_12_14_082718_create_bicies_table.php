<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bicies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parkings_id');
            $table->string('code')->unique();
            $table->unsignedBigInteger('bikers_id');
            $table->string('brand');
            $table->string('color');
            $table->string('tires');
            $table->longText('description');
            $table->unsignedBigInteger('type_bicies_id');
            $table->enum('active',[1,2,3]);
            $table->foreign('parkings_id')->references('id')->on('parkings');
            $table->foreign('bikers_id')->references('id')->on('bikers');
            $table->foreign('type_bicies_id')->references('id')->on('type_bicies');
            $table->string('url_image_back');
            $table->string('url_image_side');
            $table->string('url_image_front');
            $table->string('id_image_back');
            $table->string('id_image_side');
            $table->string('id_image_front');
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
        Schema::dropIfExists('bicies');
    }
}
