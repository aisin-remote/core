<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('icp', function (Blueprint $table) {
            $table->index('employee_id', 'icp_employee_id');

            $table->foreign('employee_id', 'icp_employee_id_fk')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('icp', function (Blueprint $table) {
            $table->dropForeign('icp_employee_id_fk');
            $table->dropIndex('icp_employee_id_idx');
        });
    }
};
