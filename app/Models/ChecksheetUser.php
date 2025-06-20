<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecksheetUser extends Model
{
    use HasFactory;

    protected $table = 'checksheet_users';
    protected $guarded = ['id'];
    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }

    public function subSection()
    {
        return $this->belongsTo(SubSection::class, 'sub_section_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id', 'id');
    }
}