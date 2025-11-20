<?php

namespace App\Models;

use App\Models\Icp;
use Illuminate\Database\Eloquent\Model;

class IcpSnapshot extends Model
{
    protected $table = 'icp_snapshots';
    protected $guarded = ['id'];
    protected $casts = ['icp' => 'array', 'details' => 'array', 'steps' => 'array'];
    public function icp()
    {
        return $this->belongsTo(Icp::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
