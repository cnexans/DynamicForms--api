<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDateValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('date_values', function (Blueprint $table) {
            //Identificador
            $table->increments('id'); 

            //Valor
            $table->date('value'); 

            //Referencia al campo
            $table->integer('field_descriptor_id')->unsigned();
            $table->foreign('field_descriptor_id')->references('id')->on('field_descriptors');

            //Referencia a la instancia del campo
            $table->integer('form_answer_id')->unsigned();
            $table->foreign('form_answer_id')->references('id')->on('form_answers');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('date_values');
    }
}
