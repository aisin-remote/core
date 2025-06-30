<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $table = 'evaluations';
    protected $guarded = ['id'];

    public function employeeCompetency()
    {
        return $this->belongsTo(EmployeeCompetency::class);
    }
    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }
    public function checksheet()
    {
        return $this->belongsTo(ChecksheetUser::class, 'checksheet_user_id', 'id');
    }
    public function checksheetUser()
    {
        return $this->belongsTo(ChecksheetUser::class);
    }
}