<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HavDetail extends Model
{
    use HasFactory;


    protected $guarded = ['id'];

    protected $fillable = ['alc_id', 'hav_id', 'score', 'evidence', 'suggestion_development', 'is_assessment'];

    // Many-to-One: HavDetail -> Hav
    public function hav()
    {
        return $this->belongsTo(Hav::class, 'hav_id', 'id');
    }

    // One-to-Many: HavDetail -> HavDetailKeyBehavior
    public function keyBehaviors()
    {
        return $this->hasMany(HavDetailKeyBehavior::class, 'hav_detail_id', 'id');
    }

    // Many-to-One: HavDetail -> Alc
    public function alc()
    {
        return $this->belongsTo(Alc::class, 'alc_id', 'id');
    }

    public function idp()
    {
        return $this->hasMany(Idp::class, 'hav_detail_id', 'id');
    }
}
