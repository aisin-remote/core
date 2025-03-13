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
        Schema::create('detail_assessment', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_id')->unsigned();
            $table->foreign('assessment_id')->references('id')->on('assessment')->onDelete('cascade');
            $table->bigInteger('alc_id')->unsigned();
            $table->foreign('alc_id')->references('id')->on('alc')->onDelete('cascade');
            $table->string('score', 2);
            $table->string('strength', 255);
            $table->string('weakness', 255);
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
        Schema::dropIfExists('detail_assessment');
    }
};
