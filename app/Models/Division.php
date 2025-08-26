<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Rtc;

class Division extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function gm()
    {
        return $this->belongsTo(Employee::class, 'gm_id');
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

    public function rtcs()
    {
        return $this->hasMany(Rtc::class, 'area_id')->where('area', 'Division');
    }

    /* Ambil 1 baris RTC terbaru / term */
    public function rtcShortLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'Division')->where('term', 'short')
            ->latestOfMany('created_at'); // butuh Laravel 8.42+
    }

    public function rtcMidLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'Division')->where('term', 'mid')
            ->latestOfMany('created_at');
    }

    public function rtcLongLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'Division')->where('term', 'long')
            ->latestOfMany('created_at');
    }
}
