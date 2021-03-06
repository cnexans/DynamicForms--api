<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_instances', function (Blueprint $table) {
            $table->increments('id');

            //Respuesta del formulario hecha por un usuario

            //Referencia al formulario
            $table->integer('form_id')->unsigned();
            $table->foreign('form_id')->references('id')->on('forms');

            //Usuario que respondio esta instancia
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            
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
        Schema::dropIfExists('form_answers');

        Schema::drop('form_instances');
    }
}
