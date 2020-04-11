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
            $table->string('wholesale_investing_as')->nullable();
            $table->string('accountant_name_and_firm')->nullable();
            $table->string('accountant_professional_body_designation')->nullable();
            $table->string('accountant_email')->nullable();
            $table->string('accountant_phone', 15)->nullable();
            $table->text('equity_investment_experience_text')->nullable();
            $table->string('experience_period')->nullable();
            $table->text('unlisted_investment_experience_text')->nullable();
            $table->text('understand_risk_text')->nullable();
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
            $table->dropColumn('wholesale_investing_as');
            $table->dropColumn('accountant_name_and_firm');
            $table->dropColumn('accountant_professional_body_designation');
            $table->dropColumn('accountant_email');
            $table->dropColumn('accountant_phone');
            $table->dropColumn('equity_investment_experience_text');
            $table->dropColumn('experience_period');
            $table->dropColumn('unlisted_investment_experience_text');
            $table->dropColumn('understand_risk_text');
        });
    }
}
