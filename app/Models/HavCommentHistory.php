<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HavCommentHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function hav()
    {
        return $this->belongsTo(Hav::class);
    }
}
