<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBicyAbandonNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bicy_abandon_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bicies_id');
            $table->foreign('bicies_id')->references('id')->on('bicies');
            $table->enum('active',[0,1])->default(1);
            $table->enum('ready_for_dispatching',[0,1])->default(0);
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
        Schema::dropIfExists('bicy_abandon_notifications');
    }
}
