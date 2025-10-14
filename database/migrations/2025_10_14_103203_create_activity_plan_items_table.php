<?php
// database/migrations/2025_10_14_000003_create_activity_plan_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_plan_id')->constrained('activity_plans')->cascadeOnDelete();
            $table->foreignId('ipp_point_id')->constrained('ipp_points')->cascadeOnDelete();

            $table->string('kind_of_activity');
            $table->text('target')->nullable();

            $table->foreignId('pic_employee_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->unsignedSmallInteger('schedule_mask')->default(0);
            $table->enum('frequency', ['once', 'yearly'])->default('once');

            $table->string('cached_category')->nullable();
            $table->string('cached_activity')->nullable();
            $table->date('cached_start_date')->nullable();
            $table->date('cached_due_date')->nullable();

            $table->text('impact_note')->nullable();
            $table->decimal('cr_value', 14, 2)->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['ipp_point_id', 'pic_employee_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('activity_plan_items');
    }
};
