<?php
// database/migrations/2025_12_01_000000_create_idp_approvals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idp_approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idp_id')
                ->constrained('idp')
                ->cascadeOnDelete();

            $table->foreignId('assessment_id')
                ->constrained('assessments')
                ->cascadeOnDelete();

            $table->foreignId('approve_by')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('level');

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['idp_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idp_approvals');
    }
};
