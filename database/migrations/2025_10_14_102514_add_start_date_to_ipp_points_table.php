<?php
// database/migrations/2025_10_14_000001_add_start_date_to_ipp_points_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ipp_points', function (Blueprint $table) {
            if (!Schema::hasColumn('ipp_points', 'start_date')) {
                $table->date('start_date')->nullable()->after('status');
                $table->index('start_date');
            }
        });
    }
    public function down(): void
    {
        Schema::table('ipp_points', function (Blueprint $table) {
            if (Schema::hasColumn('ipp_points', 'start_date')) {
                $table->dropIndex(['start_date']);
                $table->dropColumn('start_date');
            }
        });
    }
};
