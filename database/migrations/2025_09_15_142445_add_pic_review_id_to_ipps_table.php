<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambah kolom + index
        if (!Schema::hasColumn('ipps', 'pic_review_id')) {
            Schema::table('ipps', function (Blueprint $table) {
                $table->unsignedBigInteger('pic_review_id')
                    ->nullable()
                    ->after('date_review');

                // index terpisah (nama pendek agar aman di MariaDB lama)
                $table->index('pic_review_id', 'ipps_pic_review_idx');
            });
        }

        // (Opsional) Tambah FK; kalau MariaDB/engine tidak support, biarkan lewat
        try {
            Schema::table('ipps', function (Blueprint $table) {
                $table->foreign('pic_review_id', 'fk_ipps_pic_review')
                    ->references('id')->on('employees')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // Abaikan jika server tidak mengizinkan FK (MariaDB lama atau engine bukan InnoDB)
        }
    }

    public function down(): void
    {
        // Drop FK dengan aman (abaikan error jika tidak ada)
        try {
            Schema::table('ipps', function (Blueprint $table) {
                $table->dropForeign('fk_ipps_pic_review');
            });
        } catch (\Throwable $e) {
        }

        // Drop index + kolom dengan aman
        if (Schema::hasColumn('ipps', 'pic_review_id')) {
            Schema::table('ipps', function (Blueprint $table) {
                try {
                    $table->dropIndex('ipps_pic_review_idx');
                } catch (\Throwable $e) {
                }
                $table->dropColumn('pic_review_id');
            });
        }
    }
};
