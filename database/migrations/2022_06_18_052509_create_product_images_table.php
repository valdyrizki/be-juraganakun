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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->string("product_id",4);
            $table->string("img_name",200);
            $table->text("path");
            $table->text("description")->nullable();
            $table->tinyInteger("status")->default(1)->comment("0 = Belum Aktif, 1 = Aktif, 9 = Nonaktif");
            $table->integer('user_create')->default(1); //Admin
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
        Schema::dropIfExists('product_images');
    }
};
