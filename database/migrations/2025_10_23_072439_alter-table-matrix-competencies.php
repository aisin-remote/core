<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('matrix_competencies', function (Blueprint $table) {
            // add dept_id referencing departments
            $table->foreignId('dept_id')
                ->nullable()
                ->after('id')
                ->constrained('departments')
                ->cascadeOnDelete();

            // add divs_id referencing divisions
            $table->foreignId('divs_id')
                ->nullable()
                ->after('dept_id')
                ->constrained('divisions')
                ->cascadeOnDelete();

            // timestamps
            $table->timestamps();

            // index
            $table->index(['dept_id', 'divs_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matrix_competencies', function (Blueprint $table) {
            // drop foreign keys first
            $table->dropForeign(['dept_id']);
            $table->dropForeign(['divs_id']);

            // drop index
            $table->dropIndex(['dept_id', 'divs_id']);

            // drop columns
            $table->dropColumn(['dept_id', 'divs_id', 'created_at', 'updated_at']);
        });
    }
};
