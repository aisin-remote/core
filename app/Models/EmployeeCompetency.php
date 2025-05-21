<?php

namespace App\Models;

use App\Models\Employee;
use App\Models\Competency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCompetency extends Model
{
    use HasFactory;
    protected $table = 'employee_competency';
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class)->withDefault();
    }   
    
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'employee_departments');
    }
}
