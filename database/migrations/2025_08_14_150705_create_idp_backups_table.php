<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idp_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idp_id')->constrained('idp')->cascadeOnDelete(); // idp utama
            $table->foreignId('assessment_id')->nullable()->constrained('assessments');
            $table->foreignId('alc_id')->nullable()->constrained('alc'); // relasi ke ALC
            $table->foreignId('hav_detail_id')->nullable()->constrained('hav_details');

            $table->string('category', 100);
            $table->string('development_program', 160);
            $table->longText('development_target')->nullable();
            $table->date('date')->nullable();
            $table->unsignedTinyInteger('status')->default(0);

            // metadata versioning
            $table->unsignedInteger('version'); // 1,2,3,...
            $table->foreignId('changed_by')->nullable()->constrained('users');
            $table->timestamp('changed_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idp_backups');
    }
};
