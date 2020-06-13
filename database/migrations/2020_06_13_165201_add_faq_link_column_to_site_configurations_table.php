<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFaqLinkColumnToSiteConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('site_configurations', function (Blueprint $table) {
            $table->string('faq_link')->nullable();
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
            $table->dropColumn('faq_link');
        }); 
    }
}
