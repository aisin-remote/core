<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('performance_appraisal_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
<<<<<<< HEAD:database/migrations/2025_03_14_043520_create_performance_appraisal_histories_table.php
            $table->bigInteger('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('score')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('date')->nullable();
=======
            $table->bigInteger('assessment_id')->unsigned();
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->bigInteger('alc_id')->unsigned();
            $table->foreign('alc_id')->references('id')->on('alc')->onDelete('cascade');
            $table->string('score', 2);
            $table->string('strength', 255);
            $table->string('weakness', 255);
>>>>>>> origin/IDP:database/migrations/2025_03_13_023501_create_detail_assessments_table.php
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
        Schema::dropIfExists('performance_appraisal_histories');
    }
};
