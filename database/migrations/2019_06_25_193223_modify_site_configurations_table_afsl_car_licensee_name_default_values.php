<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySiteConfigurationsTableAfslCarLicenseeNameDefaultValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_configurations', function (Blueprint $table) {
            $table->string('licensee_name')->default('Konkrete Distributed Registries LTD (ABN 67617252909)')->nullable()->change();
            $table->string('afsl_no')->default('000299812')->nullable()->change();
            $table->string('car_no')->default('001251881')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_configurations', function (Blueprint $table) {
            $table->string('licensee_name')->default('Ricard Securities')->nullable()->change();
            $table->string('afsl_no')->default('299812')->nullable()->change();
            $table->string('car_no')->default('001251881')->nullable()->change();
        });
    }
}
