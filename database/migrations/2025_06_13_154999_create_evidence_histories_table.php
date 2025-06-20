<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_competency_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('employee_competency_id')
                  ->references('id')
                  ->on('employee_competency')
                  ->cascadeOnDelete();  
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
            $table->enum('action', ['approve', 'unapprove']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('evidence_histories', function (Blueprint $table) {
            $table->dropForeign(['employee_competency_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('evidence_histories');
    }
};
