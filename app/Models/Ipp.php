<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ipp extends Model
{
    protected $fillable = [
        'employee_id',
        'nama',
        'department',
        'division',
        'section',
        'date_review',
        'pic_review',
        'pic_review_id',
        'submitted_at',
        'checked_at',
        'checked_by',
        'approved_at',
        'approved_by',
        'on_year',
        'no_form',
        'status',
        'summary'
    ];

    protected $casts = [
        'summary'      => 'array',
        'date_review'  => 'date',
        'submitted_at' => 'datetime',
        'checked_at'   => 'datetime',
        'approved_at'  => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function picReviewer()
    {
        return $this->belongsTo(Employee::class, 'pic_review_id');
    }

    public function checkedBy()
    {
        return $this->belongsTo(Employee::class, 'checked_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function points()
    {
        return $this->hasMany(IppPoint::class);
    }

    public function comments()
    {
        return $this->hasMany(IppComment::class);
    }

    public function activityPlan()
    {
        return $this->hasOne(ActivityPlan::class, 'ipp_id', 'id');
    }
}
