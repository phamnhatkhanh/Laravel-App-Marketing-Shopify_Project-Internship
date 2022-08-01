<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresBackupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_stores_backup')->create('stores', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string("name_merchant",);
            $table->string("email",);
            $table->string("password",);
            $table->string("phone");
            $table->string("myshopify_domain");
            $table->string("domain");
            $table->string("access_token");
            $table->string("address");
            $table->string("province");
            $table->string("city");
            $table->string("zip");
            $table->string("country_name");
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
        if(Schema::connection('mysql_stores_backup')->hasTable('stores')){
           Schema::connection('mysql_stores_backup')->dropIfExists('stores');
        }
        
    }
}
