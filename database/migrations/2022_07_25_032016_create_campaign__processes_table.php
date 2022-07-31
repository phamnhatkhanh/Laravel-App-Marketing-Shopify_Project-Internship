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
            $table->unsigned('process')->default(0);
            $table->unsigned('send_email_done')->default(0);
            $table->unsigned('send_email_fail')->default(0);
            $table->unsigned('total_customers')->default(0);
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
