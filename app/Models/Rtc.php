<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rtc extends Model
{
    use HasFactory;
    protected $table = 'rtc';

    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'area_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'area_id');
    }

    public function subsection()
    {
        return $this->belongsTo(SubSection::class, 'area_id');
    }
    public function scopeArea($q, string $area)
    {
        return $q->where('area', $area);
    }
    public function scopeAreaId($q, int $id)
    {
        return $q->where('area_id', $id);
    }
    public function scopeTerm($q, string $term)
    {
        return $q->where('term', $term);
    }
}
