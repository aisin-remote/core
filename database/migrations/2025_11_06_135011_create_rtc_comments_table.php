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
        Schema::create('rtc_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtc_id')->constrained('rtc')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('status_from', 30)->nullable();
            $table->string('status_to', 30)->nullable();
            $table->text('comment');
            $table->timestamps();
            $table->index(['rtc_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rtc_comments');
    }
};
