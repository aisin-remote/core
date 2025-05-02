<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Plant::insert([
            ['name' => 'Plant A', 'director_id' => null],
            ['name' => 'Plant B', 'director_id' => null],
        ]);
    }
}
