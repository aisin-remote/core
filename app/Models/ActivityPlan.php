<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityPlan extends Model
{
    protected $fillable = [
        'ipp_id',
        'employee_id',
        'form_no',
        'fy_start_year',
        'division',
        'department',
        'section',
        'status',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function ipp()
    {
        return $this->belongsTo(Ipp::class, 'ipp_id', 'id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function items()
    {
        return $this->hasMany(ActivityPlanItem::class);
    }
}
