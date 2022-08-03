<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_customers')->create('customers', function (Blueprint $table) {

            $databaseName = DB::connection('mysql_stores')->getDatabaseName();
            $table->bigInteger('id')->unsigned()->primary();
            $table->unsignedBigInteger('store_id');
            $table->string("first_name",50);
            $table->string("last_name",50);
            $table->string("email",50)->nullable();
            $table->string("phone",20)->nullable();
            $table->string("country",50)->nullable();
            $table->string("orders_count");
            $table->string("total_spent");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->foreign('store_id')
                ->references('id')
                ->on(new Expression($databaseName . '.stores'))
                // ->on('stores')
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
        if(Schema::connection('mysql_customers')->hasTable('customers')){

            Schema::connection('mysql_customers')->table('customers', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
            Schema::connection('mysql_customers')->dropIfExists('customers');
        }

    }
}
