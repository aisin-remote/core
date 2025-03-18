<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Development extends Model {
    use HasFactory;

    protected $fillable = ['employee_id','development_program', 'development_achievement', 'next_action'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}
