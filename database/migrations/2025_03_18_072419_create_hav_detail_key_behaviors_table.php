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
        Schema::create('hav_detail_key_behaviors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('hav_detail_id')->unsigned();
            $table->foreign('hav_detail_id')->references('id')->on('hav_details')->onDelete('cascade');
            $table->bigInteger('key_behavior_id')->unsigned();
            $table->foreign('key_behavior_id')->references('id')->on('key_behaviors')->onDelete('cascade');
            $table->decimal('score', 3, 1);
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
        Schema::dropIfExists('hav_detail_key_behaviors');
    }
};
