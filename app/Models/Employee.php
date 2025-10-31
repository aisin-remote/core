<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Assessment;
use App\Models\Competency;
use App\Models\Department;
use Illuminate\Support\Str;
use App\Models\EmployeeCompetency;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // === RELATIONSHIPS ===
    // In Employee model
    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            'employee_departments',   // ← nama pivot table yang benar
            'employee_id',            // ← foreign key utk model Employee di pivot
            'department_id'           // ← foreign key utk model Department di pivot
        )->withTimestamps();
    }


    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'employee_id', 'id');
    }
    public function icp()
    {
        return $this->hasMany(Icp::class, 'employee_id', 'id');
    }

    public function latestIcp()
    {
        return $this->hasOne(Icp::class, 'employee_id')->latestOfMany();
    }



    public function employeeCompetencies()
    {
        return $this->hasMany(EmployeeCompetency::class, 'employee_id', 'id');
    }

    public function competencies()
    {
        return $this->belongsToMany(Competency::class)
            ->withPivot('score');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($employee) {
            if ($employee->user) {
                $employee->user->delete();
            }
        });
    }

    public function scopeForCompany($query, ?string $company)
    {
        if (!$company) return $query; // HRD: lihat semua
        return $query->where('company_name', $company);
    }

    public function promotionHistory()
    {
        return $this->hasMany(PromotionHistory::class, 'employee_id', 'id');
    }

    public function idpCommentHistory()
    {
        return $this->hasMany(IdpCommentHistory::class, 'employee_id', 'id');
    }

    public function astratraining()
    {
        return $this->hasMany(AstraTraining::class, 'employee_id', 'id');
    }

    public function externaltraining()
    {
        return $this->hasMany(ExternalTraining::class, 'employee_id', 'id');
    }

    public function educations()
    {
        return $this->hasMany(EducationalBackground::class, 'employee_id', 'id');
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkingExperience::class, 'employee_id', 'id');
    }

    public function performanceAppraisals()
    {
        return $this->hasMany(PerformanceAppraisalHistory::class, 'employee_id', 'id');
    }

    public function havHistory()
    {
        return $this->hasMany(Hav::class)->orderByDesc('year');
    }

    // Relasi ke SubSection
    public function subSection()
    {
        return $this->belongsTo(SubSection::class);
    }

    // Relasi ke department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Relasi ke Section melalui SubSection
    public function section()
    {
        return $this->subSection ? $this->subSection->section : null;
    }

    // Relasi ke SubSection sebagai Leader
    public function leadingSubSection()
    {
        return $this->hasOne(SubSection::class, 'leader_id');
    }

    // Relasi ke Section sebagai Supervisor
    public function leadingSection()
    {
        return $this->hasOne(Section::class, 'supervisor_id');
    }

    // Relasi ke Department sebagai Manager
    public function leadingDepartment()
    {
        return $this->hasOne(Department::class, 'manager_id');
    }

    // Relasi ke Division sebagai GM
    public function leadingDivision()
    {
        return $this->hasOne(Division::class, 'gm_id');
    }

    // Relasi ke Plant sebagai Director
    public function leadingPlant()
    {
        return $this->hasOne(Plant::class, 'director_id');
    }

    // === ACCESSORS ===

    // Mengambil periode kerja berdasarkan entry date
    public function getWorkingPeriodAttribute()
    {
        return $this->aisin_entry_date
            ? Carbon::parse($this->aisin_entry_date)->diffInYears(Carbon::now())
            : null;
    }


    // Mengambil Hav
    public function hav()
    {
        return $this->hasMany(\App\Models\Hav::class, 'employee_id', 'id');
    }

    public function havQuadrants()
    {
        return $this->hasMany(\App\Models\HavQuadrant::class);
    }

    public function latestHavQuadrant()
    {
        return $this->hasOne(\App\Models\HavQuadrant::class)->latestOfMany();
    }

    public function performanceAppraisalHistories()
    {
        return $this->hasMany(\App\Models\PerformanceAppraisalHistory::class);
    }
    // Mengambil Section dari SubSection
    public function getSectionAttribute()
    {
        return $this->subSection ? $this->subSection->section : null;
    }

    // Accessor untuk Department
    public function getDepartmentAttribute()
    {
        // dd($this->leadingSubSection);
        if ($this->subSection?->section?->department) {
            return $this->subSection->section->department;
        }

        if ($this->section?->department) {
            return $this->section->department;
        }

        if ($this->leadingSubSection?->section->department) {
            return $this->leadingSubSection->section->department;
        }

        if ($this->leadingSection?->department) {
            return $this->leadingSection->department;
        }

        if ($this->leadingDepartment) {
            return $this->leadingDepartment;
        }

        return null;
    }

    // Accessor untuk Division
    public function getDivisionAttribute()
    {
        if ($this->department?->division) {
            return $this->department->division;
        }

        if ($this->leadingDivision) {
            return $this->leadingDivision;
        }

        return null;
    }

    // Accessor untuk Plant
    public function getPlantAttribute()
    {
        if ($this->division?->plant) {
            return $this->division->plant;
        }

        if ($this->leadingPlant) {
            return $this->leadingPlant;
        }

        return null;
    }

    // === CUSTOM ACCESSOR ===

    // Mendapatkan SubSection dengan Section
    public function getSubSectionWithSectionAttribute()
    {
        return $this->subSection()->with('section')->first();
    }

    // ambil bawahan
    public function getSubordinatesByLevel(int $level = 1, array $allowedPositions = [])
    {
        $currentEmployees = collect([$this]);

        for ($i = 0; $i < $level; $i++) {
            $nextEmployees = collect();

            foreach ($currentEmployees as $emp) {
                $nextEmployees = $nextEmployees->merge($this->getDirectSubordinatesOf($emp));
            }

            if ($nextEmployees->isEmpty()) {
                return collect();  // langsung return kosong kalau gak ada bawahan di iterasi ini
            }

            $currentEmployees = $nextEmployees;
        }

        // Jika tidak diberikan posisi yang diizinkan, gunakan default lama
        if (empty($allowedPositions)) {
            $allowedPositions = ['manager', 'supervisor', 'leader', 'jp', 'operator', 'gm'];
        }

        return $currentEmployees->filter(function ($employee) use ($allowedPositions) {
            $normalized = $employee->getNormalizedPosition();
            return in_array($normalized, $allowedPositions);
        });
    }

    private function getDirectSubordinatesOf(Employee $employee)
    {
        $subordinateIds = collect();

        $normalizedPosition = $employee->getNormalizedPosition();


        if ($normalizedPosition === 'vpd') {
            $managerIds = Department::pluck('manager_id')->filter();
            $gmIds = Division::pluck('gm_id')->filter();

            $subordinateIds = $managerIds->merge($gmIds)->unique();
        } elseif ($normalizedPosition === 'president') {
            $managerIds = Department::pluck('manager_id')->filter()->unique();
            $divisionGmIds = Division::pluck('gm_id')->filter()->unique();

            $subordinateIds = $managerIds->isNotEmpty()
                ? $managerIds->merge($divisionGmIds)->unique()
                : $divisionGmIds;
        }


        // Lanjutkan ke kondisi biasa
        if ($employee->leadingPlant && $employee->leadingPlant->director_id === $employee->id) {
            $divisions = Division::where('plant_id', $employee->leadingPlant->id)->get();
            $subordinateIds = $this->collectSubordinates($divisions, 'gm_id', $subordinateIds);
        } elseif ($employee->leadingDivision && $employee->leadingDivision->gm_id === $employee->id) {
            $departments = Department::where('division_id', $employee->leadingDivision->id)->get();
            $subordinateIds = $this->collectSubordinates($departments, 'manager_id', $subordinateIds);
        } elseif ($employee->leadingDepartment && $employee->leadingDepartment->manager_id === $employee->id) {
            $sections = Section::where('department_id', $employee->leadingDepartment->id)->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);
        } elseif ($employee->leadingSection && $employee->leadingSection->supervisor_id === $employee->id) {
            $subSections = SubSection::where('section_id', $employee->leadingSection->id)->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);
        } elseif ($employee->subSection && $employee->subSection->leader_id === $employee->id) {
            $employeesInSameSubSection = Employee::where('sub_section_id', $employee->sub_section_id)
                ->where('id', '!=', $employee->id)
                ->pluck('id');

            $subordinateIds = $subordinateIds->merge($employeesInSameSubSection);
        }

        if ($subordinateIds->isEmpty()) {
            return collect();
        }

        return Employee::whereIn('id', $subordinateIds)->get();
    }

    private function collectSubordinates($entities, $foreignKey, $collector)
    {
        foreach ($entities as $entity) {
            $employeeId = $entity->{$foreignKey};
            if ($employeeId) {
                $collector->push($employeeId);
            }
        }
        return $collector;
    }

    public function getSubAuth()
    {
        return match (strtolower($this->position)) {
            'supervisor', 'manager' => 2,
            'direktur', 'gm' => 1,
            default => 0,
        };
    }

    // ambil atasan
    public function getSuperiorsByLevel(int $level = 1)
    {
        $superiors = collect();
        $current = $this;

        for ($i = 0; $i < $level; $i++) {
            $nextSuperior = $this->getDirectSuperiorOf($current);
            if (!$nextSuperior) {
                break;
            }

            $superiors->push($nextSuperior);
            $current = $nextSuperior;
        }

        return $superiors;
    }

    public static function manualSuperiorMap()
    {
        return [
            // gunakan posisi yang sudah dinormalisasi
            'gm'       => 'direktur',   // kalau struktural gagal, minimal naik ke Direktur
            'direktur' => 'vpd',        // Direktur naik ke VPD
            'vpd'      => 'president',  // VPD naik ke President
            'vp'       => 'president',  // jaga-jaga kalau ada data lama "VP"
        ];
    }


    private function getDirectSuperiorOf(Employee $employee)
    {
        $norm = $employee->getNormalizedPosition();

        // Cabang khusus untuk posisi puncak yang tidak tercakup struktur
        if ($norm === 'vpd') {
            return Employee::whereRaw('LOWER(position) = ?', ['president'])->first();
        }
        if ($norm === 'direktur') {
            return Employee::where(function ($q) {
                $q->whereRaw('LOWER(position) = ?', ['vpd'])
                    ->orWhereRaw('LOWER(position) = ?', ['vp']); // fallback data lama
            })->first() ?? Employee::whereRaw('LOWER(position) = ?', ['president'])->first();
        }

        // ====== LANJUTKAN DENGAN LOGIKA STRUKTURAL YANG SUDAH ADA ======
        if ($employee->leadingDivision && $employee->leadingDivision->plant && $employee->leadingDivision->plant->director_id) {
            return Employee::find($employee->leadingDivision->plant->director_id); // GM -> Direktur
        }
        if ($employee->leadingDepartment && $employee->leadingDepartment->division && $employee->leadingDepartment->division->gm_id) {
            return Employee::find($employee->leadingDepartment->division->gm_id);
        }
        if ($employee->leadingSection && $employee->leadingSection->department && $employee->leadingSection->department->manager_id) {
            return Employee::find($employee->leadingSection->department->manager_id);
        }
        if ($employee->leadingSubSection && $employee->leadingSubSection->section && $employee->leadingSubSection->section->supervisor_id) {
            return Employee::find($employee->leadingSubSection->section->supervisor_id);
        }
        if ($employee->subSection && $employee->subSection->leader_id) {
            return Employee::find($employee->subSection->leader_id);
        }

        // ====== FALLBACK MANUAL (pakai posisi yang DINORMALISASI) ======
        $map = self::manualSuperiorMap();
        $target = $map[$norm] ?? null;

        if ($target) {
            return Employee::where(function ($q) use ($target) {
                $q->whereRaw('LOWER(position) = ?', [$target]);
                if ($target === 'vpd') {
                    // dukung label lama "VP"
                    $q->orWhereRaw('LOWER(position) = ?', ['vp']);
                }
            })->first();
        }

        return null;
    }


    public function getNormalizedPosition()
    {
        $aliasMap = [
            'section head'     => 'supervisor',
            'act section head' => 'supervisor',
            'coordinator'      => 'manager',
            'act coordinator'  => 'manager',
            'act manager'      => 'manager',
            'act supervisor'   => 'supervisor',
            'act leader'       => 'leader',
            'act direktur'     => 'direktur',
            'staff'            => 'leader',
            'act jp'           => 'jp',
            'act gm'           => 'gm',
            'gm'               => 'gm',
            'direktur'         => 'direktur',
            'director'         => 'direktur',
            'vpd'              => 'vpd',
            'president'        => 'president',
        ];

        $position = strtolower($this->position);
        return $aliasMap[$position] ?? $position;
    }


    // mapping create authorization
    public function getCreateAuth()
    {
        return match ($this->getNormalizedPosition()) {
            'jp', 'operator', 'leader' => 2,
            'supervisor', 'manager', 'gm', 'direktur' => 1,
            default => 0,
        };
    }

    public function getFirstApproval()
    {
        return match ($this->getNormalizedPosition()) {
            'jp', 'operator', 'leader', 'manager' => 3,
            'supervisor', 'gm', 'direktur' => 2,
            'vpd' => 1,
            default => 0,
        };
    }

    public function getFinalApproval()
    {
        return match ($this->getNormalizedPosition()) {
            'jp', 'operator', 'leader' => 4,
            'supervisor', 'manager', 'gm', 'direktur' => 3,
            'vpd', 'president' => 1,
            default => 0,
        };
    }


    // Get average from 3 last performance appraisal history by employee_id
    public static function getLast3Performance($employee_id, $year)
    {
        $performance = PerformanceAppraisalHistory::where('employee_id', $employee_id)
            ->whereIn(DB::raw('YEAR(date)'), [$year, $year - 1, $year - 2, $year - 3])
            ->get();

        return $performance;
    }

    // astra grade conversion
    public function conversion()
    {
        return $this->hasOne(GradeConversion::class, 'aisin_grade', 'grade');
    }

    public function getAstraGradeAttribute()
    {
        $conversion = $this->conversion;
        return $conversion ? $conversion->astra_grade : null;
    }
    public function availableCompetencies()
    {
        $department = $this->departments->first();

        if (!$department) {
            return collect();
        }

        return Competency::where('department_id', $department->id)
            ->where('position', $this->position)
            ->whereNotIn('id', $this->employeeCompetencies->pluck('competency_id'))
            ->get();
    }
    public function getBagianAttribute()
    {
        $position = $this->getNormalizedPosition() ?? '';
        $position = strtolower($position);
        return match (true) {
            Str::contains($position, 'president') => 'President',
            Str::contains($position, 'direktur') => $this->leadingPlant->name ?? 'Tidak Ada Plant',
            Str::contains($position, 'gm') => $this->leadingDivision->name ?? 'Tidak Ada Divisi',
            Str::contains($position, 'manager') => $this->leadingDepartment->name ?? 'Tidak Ada Departemen',
            default => $this->department->name
                ?? 'Tidak Ada Departemen',
        };
    }

    public function isDireksi(): bool
    {
        $direksiPositions = ['President', 'VPD'];  // Tambah posisi direksi lain kalau perlu
        return in_array($this->position, $direksiPositions);
    }

    public function isActGM(): bool
    {
        $actGmPositions = ['Act GM'];
        return in_array($this->position, $actGmPositions);
    }

    public function ipps()
    {
        return $this->hasMany(Ipp::class);
    }

    public function ippComments()
    {
        return $this->hasMany(IppComment::class);
    }

    public function getFormattedDateAttribute()
    {
        return $this->aisin_entry_date
            ? Carbon::parse($this->aisin_entry_date)->format('d M Y')
            : null;
    }
    public function getFormattedBirthAttribute()
    {
        return $this->birthday_date
            ? Carbon::parse($this->birthday_date)->format('d M Y')
            : null;
    }
}
