<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCampaignsBackupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_campaigns_backup')->create('campaigns', function (Blueprint $table) {
            $databaseName = DB::connection('mysql_stores')->getDatabaseName();
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('name');
            $table->string('subject');
            $table->string('content');
            $table->string('footer');
            $table->timestamps();

            $table->foreign('store_id')
                ->references('id')
                // ->on('stores')
                ->on(new Expression($databaseName . '.stores'))
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
        if(Schema::connection('mysql_campaigns_backup')->hasTable('campaigns')){

            Schema::connection('mysql_campaigns_backup')->table('campaigns', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
            Schema::connection('mysql_campaigns_backup')->dropIfExists('campaigns');
        }
    }
}
