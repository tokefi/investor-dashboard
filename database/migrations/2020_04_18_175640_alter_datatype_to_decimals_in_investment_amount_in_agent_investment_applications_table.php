<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDatatypeToDecimalsInInvestmentAmountInAgentInvestmentApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agent_investment_applications', function (Blueprint $table) {
            $table->decimal('investment_amount', 20, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_investment_applications', function (Blueprint $table) {
            $table->integer('investment_amount')->unsigned()->change();
        });
    }
}
