<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMasterRedemptionColumnToRedemptionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('redemption_requests', function(Blueprint $table) {
            $table->integer('master_redemption')->unsigned()->nullable();
            $table->foreign('master_redemption')->references('id')->on('redemption_requests');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('redemption_requests', function(Blueprint $table) {
            $table->dropForeign('redemption_requests_master_redemption_foreign');
            $table->dropColumn('master_redemption');
        });
    }
}
