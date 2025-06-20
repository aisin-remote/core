<?php

namespace App\Models;

use App\Models\Department;
use App\Models\Employee;
use App\Models\GroupCompetency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competency extends Model
{
    use HasFactory;
    protected $table = 'competency';
    protected $guarded = ['id'];

    public function checkSheets()
    {
        return $this->hasMany(CheckSheet::class, 'competency_id', 'id');
    }

    public function employeeCompetencies()
    {
        return $this->hasMany(EmployeeCompetency::class, 'competency_id', 'id');
    }
    
    public function checksheetUsers()
    {
        return $this->hasMany(ChecksheetUser::class, 'competency_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function group_competency()
    {
        return $this->belongsTo(GroupCompetency::class, 'group_competency_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function sub_section()
    {
        return $this->belongsTo(SubSection::class, 'sub_section_id', 'id');
    }

    public function division()     
    { 
        return $this->belongsTo(Division::class, 'division_id', 'id'); 
    }

    public function plant()        
    {   
        return $this->belongsTo(Plant::class, 'plant_id', 'id'); 
    }
}
