<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `employees`
            MODIFY `position` ENUM(
            'President',
            'VPD',
            'Direktur',
            'Act Director',
            'Act GM',
            'GM',
            'Act Manager',
            'Manager',
            'Coordinator',
            'Section Head',
            'Act Section Head',
            'Act Supervisor',
            'Supervisor',
            'Act Leader',
            'Leader',
            'Staff',
            'Act JP',
            'JP',
            'Operator'
            ) NULL
        ");
    }

    public function down(): void
    {
        // Kembalikan ke versi ENUM lama
        DB::statement("
            ALTER TABLE `employees`
            MODIFY `position` ENUM(
            'President',
            'VPD',
            'Direktur',
            'Act GM',
            'GM',
            'Act Manager',
            'Manager',
            'Coordinator',
            'Section Head',
            'Act Section Head',
            'Act Supervisor',
            'Supervisor',
            'Act Leader',
            'Leader',
            'Staff',
            'Act JP',
            'JP',
            'Operator'
            ) NULL
        ");
    }
};
