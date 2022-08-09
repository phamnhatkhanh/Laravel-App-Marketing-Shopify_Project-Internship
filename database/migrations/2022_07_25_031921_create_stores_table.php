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
        Schema::connection('mysql_stores')->create('stores', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string("name_merchant",50);
            $table->string("email",50);
            $table->string("password",100);
            $table->string("phone")->nullable();
            $table->string("myshopify_domain",100);
            $table->string("domain");
            $table->string("access_token");
            $table->string("address",255)->nullable();
            $table->string("province",50)->nullable();
            $table->string("city",50)->nullable();
            $table->string("zip",50)->nullable();
            $table->string("country_name",100)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::connection('mysql_stores')->hasTable('stores')){
           Schema::connection('mysql_stores')->dropIfExists('stores');
        }

    }
}
