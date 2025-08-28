<?php

namespace App\Models;

use App\Models\Concerns\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessment extends Model
{
    use HasFactory;
    use CompanyScoped;

    protected $table = 'assessments';
    protected $guarded = ['id'];
    // protected $fillable = ['employee_id', 'date', 'upload', 'alc_id'];

    /**
     * Relasi ke model Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(DetailAssessment::class, 'assessment_id', 'id');
    }

    public function idp()
    {
        return $this->hasMany(Idp::class, 'assessment_id', 'id');
    }

    public function idpPrograms()
    {
        return $this->hasMany(Idp::class, 'assessment_id');
    }


    /**
     * Relasi ke model Alc
     */
    public function alc()
    {
        return $this->belongsTo(Alc::class, 'alc_id', 'id');
    }
}
