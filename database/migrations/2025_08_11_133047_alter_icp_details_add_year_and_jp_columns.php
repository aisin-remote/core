<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('icp_details', function (Blueprint $table) {
            $table->year('plan_year')->after('icp_id');
            $table->string('job_function', 100)->after('plan_year');
            $table->string('position', 50)->after('job_function');
            $table->string('level', 30)->after('position');

            $table->index(['icp_id', 'plan_year'], 'icp_details_icp_year_idx');
        });
    }

    public function down(): void
    {
        Schema::table('icp_details', function (Blueprint $table) {
            $table->dropIndex('icp_details_icp_year_idx');
            $table->dropColumn(['plan_year', 'job_function', 'position', 'level']);
        });
    }
};
