<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('icp_snapshots', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel icp
            $table->foreignId('icp_id')->constrained('icp')->cascadeOnDelete();

            $table->unsignedSmallInteger('plan_year')->nullable();
            $table->string('reason')->nullable();

            // Ganti tipe JSON menjadi longText agar kompatibel
            $table->longText('icp')->nullable();
            $table->longText('details')->nullable();
            $table->longText('steps')->nullable();

            // Relasi ke tabel employees
            $table->foreignId('created_by')->nullable()->constrained('employees');

            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('icp_snapshots');
    }
};
