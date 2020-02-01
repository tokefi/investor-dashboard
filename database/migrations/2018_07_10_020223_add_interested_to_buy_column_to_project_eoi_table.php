<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInterestedToBuyColumnToProjectEoiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_eoi', function (Blueprint $table) {
            $table->boolean('interested_to_buy');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_eoi', function (Blueprint $table) {
            $table->dropColumn('interested_to_buy');
        });
    }
}
