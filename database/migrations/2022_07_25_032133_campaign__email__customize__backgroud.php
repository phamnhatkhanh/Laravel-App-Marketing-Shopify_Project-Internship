<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CampaignEmailCustomizeBackgroud extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_email_customize_background', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->string('banner')->nullable();
            $table->string('color')->default('#fff');
            $table->string('radius')->nullable();
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

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
        Schema::table('campaign_email_customize_background', function (Blueprint $table) {
                $table->dropForeign(['campaign_id']);
        });
        Schema::dropIfExists('campaign_email_customize_background');
    }
}
