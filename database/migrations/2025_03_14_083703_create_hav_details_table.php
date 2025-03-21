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
        Schema::create('hav_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('hav_id')->unsigned();
            $table->foreign('hav_id')->references('id')->on('havs')->onDelete('cascade');
            $table->bigInteger('alc_id')->unsigned();
            $table->foreign('alc_id')->references('id')->on('alc')->onDelete('cascade');
            $table->decimal('score', 3, 1);
            $table->string('evidence', 500);
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
        Schema::dropIfExists('hav_details');
    }
};
