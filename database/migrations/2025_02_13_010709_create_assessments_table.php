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
            $table->string('employee_npk');
            $table->foreign('employee_npk')->references('npk')->on('employees')->onDelete('cascade');
            $table->date('date');
            $table->string('vision_business_sense', 2);
            $table->string('customer_focus', 2);
            $table->string('interpersonal_skil', 2);
            $table->string('analysis_judgment', 2);
            $table->string('planning_driving_action', 2);
            $table->string('leading_motivating', 2);
            $table->string('teamwork', 2);
            $table->string('drive_courage', 2);
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
