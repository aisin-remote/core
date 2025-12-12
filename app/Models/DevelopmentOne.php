<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevelopmentOne extends Model
{
    use HasFactory;


    protected $fillable = [  'employee_id','idp_id','development_program', 'evaluation_result', 'status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
   public function idp()
    {
        return $this->belongsTo(Idp::class, 'idp_id');
    }
    public function steps()
    {
        return $this->hasMany(DevelopmentApprovalStep::class, 'development_one_id');
    }

}
