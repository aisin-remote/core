<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
    
    public function gm()
    {
        return $this->belongsTo(Employee::class, 'gm_id');
    }
}
