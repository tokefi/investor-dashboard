<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCustomFieldValuesToAgentInvestmentApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agent_investment_applications', function (Blueprint $table) {
            $table->longText('custom_field_values')->nullable();
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
            $table->dropColumn('custom_field_values');
        });
    }
}
