<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailAssessment extends Model
{
    use HasFactory;
    protected $table = 'detail_assessments';
    protected $guarded = ['id'];

    protected $fillable = ['assessment_id', 'alc_id', 'score', 'strength', 'weakness'];

    /**
     * Relasi ke model Assessment
     */
    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'id');
    }

    /**
     * Relasi ke model Alc
     */
    public function alc()
    {
        return $this->belongsTo(Alc::class, 'alc_id', 'id');
    }
}
