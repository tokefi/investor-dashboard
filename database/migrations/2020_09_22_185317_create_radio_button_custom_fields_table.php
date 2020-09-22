<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRadioButtonCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('radio_button_custom_fields', function (Blueprint $table) {
            $table->increments('id');
             $table->integer('radio_custom_field')->unsigned();
            $table->foreign('radio_custom_field')->on('custom_fields')->references('id');
            $table->string('label');
            $table->string('value');
            $table->string('site_url');
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
        Schema::drop('radio_button_custom_fields');
    }
}
