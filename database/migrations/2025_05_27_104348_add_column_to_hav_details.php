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
        Schema::table('hav_details', function (Blueprint $table) {
            $table->string('strength', 255)->after('evidence')->nullable();
            $table->string('weakness', 255)->after('strength')->nullable();
            $table->string('suggestion_development', 255)->after('weakness')->nullable();
            $table->tinyInteger('is_assessment')->after('suggestion_development')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hav_details', function (Blueprint $table) {
            //
        });
    }
};
