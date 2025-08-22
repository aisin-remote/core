<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Rtc;

class Department extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function employees()
    {
        return $this->belongsToMany(
            Employee::class,
            'employee_departments',
            'department_id',
            'employee_id'
        )->withTimestamps();
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    // ===== Kandidat dari kolom lama (biarkan ada bila masih dipakai tempat lain)
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

    // ===== RTC terbaru per-term (area = 'department')
    public function rtcShortLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'department')->where('term', 'short')
            ->latestOfMany('created_at');
    }

    public function rtcMidLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'department')->where('term', 'mid')
            ->latestOfMany('created_at');
    }

    public function rtcLongLatest()
    {
        return $this->hasOne(Rtc::class, 'area_id')
            ->where('area', 'department')->where('term', 'long')
            ->latestOfMany('created_at');
    }
}
