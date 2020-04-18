<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEffectiveDateColumnAlterDatatypeToDecimalsInPriceInPricesColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->timestamp('effective_date')->after('price');
            $table->decimal('price', 10, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('effective_date');
            DB::statement('ALTER TABLE prices MODIFY price DOUBLE(10,4)');
        });
    }
}
