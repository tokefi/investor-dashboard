<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgentIdColumnToInvestmentInvestorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investment_investor', function(Blueprint $table) {
            $table->integer('agent_id')->unsigned()->nullable();
            $table->foreign('agent_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('investment_investor', function(Blueprint $table) {
            $table->dropForeign('investment_investor_agent_id_foreign');
            $table->dropColumn('agent_id');
        });
    }
}
