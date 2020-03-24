<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_children', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('master')->unsigned();
            $table->foreign('master')->references('id')->on('projects');
            $table->integer('child')->unsigned();
            $table->foreign('child')->references('id')->on('projects');
            $table->float('allocation');
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
        Schema::drop('master_children');
    }
}
