<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rtc extends Model
{
    use HasFactory;
    protected $table = 'rtc';

    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'area_id')->where('id', 'department');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'area_id')->where('id', 'section');
    }

    public function subsection()
    {
        return $this->belongsTo(SubSection::class, 'area_id')->where('id', 'sub_section');
    }
}
