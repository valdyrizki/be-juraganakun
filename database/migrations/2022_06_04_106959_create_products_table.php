<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string("product_id",4)->primary();
            $table->string("product_name",50);
            $table->integer("stock")->default(0);
            $table->integer("cogs")->comment("HPP");
            $table->integer("price");
            $table->text("description")->nullable();
            $table->tinyInteger("status")->default(1)->comment("0 = Ditangguhkan, 1 = Aktif, 9 = Nonaktif");
            $table->string("distributor",100)->nullable();
            $table->char("category_id",'2'); 
            $table->integer('user_create')->nullable();
            $table->integer('user_update')->nullable();
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
        Schema::dropIfExists('products');
    }
};
