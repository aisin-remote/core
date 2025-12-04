<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtcApproval extends Model
{
    use HasFactory;

    protected $table = 'rtc_approvals';

    protected $fillable = [
        'rtc_id',
        'approve_by',
        'level',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'level'       => 'integer',
    ];

    /**
     * Relasi ke RTC (satu approval milik satu RTC).
     */
    public function rtc()
    {
        return $this->belongsTo(Rtc::class);
    }

    /**
     * Relasi ke employee yang melakukan approve.
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approve_by');
    }

    /**
     * (Opsional) accessor label level, biar enak dipakai di view.
     */
    public function getLevelLabelAttribute(): string
    {
        // Silakan sesuaikan mapping ini dengan kebutuhanmu
        return match ((int) $this->level) {
            1       => 'GM',
            2       => 'Direktur',
            3       => 'VPD',
            4       => 'President',
            default => 'Unknown',
        };
    }
}
