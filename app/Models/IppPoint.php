<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IppPoint extends Model
{
    protected $fillable = [
        'ipp_id',
        'category',
        'activity',
        'target_mid',
        'target_one',
        'start_date',
        'due_date',
        'weight',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date'
    ];

    public function ipp()
    {
        return $this->belongsTo(Ipp::class);
    }

    public function activityPlanItems()
    {
        return $this->hasMany(ActivityPlanItem::class);
    }
}
