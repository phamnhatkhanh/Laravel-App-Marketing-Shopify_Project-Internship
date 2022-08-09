<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateCustomersBackup1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::connection('mysql_customers_backup_1')->create('customers', function (Blueprint $table) {

            $databaseName = DB::connection('mysql_stores')->getDatabaseName();
            $table->bigInteger('id')->unsigned()->primary();
            $table->unsignedBigInteger('store_id');
            $table->string("first_name",50);
            $table->string("last_name",50);
            $table->string("email",50);
            $table->string("phone",20);
            $table->string("country",100)->nullable();
            $table->string("orders_count");
            $table->string("total_spent");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();


            $table->foreign('store_id')
                ->references('id')
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
        if(Schema::connection('mysql_customers_backup_1')->hasTable('customers')){
            Schema::connection('mysql_customers_backup_1')->table('customers', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
            Schema::connection('mysql_customers_backup_1')->dropIfExists('customers');
        }
    }
}
