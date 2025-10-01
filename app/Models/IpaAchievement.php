<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpaAchievement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ipa_achievements';

    protected $fillable = [
        'ipa_id',
        'ipp_point_id',
        'title',
        'description',
        'one_year_target',
        'one_year_achievement',
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

    // ==== Mutators ====
    public function setCalcScore(): void
    {
        $this->calc_score = (float)$this->weight * (float)$this->self_score;
    }
}
