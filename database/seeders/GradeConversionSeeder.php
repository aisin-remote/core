<?php

namespace Database\Seeders;

use App\Models\GradeConversion;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GradeConversionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GradeConversion::create(['astra_grade' => '1A', 'aisin_grade' => '1A' ]);
        GradeConversion::create(['astra_grade' => '1B', 'aisin_grade' => '1B' ]);
        GradeConversion::create(['astra_grade' => '1C', 'aisin_grade' => '1C' ]);
        GradeConversion::create(['astra_grade' => '1D', 'aisin_grade' => '1D' ]);
        GradeConversion::create(['astra_grade' => '1E', 'aisin_grade' => '1E' ]);
        GradeConversion::create(['astra_grade' => '1F', 'aisin_grade' => '1F' ]);

        GradeConversion::create(['astra_grade' => '2A', 'aisin_grade' => '2A' ]);
        GradeConversion::create(['astra_grade' => '2B', 'aisin_grade' => '2B' ]);
        GradeConversion::create(['astra_grade' => '2C', 'aisin_grade' => '2C' ]);
        GradeConversion::create(['astra_grade' => '2D', 'aisin_grade' => '3A' ]);
        GradeConversion::create(['astra_grade' => '2E', 'aisin_grade' => '3B' ]);
        GradeConversion::create(['astra_grade' => '2F', 'aisin_grade' => '3C' ]);

        GradeConversion::create(['astra_grade' => '3A', 'aisin_grade' => '4A' ]);
        GradeConversion::create(['astra_grade' => '3B', 'aisin_grade' => '4B' ]);
        GradeConversion::create(['astra_grade' => '3C', 'aisin_grade' => '5A' ]);
        GradeConversion::create(['astra_grade' => '3D', 'aisin_grade' => '5B' ]);
        GradeConversion::create(['astra_grade' => '3E', 'aisin_grade' => '6A' ]);
        GradeConversion::create(['astra_grade' => '3F', 'aisin_grade' => '6B' ]);

        GradeConversion::create(['astra_grade' => '4A', 'aisin_grade' => '7A' ]);
        GradeConversion::create(['astra_grade' => '4B', 'aisin_grade' => '7B' ]);
        GradeConversion::create(['astra_grade' => '4C', 'aisin_grade' => '8A' ]);
        GradeConversion::create(['astra_grade' => '4C', 'aisin_grade' => '8B' ]);
        GradeConversion::create(['astra_grade' => '4D', 'aisin_grade' => '9A' ]);
        GradeConversion::create(['astra_grade' => '4D', 'aisin_grade' => '9B' ]);

        GradeConversion::create(['astra_grade' => '4E', 'aisin_grade' => '10A' ]);
        GradeConversion::create(['astra_grade' => '4F', 'aisin_grade' => '10B' ]);
        GradeConversion::create(['astra_grade' => '5A', 'aisin_grade' => '11A' ]);
        GradeConversion::create(['astra_grade' => '5B', 'aisin_grade' => '11B' ]);
        GradeConversion::create(['astra_grade' => '5C', 'aisin_grade' => '12A' ]);
        GradeConversion::create(['astra_grade' => '5D', 'aisin_grade' => '12B' ]);
        GradeConversion::create(['astra_grade' => '6A', 'aisin_grade' => '13A' ]);
        GradeConversion::create(['astra_grade' => '6B', 'aisin_grade' => '13B' ]);
        GradeConversion::create(['astra_grade' => '6C', 'aisin_grade' => '14A' ]);
        GradeConversion::create(['astra_grade' => '6D', 'aisin_grade' => '14C' ]);
        GradeConversion::create(['astra_grade' => '7A', 'aisin_grade' => '15A' ]);
        GradeConversion::create(['astra_grade' => '7B', 'aisin_grade' => '15B' ]);

    }
}
