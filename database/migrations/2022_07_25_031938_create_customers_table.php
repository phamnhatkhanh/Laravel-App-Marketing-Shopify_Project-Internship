<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("email");
            $table->string("phone");
            $table->string("country")->nullable();
            $table->string("orders_count");
            $table->string("total_spent");
            // $table->timestamps();
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->onDelete('cascade');
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
        Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
            });
        Schema::dropIfExists('customers');
    }
}
