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
        Schema::create('checksheet', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('competency_id');
            $table->foreign('competency_id')
                ->references('id')
                ->on('competency')
                ->onDelete('cascade');
            $table->string('name');
            $table->enum('position', ['GM','Manager','Coordinator','Section Head','Supervisor','Act Leader','Act JP','Operator','Leader','JP']);
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
        Schema::dropIfExists('checksheet');
    }
};