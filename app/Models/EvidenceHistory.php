<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvidenceHistory extends Model
{
    protected $fillable = [
        'employee_competency_id',
        'user_id',
        'action',
        'file_name'
    ];

    public function employeeCompetency()
    {
        return $this->belongsTo(EmployeeCompetency::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
