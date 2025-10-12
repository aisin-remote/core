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

    public function rtc()
    {
        return $this->hasMany(Rtc::class, 'area_id', 'id')
            ->where('area', 'department');
    }

    protected function rtcLatestByTerm(string $term)
    {
        return $this->hasOne(Rtc::class, 'area_id', 'id')
            ->areaLogical('department')
            ->termLogical($term)
            ->latestOfMany('id'); // atau 'created_at' jika lebih cocok
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
