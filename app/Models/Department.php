<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsToMany(Employee::class, 'employee_departments')->withTimestamps();
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}
