<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'employee_id', 'id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'employee_departments')->withTimestamps();
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id', 'id');
    }

    public function promotionHistory()
    {
        return $this->hasMany(PromotionHistory::class, 'employee_id', 'id');
    }
    public function astratraining()
    {
        return $this->hasMany(AstraTraining::class, 'employee_id', 'id');
    }
    public function externaltraining()
    {
        return $this->hasMany(ExternalTraining::class, 'employee_id', 'id');
    }
    public function educations()
    {
        return $this->hasMany(EducationalBackground::class, 'employee_id', 'id');
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkingExperience::class, 'employee_id', 'id');
    }

    public function performanceAppraisals()
    {
        return $this->hasMany(PerformanceAppraisalHistory::class, 'employee_id', 'id');
    }
    public function havHistory()
    {
        return $this->hasMany(Hav::class)->orderByDesc('year');
    }
    public function getWorkingPeriodAttribute()
    {
        return $this->aisin_entry_date
            ? Carbon::parse($this->aisin_entry_date)->diffInYears(Carbon::now())
            : null;
    }
    public function havQuadrants()
    {
        return $this->hasMany(\App\Models\HavQuadrant::class);
    }

    public function latestHavQuadrant()
    {
        return $this->hasOne(\App\Models\HavQuadrant::class)->latestOfMany();
    }

    public function performanceAppraisalHistories()
    {
        return $this->hasMany(\App\Models\PerformanceAppraisalHistory::class);
    }
}
