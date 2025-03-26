<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCompetency extends Model
{
    use HasFactory;
    protected $table = 'group_competency';
    protected $guarded = ['id'];
}
