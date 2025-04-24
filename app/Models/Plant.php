<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plant extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }
    
    public function director()
    {
        return $this->belongsTo(Employee::class, 'director_id');
    }
}
