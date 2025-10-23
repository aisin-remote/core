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
        Schema::table('activity_plan_items', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft')->after('cr_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_plan_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
