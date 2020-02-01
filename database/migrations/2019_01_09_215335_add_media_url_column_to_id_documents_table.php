<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMediaUrlColumnToIdDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('id_documents',function (Blueprint $table){
            $table->string('media_url')->default('https://konkrete.estatebaron.com');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('id_documents',function (Blueprint $table){
            $table->dropColumn('media_url');
        });
    }
}
