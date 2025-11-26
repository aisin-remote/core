<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IppApprovalStep extends Model
{
    use HasFactory;

    protected $table = 'ipp_approval_steps';

    protected $fillable = [
        'ipp_id',
        'step_order',
        'type',
        'role',
        'label',
        'actor_id',
        'acted_at',
        'status',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    // Relasi ke IPP
    public function ipp()
    {
        return $this->belongsTo(Ipp::class);
    }

    // Relasi ke Employee yang melakukan action
    public function actor()
    {
        return $this->belongsTo(Employee::class, 'actor_id');
    }
}
