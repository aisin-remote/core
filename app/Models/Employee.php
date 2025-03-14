<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = ['name', 'email', 'position', 'aisin_entry_date'];
    public function assessment()
    {
        return $this->hasMany(Assessment::class, 'employee_id', 'id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'employee_departments')->withTimestamps();
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id', 'id');
    }

    public function getWorkingPeriodAttribute()
    {
        return $this->aisin_entry_date
            ? Carbon::parse($this->aisin_entry_date)->diffInYears(Carbon::now())
            : null;
    }
}
