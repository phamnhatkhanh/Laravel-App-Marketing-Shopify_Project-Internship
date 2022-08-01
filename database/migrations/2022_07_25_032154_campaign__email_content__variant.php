<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class CampaignEmailContentVariant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

<<<<<<< HEAD
         Schema::create('campaign_email_content_variant', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->string('name')->nullable();
=======
        Schema::connection('mysql_campaigns')->create('campaign_email_content_variant', function (Blueprint $table) {
            $databaseName = DB::connection('mysql_campaigns')->getDatabaseName();
            
            $table->unsignedBigInteger('campaign_id');
            $table->string(' name');
>>>>>>> m_db_dev
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->foreign('campaign_id')
                ->references('id')
                // ->on('campaigns')
                ->on(new Expression($databaseName . '.campaigns'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::connection('mysql_campaigns')->hasTable('campaign_email_content_variant')){

            Schema::connection('mysql_campaigns')->table('campaign_email_content_variant', function (Blueprint $table) {
                $table->dropForeign(['campaign_id']);
                $table->dropColumn('campaign_id');
            });
            Schema::connection('mysql_campaigns')->dropIfExists('campaign_email_content_variant');
        }


    }
}
