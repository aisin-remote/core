<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ipa_achievements', function (Blueprint $table) {
            // relasi ke ipp_points (nullable, jika IPP point dihapus -> set null)
            $table->unsignedBigInteger('ipp_point_id')->nullable()->after('ipa_id');
            $table->text('one_year_target')->nullable()->after('description');
            $table->text('one_year_achievement')->nullable()->after('one_year_target');

            $table->index('ipp_point_id', 'idx_ipa_ach_ipp_point_id');
            $table->foreign('ipp_point_id')->references('id')->on('ipp_points')->nullOnDelete();
        });

        // Backfill: pindahkan description lama ke one_year_achievement jika kosong
        DB::table('ipa_achievements')
            ->whereNull('one_year_achievement')
            ->update(['one_year_achievement' => DB::raw('description')]);
    }

    public function down(): void
    {
        Schema::table('ipa_achievements', function (Blueprint $table) {
            $table->dropForeign(['ipp_point_id']);
            $table->dropIndex('idx_ipa_ach_ipp_point_id');
            $table->dropColumn(['ipp_point_id', 'one_year_target', 'one_year_achievement']);
        });
    }
};
