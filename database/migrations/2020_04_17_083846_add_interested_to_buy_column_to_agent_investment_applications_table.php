<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInterestedToBuyColumnToAgentInvestmentApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agent_investment_applications', function (Blueprint $table) {
            $table->boolean('interested_to_buy');
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
            $table->dropColumn('interested_to_buy');
        });
    }
}
