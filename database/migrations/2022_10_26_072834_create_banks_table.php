<?php

use App\Models\Bank;
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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name',30);
            $table->string('accnbr',20);
            $table->decimal('balance')->default(0);
            $table->text('url_logo')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger("status")->default(1)->comment("0 = Nonaktif, 1 = Aktif");
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
        Schema::dropIfExists('banks');
    }
};
