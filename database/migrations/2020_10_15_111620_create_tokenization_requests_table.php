<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokenizationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokenization_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('project_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('projects');
            $table->float('request_amount');
            $table->float('accepted_amount')->default(0);
            $table->float('price')->default(0);
            $table->integer('status_id')->unsigned();
            $table->foreign('status_id')->references('id')->on('tokenization_statuses');
            $table->string('comments', 200)->nullable();
            $table->boolean('is_money_sent')->default(0);
            $table->string('type', 20)->default('TOKENIZATION');
            $table->integer('master_tokenization')->unsigned()->nullable();
            $table->foreign('master_tokenization')->references('id')->on('tokenization_requests');
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
        Schema::drop('tokenization_requests');
    }
}
