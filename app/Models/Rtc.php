<?php

namespace App\Models;

use App\Models\Concerns\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rtc extends Model
{
    use HasFactory;
    use CompanyScoped;

    protected $table = 'rtc';
    protected $guarded = ['id'];

    /* ====== RELATIONS ====== */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'area_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'area_id');
    }

    public function subsection()
    {
        return $this->belongsTo(SubSection::class, 'area_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'area_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'area_id');
    }

    /* ====== ALIASES & SCOPES ====== */

    public static function termAliases(string $term): array
    {
        $t = strtolower(trim($term));
        return match ($t) {
            'short' => ['short', 'short_term', 'st', 's/t'],
            'mid'   => ['mid', 'mid_term', 'mt', 'm/t'],
            'long'  => ['long', 'long_term', 'lt', 'l/t'],
            default => [$t],
        };
    }

    public static function areaAliases(string $area): array
    {
        $a = strtolower(trim($area));
        return [$a, ucfirst($a)];
    }

    public function scopeArea($q, string $area)
    {
        return $q->where('area', $area);
    }

    public function scopeAreaLogical($q, string $area)
    {
        return $q->whereIn('area', self::areaAliases($area));
    }

    public function scopeAreaId($q, int $id)
    {
        return $q->where('area_id', $id);
    }

    public function scopeTerm($q, string $term)
    {
        return $q->where('term', $term);
    }

    public function scopeTermLogical($q, string $term)
    {
        return $q->whereIn('term', self::termAliases($term));
    }

    /* ====== ACCESSORS / HELPERS ====== */

    // Model area yang cocok (department / section / dsb)
    public function getAreaModelAttribute()
    {
        return match (strtolower($this->area)) {
            'department'   => $this->department,
            'section'      => $this->section,
            'sub_section'  => $this->subsection,
            'division'     => $this->division,
            'plant'        => $this->plant,
            'direksi'      => null, // khusus direksi, bukan model
            default        => null,
        };
    }

    // Nama area yang enak ditampilkan di tabel
    public function getAreaNameAttribute(): string
    {
        $area = strtolower($this->area);

        if ($area === 'direksi') {
            return 'Direksi';
        }

        // kalau relasi ketemu dan punya name
        if ($this->area_model && isset($this->area_model->name)) {
            return $this->area_model->name;
        }

        // fallback aman
        return ucfirst($this->area) . ' #' . $this->area_id;
    }
}
