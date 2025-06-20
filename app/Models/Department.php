<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function employees()
{
    return $this->belongsToMany(
        Employee::class,
        'employee_departments',   // ← nama pivot table yang sama
        'department_id',          // ← foreign key utk model Department di pivot
        'employee_id'             // ← foreign key utk model Employee di pivot
    )->withTimestamps();
}


    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function short()
    {
        return $this->belongsTo(Employee::class, 'short_term');
    }
    public function mid()
    {
        return $this->belongsTo(Employee::class, 'mid_term');
    }
    public function long()
    {
        return $this->belongsTo(Employee::class, 'long_term');
    }
}
