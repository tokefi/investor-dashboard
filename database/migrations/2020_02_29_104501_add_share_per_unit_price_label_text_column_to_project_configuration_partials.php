<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSharePerUnitPriceLabelTextColumnToProjectConfigurationPartials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_configuration_partials', function (Blueprint $table) {
            $table->string('share_per_unit_price_label_text')->default('share / unit price');
        });   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('project_configuration_partials', function (Blueprint $table) {
            $table->dropColumn('share_per_unit_price_label_text');
        });    
     }
}
