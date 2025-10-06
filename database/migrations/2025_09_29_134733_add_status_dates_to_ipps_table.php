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
        Schema::table('ipps', function (Blueprint $table) {
            // gunakan timestampTz jika DB Anda pakai timezone; jika tidak, ganti ke ->timestamp()
            $table->timestampTz('submitted_at')->default(null)->nullable()->after('status');
            $table->timestampTz('checked_at')->default(null)->nullable()->after('submitted_at');
            $table->timestampTz('approved_at')->default(null)->nullable()->after('checked_at');

            // opsional: index untuk query cepat di listing/riwayat
            $table->index('submitted_at');
            $table->index('checked_at');
            $table->index('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ipps', function (Blueprint $table) {
            $table->dropIndex(['submitted_at']);
            $table->dropIndex(['checked_at']);
            $table->dropIndex(['approved_at']);

            $table->dropColumn(['submitted_at', 'checked_at', 'approved_at']);
        });
    }
};
