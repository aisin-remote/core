<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevelopmentApprovalStep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = ['acted_at' => 'datetime'];

    public function midDevelopment()
    {
        return $this->belongsTo(Development::class, 'development_mid_id');
    }

    public function oneDevelopment()
    {
        return $this->belongsTo(DevelopmentOne::class, 'development_one_id');
    }
    public function actor()
    {
        return $this->belongsTo(Employee::class, 'actor_id');
    }
}
