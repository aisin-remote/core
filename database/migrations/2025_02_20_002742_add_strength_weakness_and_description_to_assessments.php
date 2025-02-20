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
        $table->string('strength_weakness', 255)->after('upload'); // Tambah kolom strength_weakness
        $table->text('description')->nullable()->after('strength_weakness'); // Tambah kolom description
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
