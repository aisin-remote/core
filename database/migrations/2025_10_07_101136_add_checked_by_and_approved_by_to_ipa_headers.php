<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ipa_headers', function (Blueprint $table) {
            $table->timestampTz('submitted_at')->nullable()->after('created_at');
            $table->timestampTz('checked_at')->nullable()->after('submitted_at');
            $table->timestampTz('approved_at')->nullable()->after('checked_at');

            // beri nama index biar gampang di-drop pada down()
            $table->index('submitted_at', 'ipa_headers_submitted_at_idx');
            $table->index('checked_at',   'ipa_headers_checked_at_idx');
            $table->index('approved_at',  'ipa_headers_approved_at_idx');

            $table->unsignedBigInteger('checked_by')->nullable()->after('checked_at');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');

            $table->index('checked_by',  'ipa_headers_checked_by_idx');
            $table->index('approved_by', 'ipa_headers_approved_by_idx');

            // beri nama FK supaya down() mudah
            $table->foreign('checked_by',  'ipa_headers_checked_by_fk')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('approved_by', 'ipa_headers_approved_by_fk')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ipa_headers', function (Blueprint $table) {
            // drop FK dulu, baru index/column
            $table->dropForeign('ipa_headers_checked_by_fk');
            $table->dropForeign('ipa_headers_approved_by_fk');

            $table->dropIndex('ipa_headers_checked_by_idx');
            $table->dropIndex('ipa_headers_approved_by_idx');

            $table->dropIndex('ipa_headers_submitted_at_idx');
            $table->dropIndex('ipa_headers_checked_at_idx');
            $table->dropIndex('ipa_headers_approved_at_idx');

            $table->dropColumn(['submitted_at', 'checked_at', 'approved_at', 'checked_by', 'approved_by']);
        });
    }
};
