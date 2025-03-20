<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('idp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('assessment_id')->unsigned();
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->bigInteger('alc_id')->unsigned();
            $table->foreign('alc_id')->references('id')->on('alc')->onDelete('cascade');
            $table->string('category');
            $table->text('development_program');
            $table->text('development_target');
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('idp');
    }
};
