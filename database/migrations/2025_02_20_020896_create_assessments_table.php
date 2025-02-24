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
        Schema::create('assessments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_id');
            $table->foreign('employee_id')->references('npk')->on('employees')->onDelete('cascade');
            $table->integer('alc_id');
            $table->foreign('alc_id')->references('id')->on('alc')->onDelete('cascade');
            $table->string('score', 2);
            $table->string('description', 2000);
            $table->date('date');
            $table->string('upload', 200);
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
        Schema::dropIfExists('assessments');
    }
};
