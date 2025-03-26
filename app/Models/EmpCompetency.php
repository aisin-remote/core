<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpCompetency extends Model
{
    use HasFactory;
    protected $table = 'emp_competencies';
    protected $guarded = ['competency_id'];
}
