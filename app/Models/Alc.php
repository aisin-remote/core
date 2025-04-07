<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alc extends Model
{
    use HasFactory;

    protected $table = 'alc'; // Nama tabel di database

    protected $fillable = [
        'name', // Nama ALC
    ];

    // Relasi ke Assessment
    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
    public function idp()
    {
        return $this->hasMany(Assessment::class);
    }
}
