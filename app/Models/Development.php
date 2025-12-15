<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Development extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function idp()
    {
        return $this->belongsTo(Idp::class);
    }
    public function steps()
    {
        return $this->hasMany(DevelopmentApprovalStep::class, 'development_mid_id');
    }
}
