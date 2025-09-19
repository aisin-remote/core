<?php

namespace App\Models;

use App\Models\Concerns\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Icp extends Model
{
    use HasFactory;
    use CompanyScoped;

    protected $table = 'icp';
    protected $guarded = ['id'];
    protected $casts = [
        'date' => 'date',
    ];

    public const STATUS_REVISE    = 0;
    public const STATUS_SUBMITTED = 1;
    public const STATUS_CHECKED   = 2;
    public const STATUS_APPROVED  = 3;
    public const STATUS_DRAFT     = 4;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(IcpDetail::class, 'icp_id', 'id');
    }
    public function latestIcp()
    {
        return $this->hasOne(Icp::class)->latestOfMany();
    }

    public function steps()
    {
        return $this->hasMany(IcpApprovalStep::class, 'icp_id');
    }
}
