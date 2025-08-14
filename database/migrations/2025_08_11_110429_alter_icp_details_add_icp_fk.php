<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('icp_details', function (Blueprint $table) {
            $table->index('icp_id', 'icp_details_icp_id_idx');
        });

        Schema::table('icp_details', function (Blueprint $table) {
            $table->foreign('icp_id', 'icp_details_icp_id_fk')
                ->references('id')
                ->on('icp')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('icp_details', function (Blueprint $table) {
            $table->dropForeign('icp_details_icp_id_fk');
            $table->dropIndex('icp_details_icp_id_idx');
        });
    }
};
