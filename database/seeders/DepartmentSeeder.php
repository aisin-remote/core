<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Division 1: Marketing, Sales
        DB::table('departments')->whereIn('id', [4, 6])->update(['division_id' => 1]);

        // Division 2: Production, Production & Engineering, Maintenance, PPIC
        DB::table('departments')->whereIn('id', [5, 15, 16, 19])->update(['division_id' => 2]);

        // Division 3: Quality Assurance, R&D
        DB::table('departments')->whereIn('id', [7, 8])->update(['division_id' => 3]);

        // Division 4: PRO EC and variations
        DB::table('departments')->whereIn('id', [9, 10, 11, 12])->update(['division_id' => 2]);

        // Division 5: MMA, MIS, BRP
        DB::table('departments')->whereIn('id', [13, 17, 18])->update(['division_id' => 2]);
    }
}
