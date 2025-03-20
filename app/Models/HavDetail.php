<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HavDetail extends Model
{
    use HasFactory;


    protected $guarded = ['id'];

    protected $fillable = ['alc_id', 'hav_id', 'score', 'evidence'];

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
}
