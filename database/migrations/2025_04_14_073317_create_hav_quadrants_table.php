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
        Schema::create('hav_quadrants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('employee_id')->unsigned();
            $table->string('assessment_score')->nullable();
            $table->string('performance_score')->nullable();
            $table->integer('quadrant')->default(0)->comment('1: Star, 2: Future Star, 3: Future Star, 4: Potential Candidate, 5: Raw Diamond, 6: Candidate, 7: Top Performer, 8: Strong Performer, 9: Career Person, 10: Most Unfit Employee, 11: Unfit Employee, 12: Problem Employee, 13: Maximal Contribution, 14: Contribution, 15: Minimal Contribution, 16: Dead Wood');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
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
        Schema::dropIfExists('hav_quadrants');
    }
};
