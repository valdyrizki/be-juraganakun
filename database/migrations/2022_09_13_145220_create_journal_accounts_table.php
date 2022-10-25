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
        Schema::create('journal_accounts', function (Blueprint $table) {
            $table->string("id",3)->primary();
            $table->foreign("journal_category_id");
            $table->string("name",100);
            $table->integer("balance")->default(0);
            $table->tinyInteger("status")->default(0)->comment("0 = Nonaktif, 1 = Aktif");
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
        Schema::dropIfExists('journal_accounts');
    }
};
