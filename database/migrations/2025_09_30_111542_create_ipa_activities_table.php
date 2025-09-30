<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ipa_activities', function (Blueprint $table) {
            $table->id();

            // Header
            $table->foreignId('ipa_id')
                ->constrained('ipa_headers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Sumber baris: dari IPP atau custom
            $table->enum('source', ['from_ipp', 'custom'])->default('from_ipp')->index();

            // Relasi ke point IPP jika from_ipp
            $table->foreignId('ipp_point_id')
                ->nullable()
                ->constrained('ipp_points')
                ->nullOnDelete();

            // Kategori (selaraskan dengan kategori IPP)
            $table->string('category', 100)->nullable()->index();

            // Deskripsi aktivitas/target
            $table->text('description');

            // Bobot & skor (gunakan skala dari "Skala Penilaian")
            $table->decimal('weight', 6, 2)->default(0);      // contoh: 0–100 (atau 0–1 jika pakai proporsi)
            $table->decimal('self_score', 6, 2)->default(0);  // contoh: 1–5

            // Nilai hasil (weight × self_score) – disimpan untuk rekap cepat
            $table->decimal('calc_score', 10, 2)->default(0)->index();

            // Bukti/link/file (opsional)
            $table->text('evidence')->nullable();

            // Posisi urutan (opsional)
            $table->unsignedSmallInteger('position')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Optimasi pencarian
            $table->index(['ipa_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_activities');
    }
};
