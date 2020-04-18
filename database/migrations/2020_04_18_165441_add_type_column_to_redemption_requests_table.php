<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeColumnToRedemptionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('redemption_requests', function (Blueprint $table) {
            $table->string('type', 20)->default('ENCASH');
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
            $table->dropColumn('type');
        });
    }
}
