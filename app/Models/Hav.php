<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hav extends Model
{
    use HasFactory;


    protected $guarded = ['id'];

    protected $fillable = ['employee_id', 'quadrant', 'status'];

    // One-to-Many: Hav -> HavDetails
    public function details()
    {
        return $this->hasMany(HavDetail::class, 'hav_id', 'id');
    }


    // Many-to-One: Hav -> Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function commentHistory()
    {
        return $this->hasMany(HavCommentHistory::class, 'hav_id');
    }

    // Many-to-One: Hav -> Employee
    public function quadran()
    {
        return $this->belongsTo(QuadranMaster::class, 'quadrant', 'code');
    }
    // Get the status of the employee
    public function getStatusQuadrantAttribute()
    {
        $statusList = [
            1 => 'Star',
            2 => 'Future Star',
            3 => 'Future Star',
            4 => 'Potential Candidate',
            5 => 'Raw Diamond',
            6 => 'Candidate',
            7 => 'Top Performer',
            8 => 'Strong Performer',
            9 => 'Career Person',
            10 => 'Most Unfit Employee',
            11 => 'Unfit Employee',
            12 => 'Problem Employee',
            13 => 'Maximal Contribution',
            14 => 'Contribution',
            15 => 'Minimal Contribution',
            16 => 'Dead Wood',
        ];

        return $statusList[$this->quadrant] ?? 'Unknown';
    }
}
