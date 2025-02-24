<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',   // Menggunakan employee_id sesuai dengan database
        'alc_id',        // Relasi ke tabel alc
        'score',         // Nilai yang diberikan untuk setiap alc_id
        'description',   // Deskripsi untuk setiap alc_id
        'date',
        'upload',
    ];

    /**
     * Relasi ke model Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'npk');
    }

    /**
     * Relasi ke model Alc
     */
    public function alc()
    {
        return $this->belongsTo(Alc::class, 'alc_id', 'id');
    }
}
