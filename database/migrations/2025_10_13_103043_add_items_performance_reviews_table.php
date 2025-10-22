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
        Schema::table('performance_reviews', function (Blueprint $table) {
            // Ganti tipe json menjadi text agar kompatibel dengan server lama
            $table->text('b1_items')->nullable()->after('b1_pdca_values');
            $table->text('b2_items')->nullable()->after('b2_people_mgmt');
        });
    }

    /**
     * Rollback migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropColumn(['b1_items', 'b2_items']);
        });
    }
};
