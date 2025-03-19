<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevelopmentOne extends Model
{
    use HasFactory;


    protected $fillable = [  'employee_id','development_program', 'evaluation_result'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


}
