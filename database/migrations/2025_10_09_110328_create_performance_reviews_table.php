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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->enum('period', ['mid', 'one']);
            $table->foreignId('ipa_header_id')->nullable()->constrained('ipa_headers')->nullOnDelete();

            $table->decimal('result_percent', 6, 2)->nullable();
            $table->decimal('result_value', 4, 2)->nullable();

            $table->decimal('b1_pdca_values', 4, 2)->nullable();
            $table->decimal('b2_people_mgmt', 4, 2)->nullable();

            $table->decimal('weight_result', 4, 3)->default(0.50);
            $table->decimal('weight_b1', 4, 3)->default(0.35);
            $table->decimal('weight_b2', 4, 3)->default(0.15);

            $table->decimal('final_value', 4, 2)->nullable();
            $table->string('grading', 5)->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'year', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('performance_reviews');
    }
};
