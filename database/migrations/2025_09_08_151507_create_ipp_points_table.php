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
        Schema::create('ipp_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipp_id')->constrained('ipps')->cascadeOnDelete();
            $table->string('category', 64);
            $table->text('activity')->nullable();
            $table->text('target_mid')->nullable();
            $table->text('target_one')->nullable();
            $table->string('due_date', 7)->nullable();
            $table->unsignedSmallInteger('weight')->default(0);
            $table->timestamps();
            $table->index(['ipp_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ipp_points');
    }
};
