<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubSection extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * SubSection is led by one Leader (Employee)
     */
    public function leader()
    {
        return $this->belongsTo(Employee::class, 'leader_id');
    }

    /**
     * SubSection has many Employees
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
