<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HavCommentHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function hav()
    {
        return $this->belongsTo(Hav::class);
    }
    // Many-to-One: Hav -> Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
