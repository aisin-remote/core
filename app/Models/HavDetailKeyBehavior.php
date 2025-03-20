<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HavDetailKeyBehavior extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = ['hav_detail_id', 'key_behavior_id', 'hav_id', 'score'];

    // Many-to-One: HavDetailKeyBehavior -> HavDetail
    public function havDetail()
    {
        return $this->belongsTo(HavDetail::class, 'hav_detail_id', 'id');
    }

    // Many-to-One: HavDetailKeyBehavior -> KeyBehavior
    public function keyBehavior()
    {
        return $this->belongsTo(KeyBehavior::class, 'key_behavior_id', 'id');
    }
}
