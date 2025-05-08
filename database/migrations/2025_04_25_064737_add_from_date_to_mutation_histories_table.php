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
        Schema::table('mutation_histories', function (Blueprint $table) {
            $table->integer('duration_in_previous_structure')->after('to_id')->nullable(); // dalam bulan
            $table->string('duration_text')->after('duration_in_previous_structure')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('date_to_mutation_histories', function (Blueprint $table) {
            //
        });
    }
};
