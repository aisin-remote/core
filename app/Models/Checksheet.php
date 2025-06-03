<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckSheet extends Model
{
    use HasFactory;
    protected $table = 'checksheet';
    protected $guarded = ['id'];

    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }

    public function assessments()
    {
        return $this->hasMany(ChecksheetAssessment::class);
    }
}