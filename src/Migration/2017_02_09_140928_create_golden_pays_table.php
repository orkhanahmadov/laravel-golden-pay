<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoldenPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('golden_pays', function (Blueprint $table) {
            $table->increments('id');
            $table->string("card_type", 1);
            $table->string("payment_key", 36);
            $table->integer("payment_status_code")->default(0);
            $table->string("payment_status_message")->nullable();
            $table->timestamp("payment_date")->nullable();
            $table->string("card_number")->nullable();
            $table->string("reference_number")->nullable();
            $table->integer("check_count")->default(0);
            $table->string("language", 2);
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
        Schema::dropIfExists('golden_pays');
    }
}
