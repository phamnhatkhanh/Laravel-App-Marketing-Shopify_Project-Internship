<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            // $databaseName = DB::connection('mysql_products')->getDatabaseName();
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('customer');
            $table->text('review');
            $table->integer('star');
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                // ->on('products')
                ->on('products')
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
        if(Schema::hasTable('reviews')){

            Schema::table('reviews', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
            Schema::dropIfExists('reviews');
        }
    }
}
