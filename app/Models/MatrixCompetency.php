<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MatrixCompetency extends Model
{
    use HasFactory;

    protected $table = 'matrix_competencies'; // Nama tabel di database

   protected $guarded = ['id'];

    // Relasi ke Assessment
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

}
