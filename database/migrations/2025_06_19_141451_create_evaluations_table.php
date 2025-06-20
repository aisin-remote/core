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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_competency_id');
            $table->foreign('employee_competency_id')
                ->references('id')
                ->on('employee_competency')
                ->onDelete('cascade');
            $table->unsignedBigInteger('checksheet_user_id');
            $table->foreign('checksheet_user_id')
                ->references('id')
                ->on('checksheet_users')
                ->onDelete('cascade');
            $table->string('answer')->nullable();
            $table->string('file')->nullable();
            $table->integer('score')->nullable();
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
        Schema::dropIfExists('evaluations');
    }
};
