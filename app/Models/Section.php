<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Rtc;

class Section extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }
    public function subSections()
    {
        return $this->hasMany(SubSection::class);
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

    // RTC terbaru per-term (area = 'section')
    protected function rtcLatestByTerm(string $term)
    {
        return $this->hasOne(Rtc::class, 'area_id', 'id')
            ->areaLogical('section')
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
