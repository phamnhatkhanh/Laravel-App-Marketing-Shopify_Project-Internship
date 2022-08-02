<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_campaigns')->create('campaigns', function (Blueprint $table) {
            $databaseName = DB::connection('mysql_stores')->getDatabaseName();
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('name',50);
            $table->string('subject',200);
            $table->longText('content');
            $table->string('footer',200);
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
        if(Schema::connection('mysql_campaigns')->hasTable('campaigns')){

            Schema::connection('mysql_campaigns')->table('campaigns', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
            Schema::connection('mysql_campaigns')->dropIfExists('campaigns');
        }
    }
}
