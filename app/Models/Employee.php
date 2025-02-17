<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'npk'; // Set primary key ke npk
    public $incrementing = false; // Non-auto increment
    protected $keyType = 'string'; // Set tipe primary key ke string

    protected $fillable = [
        'npk', 'name', 'identity_number', 'birthday_date', 'photo', 'gender',
        'company_name', 'function', 'position_name', 'aisin_entry_date', 'working_period',
        'company_group', 'foundation_group', 'position', 'grade', 'last_promote_date'
    ];
}
