<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDatatypeToDecimalsInSharePerUnitPriceColumnInProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('share_per_unit_price', 10, 4)->default(1)->change();
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
            DB::statement('ALTER TABLE projects MODIFY share_per_unit_price DOUBLE(10,2) DEFAULT 1');
        });
    }
}
