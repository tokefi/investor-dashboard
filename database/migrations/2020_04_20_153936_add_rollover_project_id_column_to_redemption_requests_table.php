<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRolloverProjectIdColumnToRedemptionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('redemption_requests', function (Blueprint $table) {
            $table->integer('rollover_project_id')->unsigned()->after('is_money_sent')->nullable();
            $table->foreign('rollover_project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('redemption_requests', function (Blueprint $table) {
            $table->dropForeign(['rollover_project_id']);
            $table->dropColumn('rollover_project_id');
        });
    }
}
