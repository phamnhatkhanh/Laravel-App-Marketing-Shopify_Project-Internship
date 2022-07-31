<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class CreateCampaignProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_campaigns_processes')->create('campaign_processes', function (Blueprint $table) {
            $databaseName = DB::connection('mysql_campaigns')->getDatabaseName();
            
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('name');
            $table->string('status');
            $table->string('process');
            $table->string('send_email_done');
            $table->string('send_email_fail');
            $table->string('total_customers');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on(new Expression($databaseName . '.campaigns'))
                // ->on('campaigns')
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
        if(Schema::connection('mysql_campaigns_processes')->hasTable('campaign_processes')){

            Schema::connection('mysql_campaigns_processes')->table('campaign_processes', function (Blueprint $table) {
                $table->dropForeign(['campaign_id']);
                $table->dropColumn('campaign_id');
            });
            Schema::connection('mysql_campaigns_processes')->dropIfExists('campaign_processes');
        }

    }
}
