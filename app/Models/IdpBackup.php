<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdpBackup extends Model
{
    protected $guarded = ['id'];

    protected $cast = [
        'changed_at' => 'datetime',
    ];

    public function idp()
    {
        return $this->belongsTo(Idp::class);
    }
    public function alc()
    {
        return $this->belongsTo(Alc::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
