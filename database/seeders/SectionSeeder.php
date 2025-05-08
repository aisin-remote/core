<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Section::insert([
            ['name' => 'Section Marketing A', 'department_id' => 4, 'supervisor_id' => null],
            ['name' => 'Section Production A', 'department_id' => 5, 'supervisor_id' => null],
            ['name' => 'Section Sales A', 'department_id' => 6, 'supervisor_id' => null],
            ['name' => 'Section QA A', 'department_id' => 7, 'supervisor_id' => null],
            ['name' => 'Section R&D A', 'department_id' => 8, 'supervisor_id' => null],
            ['name' => 'Section PRO EC A', 'department_id' => 9, 'supervisor_id' => null],
            ['name' => 'Section PRO EC-ASA-ASA A', 'department_id' => 10, 'supervisor_id' => null],
            ['name' => 'Section PRO EC-ASA A', 'department_id' => 11, 'supervisor_id' => null],
            ['name' => 'Section PRO EC-ECO-ECO A', 'department_id' => 12, 'supervisor_id' => null],
            ['name' => 'Section MMA A', 'department_id' => 13, 'supervisor_id' => null],
            ['name' => 'Section Prod & Eng A', 'department_id' => 15, 'supervisor_id' => null],
            ['name' => 'Section Maintenance A', 'department_id' => 16, 'supervisor_id' => null],
            ['name' => 'Section MIS A', 'department_id' => 17, 'supervisor_id' => null],
            ['name' => 'Section BRP A', 'department_id' => 18, 'supervisor_id' => null],
            ['name' => 'Section PPIC A', 'department_id' => 19, 'supervisor_id' => null],
        ]);
    }
}
