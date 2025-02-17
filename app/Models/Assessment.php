<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_npk',
        'date',
        'vision_business_sense',
        'customer_focus',
        'interpersonal_skil',
        'analysis_judgment',
        'planning_driving_action',
        'leading_motivating',
        'teamwork',
        'drive_courage',
        'upload',
    ];

        public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_npk', 'npk');
    }

}
