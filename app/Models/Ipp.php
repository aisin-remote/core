<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ipp extends Model
{
    protected $fillable = [
        'employee_id',
        'nama',
        'department',
        'division',
        'section',
        'date_review',
        'pic_review',
        'on_year',
        'no_form',
        'status',
        'summary'
    ];

    protected $casts = [
        'summary' => 'array',
        'date_review' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function points()
    {
        return $this->hasMany(IppPoint::class);
    }
}
