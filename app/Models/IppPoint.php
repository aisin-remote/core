<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IppPoint extends Model
{
    protected $fillable = [
        'ipp_id',
        'category',
        'activity',
        'target_mid',
        'target_one',
        'due_date',
        'weight'
    ];

    public function ipp()
    {
        return $this->belongsTo(Ipp::class);
    }
}
