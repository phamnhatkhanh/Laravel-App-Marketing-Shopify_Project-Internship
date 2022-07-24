<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            // $databaseName = DB::connection('mysql_authen')->getDatabaseName();
            $table->id();
            $table->string('title');
            $table->text('desc');
            $table->integer('price');
            $table->integer('stock');
            $table->integer('discount');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        if(Schema::hasTable('products')){

            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            Schema::dropIfExists('products');
        }
    }
}
