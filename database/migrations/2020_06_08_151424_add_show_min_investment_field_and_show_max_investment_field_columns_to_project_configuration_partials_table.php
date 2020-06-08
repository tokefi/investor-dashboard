<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowMinInvestmentFieldAndShowMaxInvestmentFieldColumnsToProjectConfigurationPartialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_configuration_partials', function (Blueprint $table) {
            $table->boolean('show_min_investment_field')->default(1);
            $table->boolean('show_max_investment_field')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_configuration_partials', function (Blueprint $table) {
            $table->dropColumn('show_min_investment_field');
            $table->dropColumn('show_max_investment_field');
        }); 
    }
}
