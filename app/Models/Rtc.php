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

    /* ====== ALIASES & SCOPES ====== */

    // Beberapa data lama mungkin pakai short_term / mid_term / long_term
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
        // toleran dengan kapitalisasi 'Division' vs 'division'
        $a = strtolower(trim($area));
        return [$a, ucfirst($a)];
    }

    public function scopeArea($q, string $area)
    {
        // versi lama – tetap ada untuk kompatibilitas
        return $q->where('area', $area);
    }

    // versi toleran alias
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
        // versi lama – tetap ada
        return $q->where('term', $term);
    }

    // versi toleran alias
    public function scopeTermLogical($q, string $term)
    {
        return $q->whereIn('term', self::termAliases($term));
    }
}
