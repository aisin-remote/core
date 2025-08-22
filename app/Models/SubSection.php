<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Rtc;

class SubSection extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
    public function leader()
    {
        return $this->belongsTo(Employee::class, 'leader_id');
    }
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function short()
    {
        return $this->belongsTo(Employee::class, 'short_term');
    }
    public function mid()
    {
        return $this->belongsTo(Employee::class, 'mid_term');
    }
    public function long()
    {
        return $this->belongsTo(Employee::class, 'long_term');
    }

    // RTC terbaru per-term (area = 'sub_section')
    public function rtcShortLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'sub_section')->where('term', 'short')
            ->latestOfMany('created_at');
    }

    public function rtcMidLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'sub_section')->where('term', 'mid')
            ->latestOfMany('created_at');
    }

    public function rtcLongLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'sub_section')->where('term', 'long')
            ->latestOfMany('created_at');
    }
}
