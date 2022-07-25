<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string("name_merchant",);
            $table->string("email",);
            $table->string("phone");
            $table->string("myshopify_domain");
            $table->string("domain");
            $table->string("access_token");
            $table->string("address");
            $table->string("province");
            $table->string("city");
            $table->string("zip");
            $table->string("country_name");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}
