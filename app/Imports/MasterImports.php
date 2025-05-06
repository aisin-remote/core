<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\EmployeeImport;
use App\Imports\EducationBackground;
use App\Imports\WorkingExperience;
use App\Imports\PerformanceAppraisal;
use App\Imports\HavQuadrans;

class MasterImports implements WithMultipleSheets
{
    public function sheets(): array
    {

        return [

            'Employee' => new EmployeeImport(),
            'Education Background' => new EducationBackground(),
            'Working Experience' => new WorkingExperience(),
            'Performance Appraisal History' => new PerformanceAppraisal(),
            'Astra Training' => new AstraTrainings(),
            'Assessment' => new HavQuadrans(),
            'External Training' => new ExternalTrainings(),
            'Promotion History' => new PromotionHistorys(),

        ];
    }
}
