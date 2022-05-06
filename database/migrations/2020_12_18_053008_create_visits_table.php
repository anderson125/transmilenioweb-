<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parkings_id');
            $table->foreign('parkings_id')->references('id')->on('parkings');
            $table->string('number');
            $table->unsignedBigInteger('bikers_id');
            $table->foreign('bikers_id')->references('id')->on('bikers');
            $table->unsignedBigInteger('bicies_id');
            $table->foreign('bicies_id')->references('id')->on('bicies');
            $table->date('date_input');
            $table->time('time_input');
            $table->date('date_output');
            $table->time('time_output');
            $table->integer('duration');
            $table->unsignedBigInteger('visit_statuses_id');
            $table->foreign('visit_statuses_id')->references('id')->on('visit_statuses');
            $table->timestamps();

            $table->unique(['number', 'date_input', 'parkings_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visits');
    }
}
