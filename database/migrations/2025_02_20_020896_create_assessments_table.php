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
        Schema::create('assessments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('employee_id')->unsigned();
            $table->bigInteger('alc_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('alc_id')->references('id')->on('alc')->onDelete('cascade');
            $table->string('score', 2);
            $table->date('date');
            $table->string('upload', 200);
            $table->string('strength_weakness', 255); // Tambah kolom strength_weakness
            $table->text('description')->nullable(); // Tambah kolom description
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
