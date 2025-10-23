<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityPlanItem extends Model
{
    protected $fillable = [
        'activity_plan_id',
        'ipp_point_id',
        'kind_of_activity',
        'target',
        'pic_employee_id',
        'schedule_mask',
        'frequency',
        'cached_category',
        'cached_activity',
        'cached_start_date',
        'cached_due_date',
        'impact_note',
        'cr_value',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'cached_start_date' => 'date',
        'cached_due_date'   => 'date',
        'cr_value'          => 'decimal:2',
    ];

    public function plan()
    {
        return $this->belongsTo(ActivityPlan::class, 'activity_plan_id');
    }
    public function ippPoint()
    {
        return $this->belongsTo(IppPoint::class);
    }
    public function pic()
    {
        return $this->belongsTo(Employee::class, 'pic_employee_id');
    }
}
