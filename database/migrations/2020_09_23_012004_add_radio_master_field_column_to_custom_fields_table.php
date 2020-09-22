<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRadioMasterFieldColumnToCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->integer('radio_master_field')->unsigned()->nullable();
            $table->foreign('radio_master_field')->on('radio_button_custom_fields')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropForeign('custom_fields_radio_master_field_foreign');
            $table->dropColumn('radio_master_field');
        });
    }
}
