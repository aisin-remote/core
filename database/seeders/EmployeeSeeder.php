<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // upsert helper
        $mk = function (string $name, ?string $npk = null) {
            return Employee::updateOrCreate(
                ['npk' => $npk ?: strtoupper(preg_replace('/\s+/', '', substr($name, 0, 6)))],
                ['name' => $name]
            );
        };

        $mk('Rafie Afif Andika', 'RAF');
        $mk('Fabrian Abadi', 'FAB');
        $mk('Semua PIC', 'ALL');
    }
}
