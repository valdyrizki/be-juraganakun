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
        Schema::create('comment_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId("comment_id");
            $table->text("filename");
            $table->text("path");
            $table->text("description")->nullable();
            $table->tinyInteger("status")->default(0)->comment("0 = Nonaktif, 1 = Aktif");
            $table->text('client_name')->nullable(); //Default from Transaction.client_name 
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
        Schema::dropIfExists('comment_files');
    }
};
