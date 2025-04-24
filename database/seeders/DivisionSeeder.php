<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Division::insert([
            ['name' => 'Division 1 - A', 'plant_id' => 1, 'gm_id' => null],
            ['name' => 'Division 2 - A', 'plant_id' => 1, 'gm_id' => null],
            ['name' => 'Division 1 - B', 'plant_id' => 2, 'gm_id' => null],
        ]);
    }
}
