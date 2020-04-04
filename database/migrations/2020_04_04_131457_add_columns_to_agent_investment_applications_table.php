<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToAgentInvestmentApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agent_investment_applications', function(Blueprint $table) {
            $table->string('joint_investor_first_name')->nullable();
            $table->string('joint_investor_last_name')->nullable();
            $table->string('investing_company')->nullable();
            $table->string('investing_as');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_investment_applications', function(Blueprint $table) {
            $table->dropColumn('joint_investor_first_name');
            $table->dropColumn('joint_investor_last_name');
            $table->dropColumn('investing_company');
            $table->dropColumn('investing_as');
        });
    }
}
