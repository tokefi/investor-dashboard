<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldMasterInvestmentIdToInvestmentInvestorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investment_investor', function(Blueprint $table) {
            $table->integer('master_investment')->unsigned()->nullable();
            $table->foreign('master_investment')->references('id')->on('investment_investor');
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
            $table->dropForeign('investment_investor_master_investment_foreign');
            $table->dropColumn('master_investment');
        });
    }
}
