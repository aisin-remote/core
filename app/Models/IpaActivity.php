<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpaActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ipa_activities';

    protected $fillable = [
        'ipa_id',
        'source',
        'ipp_point_id',
        'category',
        'description',
        'weight',
        'self_score',
        'calc_score',
        'evidence',
        'position',
    ];

    // ==== Relationships ====
    public function ipa()
    {
        return $this->belongsTo(IpaHeader::class, 'ipa_id');
    }

    public function ippPoint()
    {
        return $this->belongsTo(IppPoint::class, 'ipp_point_id');
    }

    // ==== Mutators ====
    public function setCalcScore(): void
    {
        $this->calc_score = (float)$this->weight * (float)$this->self_score;
    }
}
