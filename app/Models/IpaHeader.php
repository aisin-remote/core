<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpaHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ipa_headers';

    protected $fillable = [
        'employee_id',
        'ipp_id',
        'on_year',
        'notes',
        'activity_total',
        'achievement_total',
        'grand_total',
        'grand_score',
        'created_by',
    ];

    // ==== Relationships ====
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function ipp()
    {
        return $this->belongsTo(Ipp::class);
    }

    public function activities()
    {
        return $this->hasMany(IpaActivity::class, 'ipa_id');
    }

    public function achievements()
    {
        return $this->hasMany(IpaAchievement::class, 'ipa_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==== Helpers ====
    public function recalcTotals(): void
    {
        $actTotal = $this->activities()->sum('calc_score');
        $achTotal = $this->achievements()->sum('calc_score');

        $actScore = $this->activities()->sum('self_score');
        $achScore = $this->achievements()->sum('self_score');
        $this->update([
            'activity_total'    => $actTotal,
            'achievement_total' => $achTotal,
            'grand_total'       => $actTotal + $achTotal,
            'grand_score'       => $actScore + $achScore,
        ]);
    }
}
