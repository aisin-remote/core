<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ipp_points', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });

        Schema::table('ipp_points', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('ipp_points', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });

        Schema::table('ipp_points', function (Blueprint $table) {
            $table->string('due_date', 7)->nullable();
        });
    }
};
