<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBikerAuthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biker_auths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('biker_young');
            $table->foreign('biker_young')->references('id')->on('bikers');
            $table->unsignedBigInteger('parents_id');
            $table->foreign('parents_id')->references('id')->on('parents');
            $table->unsignedBigInteger('users_id');
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
        Schema::dropIfExists('biker_auths');
    }
}
