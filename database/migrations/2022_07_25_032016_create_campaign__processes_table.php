<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;
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
            $DB_campaigns = DB::connection('mysql_campaigns')->getDatabaseName();
            $DB_campaigns_backup = DB::connection('mysql_campaigns_backup')->getDatabaseName();

            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('name',50);
            $table->string('status')->nullable()->default(null);
            $table->integer('process')->default(0);
            // $table->double('process',10,2)->default(0);
            $table->integer('send_email_done')->unsigned()->default(0);
            $table->integer('send_email_fail')->unsigned()->default(0);
            $table->integer('total_customers')->unsigned()->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on(new Expression($DB_campaigns . '.campaigns'))
                ->constrained()
                ->onDelete('cascade');

            $table->foreign('campaign_id','campaignsProcess_campagins_backup')
                ->references('id')
                ->on(new Expression($DB_campaigns_backup . '.campaigns'))
                ->constrained()
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
                $table->dropForeign('campaignsProcess_campagins_backup');
                $table->dropForeign(['campaign_id']);
                $table->dropColumn('campaign_id');
            });
            Schema::connection('mysql_campaigns_processes')->dropIfExists('campaign_processes');
        }

    }
}
