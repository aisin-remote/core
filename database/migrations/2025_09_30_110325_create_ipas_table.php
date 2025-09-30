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
        Schema::create('ipa_headers', function (Blueprint $table) {
            $table->id();
            // Owner dokumen IPA
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Link ke IPP terkait (tahun yang sama)
            $table->foreignId('ipp_id')
                ->constrained('ipps')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Tahun penilaian (YYYY)
            $table->string('on_year', 4)->index();

            // Catatan umum (opsional)
            $table->text('notes')->nullable();

            // (Opsional) Cache total untuk rekap cepat
            $table->decimal('activity_total', 10, 2)->nullable()->default(null);
            $table->decimal('achievement_total', 10, 2)->nullable()->default(null);
            $table->decimal('grand_total', 10, 2)->nullable()->default(null);

            // Auditor/pembuat
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Satu IPA per employee per tahun
            $table->unique(['employee_id', 'on_year'], 'uniq_employee_year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ipa_headers');
    }
};
