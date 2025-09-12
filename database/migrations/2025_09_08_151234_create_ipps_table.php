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
        Schema::create('ipps', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('department')->nullable();
            $table->string('division')->nullable();
            $table->string('section')->nullable();
            $table->date('date_review')->nullable();
            $table->string('pic_review')->nullable();
            $table->string('on_year', 10)->nullable();
            $table->string('no_form')->nullable();

            $table->enum('status', ['draft', 'submitted', 'checked', 'approved', 'revised'])->default('draft');
            $table->json('summary')->nullable();
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
        Schema::dropIfExists('ipps');
    }
};
