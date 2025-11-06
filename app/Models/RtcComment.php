<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtcComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rtc_id',
        'employee_id',
        'status_from',
        'status_to',
        'comment'
    ];

    public function rtc()
    {
        return $this->belongsTo(Rtc::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
