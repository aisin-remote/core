<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
