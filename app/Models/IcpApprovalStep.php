<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IcpApprovalStep extends Model
{
    use HasFactory;

    protected $table = 'icp_approval_steps';
    protected $guarded = ['id'];
    protected $casts = ['acted_at' => 'datetime'];

    public function icp()
    {
        return $this->belongsTo(Icp::class, 'icp_id');
    }
    public function actor()
    {
        return $this->belongsTo(Employee::class, 'actor_id');
    }
}
