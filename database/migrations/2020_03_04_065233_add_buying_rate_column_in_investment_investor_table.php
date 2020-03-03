<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBuyingRateColumnInInvestmentInvestorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investment_investor', function (Blueprint $table) {
            $table->double('buy_rate')->after('amount')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('investment_investor', function (Blueprint $table) {
            $table->dropColumn('buy_rate');
        });
    }
}
