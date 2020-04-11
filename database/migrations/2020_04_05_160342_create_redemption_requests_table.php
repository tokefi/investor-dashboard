<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedemptionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redemption_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('project_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('projects');
            $table->float('request_amount');
            $table->float('accepted_amount')->default(0);
            $table->float('price')->default(0);
            $table->integer('status_id')->unsigned();
            $table->foreign('status_id')->references('id')->on('redemption_statuses');
            $table->string('comments', 200)->nullable();
            $table->boolean('is_money_sent')->default(0);
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
        Schema::drop('redemption_requests');
    }
}
