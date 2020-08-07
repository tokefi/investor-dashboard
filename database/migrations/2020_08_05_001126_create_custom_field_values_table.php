<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('investment_investor_id')->unsigned()->nullable();
            $table->foreign('investment_investor_id')->on('investment_investor')->references('id');
            $table->index('investment_investor_id');
            $table->integer('agent_investment_id')->unsigned()->nullable();
            $table->foreign('agent_investment_id')->on('agent_investment_applications')->references('id');
            $table->integer('custom_field_id')->unsigned();
            $table->foreign('custom_field_id')->on('custom_fields')->references('id');
            $table->longText('value')->nullable();
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
        Schema::drop('custom_field_values');
    }
}
