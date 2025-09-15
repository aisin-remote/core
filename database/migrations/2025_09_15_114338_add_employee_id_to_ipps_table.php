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
            $table->foreignId('employee_id')
                ->nullable()
                ->after('id')
                ->constrained('employees')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['employee_id', 'on_year'], 'ipps_emp_year_idx');
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
            if (Schema::hasColumn('ipps', 'employee_id')) {
                $table->dropIndex('ipps_emp_year_idx');
                $table->dropConstrainedForeignId('employee_id');
            }
        });
    }
};
