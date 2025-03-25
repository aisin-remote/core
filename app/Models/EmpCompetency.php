<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpCompetency extends Model
{
    use HasFactory;
    protected $table = 'employee_competency';
    protected $guarded = ['id'];
}
