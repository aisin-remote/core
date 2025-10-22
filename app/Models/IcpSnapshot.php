<?php

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
}
