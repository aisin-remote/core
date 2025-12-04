<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtc_approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rtc_id')
                ->constrained('rtc')
                ->cascadeOnDelete();

            $table->foreignId('approve_by')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('level');

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['rtc_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtc_approvals');
    }
};
