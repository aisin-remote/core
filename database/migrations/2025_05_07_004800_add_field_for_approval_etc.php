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
        Schema::table('havs', function (Blueprint $table) {
            $table->integer('status')->after('quadrant')->nullable()->comment('0 = Create, 1 = Revise, 2 = Approve');
            $table->string('year')->after('status')->nullable();
            $table->string('npk_approve')->after('year')->nullable();
            $table->timestamp('approved_at')->after('npk_approve')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('havs', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('year');
            $table->dropColumn('npk_approve');
            $table->dropColumn('approved_at');
        });
    }
};
