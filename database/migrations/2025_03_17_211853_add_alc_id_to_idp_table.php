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
        Schema::table('idp', function (Blueprint $table) {
            $table->bigInteger('alc_id')->unsigned()->after('id');
            $table->foreign('alc_id')->references('id')->on('alc')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('idp', function (Blueprint $table) {
            $table->dropForeign(['alc_id']);
            $table->dropColumn('alc_id');
        });
    }
};
