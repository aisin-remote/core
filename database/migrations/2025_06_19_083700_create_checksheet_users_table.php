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
        Schema::create('checksheet_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('competency_id');
            $table->foreign('competency_id')
                ->references('id')
                ->on('competency')
                ->onDelete('cascade');
            $table->string('name');
            $table->enum('position', ['GM','Manager','Coordinator','Section Head','Supervisor','Act Leader','Act JP','Operator','Leader','JP']);
            $table->unsignedBigInteger('sub_section_id');
            $table->foreign('sub_section_id')
                ->references('id')
                ->on('sub_sections')
                ->onDelete('cascade');
            $table->unsignedBigInteger('section_id');
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade');
            $table->unsignedBigInteger('division_id');
            $table->foreign('division_id')
                ->references('id')
                ->on('divisions')
                ->onDelete('cascade');
            $table->unsignedBigInteger('plant_id');
            $table->foreign('plant_id')
                ->references('id')
                ->on('plants')
                ->onDelete('cascade');
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
        Schema::dropIfExists('checksheet_users');
    }
};
