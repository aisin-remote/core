<?php
// app/Models/IdpApproval.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IdpApproval extends Model
{
    use HasFactory;

    protected $table = 'idp_approvals';

    protected $fillable = [
        'idp_id',
        'approve_by',
        'level',
        'approved_at',
        'assessment_id'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function idp()
    {
        return $this->belongsTo(Idp::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approve_by');
    }
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
