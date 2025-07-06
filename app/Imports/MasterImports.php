<?php

namespace App\Imports;

use App\Models\HavQuadrant;
use App\Imports\HavQuadrans;
use App\Imports\EmployeeImport;
use App\Imports\WorkingExperience;
use App\Imports\EducationBackground;
use App\Imports\PerformanceAppraisal;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterImports implements WithMultipleSheets
{
    protected $filePath, $havId;

    public function __construct($filePath, $havId = null)
    {
        $this->filePath = $filePath;
        $this->havId = $havId;
    }

    public function sheets(): array
    {

        return [

            'Employee' => new EmployeeImport(),
            // 'Education Background' => new EducationBackground(),
            // 'Working Experience' => new WorkingExperience(),
            // 'Performance Appraisal History' => new PerformanceAppraisal(),
            // 'Astra Training' => new AstraTrainings(),
            // 'Assessment' => new HavQuadrant(),
            // 'Assessment' => new HavQuadrans($this->filePath, $this->havId)
            // 'External Training' => new ExternalTrainings(),
            // 'Promotion History' => new PromotionHistorys(),

        ];
    }
}
