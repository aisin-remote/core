<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sub_sections', function (Blueprint $table) {
            $table->unsignedBigInteger('short_term')->nullable()->after('leader_id');
            $table->unsignedBigInteger('mid_term')->nullable()->after('short_term');
            $table->unsignedBigInteger('long_term')->nullable()->after('mid_term');
            $table->foreign('short_term')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('mid_term')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('long_term')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sub_sections', function (Blueprint $table) {
            //
        });
    }
};
