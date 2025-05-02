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
    public function short()
    {
        return $this->belongsTo(Employee::class, 'short_term');
    }
    public function mid()
    {
        return $this->belongsTo(Employee::class, 'mid_term');
    }
    public function long()
    {
        return $this->belongsTo(Employee::class, 'long_term');
    }
}
