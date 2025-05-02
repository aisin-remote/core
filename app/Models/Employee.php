<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // === RELATIONSHIPS ===

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'employee_id', 'id');
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

    // Relasi ke SubSection
    public function subSection()
    {
        return $this->belongsTo(SubSection::class);
    }

    // Relasi ke Section melalui SubSection
    public function section()
    {
        return $this->subSection ? $this->subSection->section : null;
    }

    // Relasi ke SubSection sebagai Leader
    public function leadingSubSection()
    {
        return $this->hasOne(SubSection::class, 'leader_id');
    }

    // Relasi ke Section sebagai Supervisor
    public function leadingSection()
    {
        return $this->hasOne(Section::class, 'supervisor_id');
    }

    // Relasi ke Department sebagai Manager
    public function leadingDepartment()
    {
        return $this->hasOne(Department::class, 'manager_id');
    }

    // Relasi ke Division sebagai GM
    public function leadingDivision()
    {
        return $this->hasOne(Division::class, 'gm_id');
    }

    // Relasi ke Plant sebagai Director
    public function leadingPlant()
    {
        return $this->hasOne(Plant::class, 'director_id');
    }

    // === ACCESSORS ===

    // Mengambil periode kerja berdasarkan entry date
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
    // Mengambil Section dari SubSection
    public function getSectionAttribute()
    {
        return $this->subSection ? $this->subSection->section : null;
    }

    // Accessor untuk Department
    public function getDepartmentAttribute()
    {
        if ($this->subSection?->section?->department) {
            return $this->subSection->section->department;
        }

        if ($this->section?->department) {
            return $this->section->department;
        }

        if ($this->leadingSection?->department) {
            return $this->leadingSection->department;
        }

        if ($this->leadingDepartment) {
            return $this->leadingDepartment;
        }

        return null;
    }

    // Accessor untuk Division
    public function getDivisionAttribute()
    {
        if ($this->department?->division) {
            return $this->department->division;
        }

        if ($this->leadingDivision) {
            return $this->leadingDivision;
        }

        return null;
    }

    // Accessor untuk Plant
    public function getPlantAttribute()
    {
        if ($this->division?->plant) {
            return $this->division->plant;
        }

        if ($this->leadingPlant) {
            return $this->leadingPlant;
        }

        return null;
    }

    // === CUSTOM ACCESSOR ===

    // Mendapatkan SubSection dengan Section
    public function getSubSectionWithSectionAttribute()
    {
        return $this->subSection()->with('section')->first();
    }
}
