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
        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->char("product_id",4);
            $table->text("invoice_id")->nullable();
            $table->text("filename");
            $table->text("path");
            $table->text("description")->nullable();
            $table->tinyInteger("status")->default(0)->comment("0 = Belum Terjual, 1 = Terjual, 9 = Refund");
            $table->integer('user_create')->default(1); //Admin
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
        Schema::dropIfExists('product_files');
    }
};
