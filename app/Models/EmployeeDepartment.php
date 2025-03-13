<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeDepartment extends Pivot
{
    use HasFactory;

    protected $table = 'employee_departments';

    public function departments()
    {
        return $this->belongsTo(Department::class, 'department_id'); // Sesuaikan dengan foreign key
    }
}
