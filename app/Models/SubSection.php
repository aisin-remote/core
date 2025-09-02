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
    protected function rtcLatestByTerm(string $term)
    {
        return $this->hasOne(Rtc::class, 'area_id', 'id')
            ->areaLogical('sub_section')
            ->termLogical($term)
            ->latestOfMany('id');
    }
    public function rtcShortLatest()
    {
        return $this->rtcLatestByTerm('short');
    }
    public function rtcMidLatest()
    {
        return $this->rtcLatestByTerm('mid');
    }
    public function rtcLongLatest()
    {
        return $this->rtcLatestByTerm('long');
    }
}
