<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi ke model Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Relasi ke model Alc
     */
    public function alc()
    {
        return $this->belongsTo(Alc::class, 'alc_id', 'id');
    }
}
