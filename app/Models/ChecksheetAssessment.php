<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecksheetAssessment extends Model
{
    use HasFactory;
    protected $table = 'checksheet_assessment';
    protected $guarded = ['id'];

    public function employeeCompetency()
    {
        return $this->belongsTo(EmployeeCompetency::class);
    }

    public function checksheet()
    {
        return $this->belongsTo(Checksheet::class);
    }
}
