<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipps', function (Blueprint $table) {
            // Paksa InnoDB agar FK didukung di MariaDB lama
            $table->engine = 'InnoDB';

            $table->id();

            // relasi ke employees (nullable dulu supaya aman di urutan migrate)
            $table->unsignedBigInteger('employee_id')->nullable()->index();

            $table->string('nama')->nullable();
            $table->string('department')->nullable();
            $table->string('division')->nullable();
            $table->string('section')->nullable();
            $table->date('date_review')->nullable();
            $table->string('pic_review')->nullable();

            // tahun disimpan pendek supaya index aman di MariaDB lama
            $table->string('on_year', 10)->nullable()->index();
            $table->string('no_form')->nullable();

            // ENUM aman di MariaDB lama
            $table->enum('status', ['draft', 'submitted', 'checked', 'approved', 'revised'])->default('draft');

            // summary: ganti JSON -> LONGTEXT
            // (tanpa CHECK JSON_VALID karena MariaDB lama sering mengabaikannya)
            $table->longText('summary')->nullable();

            $table->timestamps();

            // Unique kombinasi (employee_id, on_year) agar 1 IPP per karyawan per tahun
            $table->unique(['employee_id', 'on_year'], 'ipps_emp_year_idx');
        });

        // Tambah FK terpisah; jika tabel employees belum ada saat ini,
        // biarkan nullable + index sederhana—FK bisa ditambahkan di migration berikutnya.
        Schema::table('ipps', function (Blueprint $table) {
            if (Schema::hasTable('employees')) {
                // beri nama eksplisit supaya mudah di-rollback di server lama
                $table->foreign('employee_id', 'fk_ipps_employee_id')
                    ->references('id')->on('employees')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        // Drop FK kalau ada (nama eksplisit & default Laravel)
        if (Schema::hasTable('ipps')) {
            try {
                Schema::table('ipps', function (Blueprint $table) {
                    // dua-duanya dicoba agar aman di berbagai server
                    try {
                        $table->dropForeign('fk_ipps_employee_id');
                    } catch (\Throwable $e) {
                    }
                    try {
                        $table->dropForeign('ipps_employee_id_foreign');
                    } catch (\Throwable $e) {
                    }
                    try {
                        $table->dropUnique('ipps_emp_year_idx');
                    } catch (\Throwable $e) {
                    }
                });
            } catch (\Throwable $e) {
                // abaikan; akan di-drop bersama tabel
            }
        }

        Schema::dropIfExists('ipps');
    }
};
