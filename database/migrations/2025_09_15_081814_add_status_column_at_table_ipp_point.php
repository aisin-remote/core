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
        Schema::table('ipp_points', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'checked', 'approved', 'revised'])->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ipp_points', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
