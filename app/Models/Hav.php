<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hav extends Model
{
    use HasFactory;


    protected $guarded = ['id'];

    protected $fillable = ['employee_id'];

    // One-to-Many: Hav -> HavDetails
    public function details()
    {
        return $this->hasMany(HavDetail::class, 'hav_id', 'id');
    }

    // Many-to-One: Hav -> Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
