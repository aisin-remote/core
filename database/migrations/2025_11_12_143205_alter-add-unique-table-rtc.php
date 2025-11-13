<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtc', function (Blueprint $table) {
            $table->unique(['area', 'area_id', 'term'], 'rtc_area_areaid_term_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rtc', function (Blueprint $table) {
            $table->dropUnique('rtc_area_areaid_term_unique');
        });
    }
};
