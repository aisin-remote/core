<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Tambahkan 'Act Director' ke daftar ENUM
        DB::statement("
            ALTER TABLE `employees`
            MODIFY `position` ENUM(
                'President',
                'VPD',
                'Direktur',
                'Act Direktur',
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
        // 1) Bersihkan data yang tidak akan valid di ENUM lama.
        //    Minimal: map 'Act Director' ke nilai yang valid, atau NULL-kan.
        //    Pilih salah satu: gunakan 'Direktur' (mendekati) atau NULL.

        // Opsi A: mapping ke 'Direktur'
        DB::table('employees')
            ->where('position', 'Act Director')
            ->update(['position' => 'Direktur']);

        // (Opsional, lebih ketat) Jika ada nilai lain di luar daftar lama, NULL-kan:
        $allowedOld = [
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
            'Operator',
            null, // untuk baris yang memang null
            ''    // jaga-jaga bila ada string kosong
        ];

        DB::table('employees')
            ->whereNotIn('position', $allowedOld)
            ->update(['position' => null]);

        // 2) Kembalikan ENUM ke daftar lama (tanpa 'Act Director')
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
