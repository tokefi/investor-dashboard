<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMasterFieldColumnToCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->integer('master_field')->unsigned()->nullable();
            $table->foreign('master_field')->on('custom_fields')->references('id');
            $table->boolean('show_custom_field')->default(1);
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
            $table->dropForeign('custom_fields_master_field_foreign');
            $table->dropColumn('master_field');
            $table->dropColumn('show_custom_field');
        });
    }
}
