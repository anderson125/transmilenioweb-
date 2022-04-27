<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBikersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bikers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->unsignedBigInteger('type_documents_id');            
            $table->unsignedBigInteger('document')->unique();
            $table->date('birth');
            $table->unsignedBigInteger('genders_id');
            $table->unsignedBigInteger('parkings_id');    
            $table->string('phone');
            $table->string('email');
            $table->string('url_img');
            $table->string('id_img');
            $table->string('confirmation')->nullable(true);
            $table->unsignedBigInteger('jobs_id');
            $table->string('neighborhoods_id');
            $table->enum('levels_id',[
                'Estrato 1',
                'Estrato 2',
                'Estrato 3',
                'Estrato 4',
                'Estrato 5',
                'Estrato 6',
            ]);
            $table->string('code')->nullable();
            $table->date('register');
            $table->enum('active',[1,2,3]);
            $table->tinyInteger('auth')->default(2);

            $table->foreign('type_documents_id')->references('id')->on('type_documents');
            $table->foreign('parkings_id')->references('id')->on('parkings');
            $table->foreign('genders_id')->references('id')->on('genders');
            $table->foreign('jobs_id')->references('id')->on('jobs');
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
        Schema::dropIfExists('bikers');
    }
}
