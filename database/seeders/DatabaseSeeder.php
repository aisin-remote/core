<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks to avoid conflicts
        // $data = [
        //     ['id' => 1, 'name' => 'Vision & Business Sense', 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 2, 'name' => 'Customer Focus', 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 3, 'name' => 'Interpersonal Skill', 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 4, 'name' => 'Analysis & Judgment', 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 5, 'name' => 'Planning & Driving Action', 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 6, 'name' => 'Leading & Motivating', 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 7, 'name' => 'Teamwork', 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 8, 'name' => 'Drive & Courage', 'created_at' => now(), 'updated_at' => now()],
        // ];

        // DB::table('alc')->insert($data);
        $this->call([
            PlantSeeder::class,
            DivisionSeeder::class,
            DepartmentSeeder::class,
            SectionSeeder::class,
            SubSectionSeeder::class,
        ]);
    }
}
