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
        Schema::create('transactions', function (Blueprint $table) {
            $table->string("invoice_id",15)->primary();
            $table->foreignId("user_id");
            $table->integer("total_price");
            $table->integer("unique_number");
            $table->integer("discount")->default(0);
            $table->string("coupon",50)->nullable();
            $table->text("description")->nullable();
            $table->string("client_name",50)->nullable();
            $table->string("phone_number",20)->nullable();
            $table->string("email",50)->nullable();
            $table->text("bank")->nullable();
            $table->text("invoice_merchant")->nullable();
            $table->tinyInteger("status")->default(1)->comment("0 = Belum confirm/Active, 1 = Confirm/Done, 2 = Refund, 3 = Expired, 9 = Batal");
            $table->tinyInteger("eod_id")->default(0);
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
        Schema::dropIfExists('transactions');
    }
};
