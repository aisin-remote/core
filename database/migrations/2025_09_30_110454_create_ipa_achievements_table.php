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
        Schema::create('ipa_achievements', function (Blueprint $table) {
            $table->id();

            // Header
            $table->foreignId('ipa_id')
                ->constrained('ipa_headers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // One Year Achievement
            $table->text('description')->nullable();

            // Bobot & skor
            $table->decimal('weight', 6, 2)->default(0);
            $table->decimal('self_score', 6, 2)->default(0);

            // Hasil (weight × self_score)
            $table->decimal('calc_score', 10, 2)->default(0)->index();

            // Bukti/link/file (opsional)
            $table->text('evidence')->nullable();

            // Posisi urutan (opsional)
            $table->unsignedSmallInteger('position')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ipa_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ipa_achievements');
    }
};
