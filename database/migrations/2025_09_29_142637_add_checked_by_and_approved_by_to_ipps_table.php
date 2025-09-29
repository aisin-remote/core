<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ipps', function (Blueprint $table) {
            // kolom user/employee yang melakukan action
            $table->unsignedBigInteger('checked_by')->nullable()->after('checked_at');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');

            // index untuk performa
            $table->index('checked_by');
            $table->index('approved_by');

            // jika mau foreign key constraint
            $table->foreign('checked_by')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ipps', function (Blueprint $table) {
            $table->dropForeign(['checked_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['checked_by', 'approved_by']);
        });
    }
};
