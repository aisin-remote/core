<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpaComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ipa_id',
        'user_id',
        'employee_id',
        'status_from',
        'status_to',
        'comment',
        'parent_id',
    ];

    public function ipa()
    {
        return $this->belongsTo(IpaHeader::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function parent()
    {
        return $this->belongsTo(IppComment::class, 'parent_id');
    }
}
