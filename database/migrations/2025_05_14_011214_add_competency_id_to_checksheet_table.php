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
        Schema::table('checksheet', function (Blueprint $table) {
            $table->unsignedBigInteger('competency_id')->after('id');
            $table->foreign('competency_id')->references('id')->on('competency');
        });        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checksheet', function (Blueprint $table) {
            $table->dropForeign(['competency_id']);
            $table->dropColumn('competency_id');
        });
    }
};
