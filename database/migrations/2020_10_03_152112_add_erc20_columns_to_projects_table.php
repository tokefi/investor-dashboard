<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddErc20ColumnsToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('erc20_contract_address')->nullable();
            $table->string('erc20_wallet_address')->nullable();
            $table->string('erc20_project_token',4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('erc20_contract_address');
            $table->dropColumn('erc20_wallet_address');
            $table->dropColumn('erc20_project_token');
        });
    }
}
