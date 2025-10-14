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
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'checked', 'approved', 'revised'])->default('draft')->after('grading');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
