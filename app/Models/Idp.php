<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idp extends Model
{
    use HasFactory;

    protected $table = 'idp';
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'id');
    }
}
