<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyBehavior extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = ['alc_id', 'description'];

    // One-to-Many: KeyBehavior -> HavDetailKeyBehavior
    public function havDetailKeyBehaviors()
    {
        return $this->hasMany(HavDetailKeyBehavior::class, 'key_behavior_id', 'id');
    }
}
