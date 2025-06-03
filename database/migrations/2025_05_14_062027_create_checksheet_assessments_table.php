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
        Schema::create('checksheet_assessments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('checksheet_id');
            $table->unsignedBigInteger('employee_competency_id');
            $table->foreign('checksheet_id')
                ->references('id')
                ->on('checksheet')
                ->onDelete('cascade');
            $table->foreign('employee_competency_id')
                ->references('id')
                ->on('employee_competency')
                ->onDelete('cascade');
            $table->integer('score');
            $table->string('description');
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
        Schema::dropIfExists('checksheet_assessments');
    }
};
