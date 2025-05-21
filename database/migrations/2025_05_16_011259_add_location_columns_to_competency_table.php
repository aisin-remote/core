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
        Schema::table('competency', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_section_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('section_id')->nullable()->after('sub_section_id');
            $table->unsignedBigInteger('division_id')->nullable()->after('section_id');
            $table->unsignedBigInteger('plant_id')->nullable()->after('division_id');
            $table->foreign('sub_section_id')
                  ->references('id')->on('sub_sections')
                  ->onDelete('cascade');

            $table->foreign('section_id')
                  ->references('id')->on('sections')
                  ->onDelete('cascade');

            $table->foreign('division_id')
                  ->references('id')->on('divisions')
                  ->onDelete('cascade');

            $table->foreign('plant_id')
                  ->references('id')->on('plants')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('competency', function (Blueprint $table) {
            $table->dropForeign(['sub_section_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['division_id']);
            $table->dropForeign(['plant_id']);
            $table->dropColumn([
                'sub_section_id',
                'section_id',
                'division_id',
                'plant_id',
            ]);
        });
    }
};
