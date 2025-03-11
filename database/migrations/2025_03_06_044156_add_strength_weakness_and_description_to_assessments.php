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
    Schema::table('assessments', function (Blueprint $table) {
        if (!Schema::hasColumn('assessments', 'strength_weakness')) {
            $table->string('strength_weakness')->after('upload');
        }

        if (!Schema::hasColumn('assessments', 'description')) {
            $table->text('description')->nullable()->after('strength_weakness');
        }
    });
}



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    Schema::table('assessments', function (Blueprint $table) {
        $table->dropColumn(['strength_weakness', 'description']);
    });
}
};
