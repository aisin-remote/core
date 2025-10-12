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
        Schema::create('ipp_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipp_id')->constrained('ipps')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('status_from', 30)->nullable();
            $table->string('status_to', 30)->nullable();
            $table->text('comment');
            $table->foreignId('parent_id')->nullable()->constrained('ipp_comments')->nullOnDelete();
            $table->timestamps();
            $table->index(['ipp_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ipp_comments');
    }
};
