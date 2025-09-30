<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IppComment extends Model
{
    protected $fillable = [
        'ipp_id',
        'user_id',
        'employee_id',
        'status_from',
        'status_to',
        'comment',
        'parent_id',
    ];

    public function ipp(): BelongsTo
    {
        return $this->belongsTo(Ipp::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(IppComment::class, 'parent_id');
    }
}
