<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IcpDetail extends Model
{
    use HasFactory;

    protected $table = 'icp_details';
    protected $guarded = ['id'];

}
