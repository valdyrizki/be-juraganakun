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
        Schema::create('journal_transactions', function (Blueprint $table) {
            $table->id();
            $table->string("txid",12);
            $table->string("journal_account_id",3);
            $table->smallInteger("dbcr");
            $table->integer('amount');
            $table->text('description')->nullable();
            $table->tinyInteger("status")->default(0)->comment("0 = Nonaktif, 1 = Aktif");
            $table->integer("user_create")->nullable();
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
        Schema::dropIfExists('journal_transactions');
    }
};
