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
        Schema::table('idp', function (Blueprint $table) {
            $table->unsignedBigInteger('hav_detail_id')->nullable()->after('id');
            $table->foreign('hav_detail_id')
                ->references('id')->on('hav_details')
                ->onDelete('cascade')->onUpdate('cascade');
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
            //
        });
    }
};
