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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId("product_id");
            $table->string("invoice_id",15);
            $table->integer("price");
            $table->integer("qty");
            $table->text("description")->nullable();
            $table->tinyInteger("status")->default(1)->comment("0 = Belum confirm, 1 = Confirm, 9 = Batal");
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
        Schema::dropIfExists('transaction_details');
    }
};
