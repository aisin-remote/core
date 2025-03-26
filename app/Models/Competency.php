<?php

namespace App\Models;

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

    public function departement()
    {
        return $this->belongsTo(Department::class, 'departement_id', 'id');
    }

    public function group_competency()
    {
        return $this->belongsTo(Department::class, 'group_competency_id', 'id');
    }

}
