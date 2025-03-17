<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevelopmentOne extends Model
{
    use HasFactory;


    protected $fillable = [ 'development_program', 'evaluation_result'];

   
}
