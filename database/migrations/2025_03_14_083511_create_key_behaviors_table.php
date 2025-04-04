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
        Schema::create('key_behaviors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('alc_id')->unsigned();
            $table->foreign('alc_id')->references('id')->on('alc')->onDelete('cascade');
            $table->string('description', 500);
            $table->integer('max_score');
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
        Schema::dropIfExists('key_behaviors');
    }
};
