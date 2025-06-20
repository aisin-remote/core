<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IdpCommentHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function idp()
    {
        return $this->belongsTo(Idp::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
