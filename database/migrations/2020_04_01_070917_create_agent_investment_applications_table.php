<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentInvestmentApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_investment_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('projects');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('client_first_name');
            $table->string('client_last_name');
            $table->string('client_email');
            $table->string('phone_number', 15);
            $table->integer('investment_amount')->unsigned();
            $table->string('account_name')->nullable();
            $table->string('bsb')->nullable();
            $table->string('account_number')->nullable();
            $table->string('line_1')->nullable();
            $table->string('line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('project_site')->default('https://estatebaron.com');
            $table->string('country', 30)->nullable();
            $table->string('tfn')->nullable();
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
        Schema::drop('agent_investment_applications');
    }
}
