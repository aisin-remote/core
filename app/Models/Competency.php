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
}
