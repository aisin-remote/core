<?php

namespace Database\Seeders;

use App\Models\SubSection;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SubSection::insert([
            ['name' => 'Sub Marketing A1', 'section_id' => 1, 'leader_id' => null],
            ['name' => 'Sub Production A1', 'section_id' => 2, 'leader_id' => null],
            ['name' => 'Sub Sales A1', 'section_id' => 3, 'leader_id' => null],
            ['name' => 'Sub QA A1', 'section_id' => 4, 'leader_id' => null],
            ['name' => 'Sub R&D A1', 'section_id' => 5, 'leader_id' => null],
            ['name' => 'Sub PRO EC A1', 'section_id' => 6, 'leader_id' => null],
            ['name' => 'Sub PRO EC-ASA-ASA A1', 'section_id' => 7, 'leader_id' => null],
            ['name' => 'Sub PRO EC-ASA A1', 'section_id' => 8, 'leader_id' => null],
            ['name' => 'Sub PRO EC-ECO-ECO A1', 'section_id' => 9, 'leader_id' => null],
            ['name' => 'Sub MMA A1', 'section_id' => 10, 'leader_id' => null],
            ['name' => 'Sub Prod & Eng A1', 'section_id' => 11, 'leader_id' => null],
            ['name' => 'Sub Maintenance A1', 'section_id' => 12, 'leader_id' => null],
            ['name' => 'Sub MIS A1', 'section_id' => 13, 'leader_id' => null],
            ['name' => 'Sub BRP A1', 'section_id' => 14, 'leader_id' => null],
            ['name' => 'Sub PPIC A1', 'section_id' => 15, 'leader_id' => null],
        ]);
    }
}
