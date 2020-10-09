<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckboxConditionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkbox_conditionals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('custom_field_id')->unsigned();
            $table->foreign('custom_field_id')->on('custom_fields')->references('id');
            $table->integer('checkbox_id')->unsigned();
            $table->boolean('is_linked')->default(0);
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
        Schema::drop('checkbox_conditionals');
    }
}
