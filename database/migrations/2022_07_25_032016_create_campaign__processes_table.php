<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('name',50);
            $table->string('status');
            $table->integer('process')->unsigned()->default(0);
            $table->integer('send_email_done')->unsigned()->default(0);
            $table->integer('send_email_fail')->unsigned()->default(0);
            $table->integer('total_customers')->unsigned()->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('campaigns')
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
        Schema::table('campaign_processes', function (Blueprint $table) {
                $table->dropForeign(['campaign_id']);
        });
        Schema::dropIfExists('campaign_processes');
    }
}
