<?php

namespace App\Helpers;

use App\Models\Hav;
use App\Models\HavQuadrant;
use App\Models\Rtc;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RtcHelper
{
    /**
     * Context area untuk query RTC latest-per-term.
     * Contoh: 'Division' | 'department' | 'section' | 'sub_section'
     */
    protected static ?string $area = null;
    protected static ?int $areaId = null;

    /**
     * Set konteks area + id untuk seluruh pemanggilan formatCandidate()
     * berikutnya (di dalam 1 node).
     *
     * @param  string  $area  Nilai kolom 'area' pada tabel rtc
     * @param  int     $areaId Nilai kolom 'area_id' pada tabel rtc
     */
    public static function setAreaContext(string $area, int $areaId): void
    {
        self::$area   = $area;
        self::$areaId = $areaId;
    }

    /**
     * Format data orang untuk kartu utama/manager/supervisor.
     */
    public static function formatPerson($person): array
    {
        if (!$person) {
            return [
                'id'              => null,
                'name'            => '-',
                'photo'           => null,
                'grade'           => '-',
                'age'             => '-',
                'los'             => '',
                'lcp'             => '',
                'position'        => '-',
                'department'      => '-',
                'work_experience' => '-',
            ];
        }

        $workExperience = collect($person?->workExperiences)
            ->whereNull('end_date')
            ->where('start_date')
            ->first();

        return [
            'id'              => $person->id,
            'name'            => $person->name ?? '-',
            'photo'           => $person?->photo ? asset('storage/' . $person->photo) : null,
            'grade'           => $person->grade ?? '-',
            'age'             => ($person && $person->birthday_date) ? Carbon::parse($person->birthday_date)->age : '-',
            'los'             => ($person && $person->aisin_entry_date) ? Carbon::parse($person->aisin_entry_date)->diffInYears(now()) : '',
            'lcp'             => $workExperience ? Carbon::parse($workExperience->start_date)->diffInYears(now()) : '',
            'position'        => $person->position ?? '-',
            'department'      => $person->department ?? '-',
            'work_experience' => $workExperience ? "{$workExperience->position} - {$workExperience->department}" : '-',
        ];
    }

    public static function formatCandidate($candidate, ?string $term = null): array
    {
        $base = [
            'name'  => '-',
            'grade' => '-',
            'age'   => '-',
        ];

        $rtc = null;

        if (self::$area && self::$areaId && $term) {
            $rtc = self::latestRtcForTerm(self::$area, self::$areaId, $term);
        }


        if ($rtc && $rtc->employee) {
            $emp = $rtc->employee;

            $base['name']  = $emp->name  ?? '-';
            $base['grade'] = $emp->grade ?? '-';
            $base['age']   = $emp->birthday_date
                ? Carbon::parse($emp->birthday_date)->age
                : '-';
        } elseif ($candidate) {
            $base['name']  = $candidate->name  ?? '-';
            $base['grade'] = $candidate->grade ?? '-';
            $base['age']   = $candidate->birthday_date
                ? Carbon::parse($candidate->birthday_date)->age
                : '-';
        }

        $humanAssests = [];
        $employeeId = null;

        if ($rtc && $rtc->employee_id) {
            $employeeId = $rtc->employee_id;
        } elseif ($candidate && isset($candidate->id)) {
            $employeeId = $candidate->id;
        }

        if ($employeeId) {
            $hav = Hav::where('employee_id', $employeeId)
                ->select('quadrant', 'year', DB::raw('COUNT(*) as count'))
                ->groupBy('quadrant', 'year')
                ->orderByDesc('year')
                ->get();

            if ($hav->isNotEmpty()) {
                $humanAssests = $hav->toArray();
            } else {
                $havQuadrants = HavQuadrant::where('employee_id', $employeeId)->get();
                $humanAssests = $havQuadrants->toArray();
            }
        }

        $createdText = null;
        if ($rtc) {
            $ts = $rtc->updated_at ?? $rtc->created_at;
            if ($ts) {
                $createdText = Carbon::parse($ts)
                    ->timezone('Asia/Jakarta')
                    ->format('d M Y, H:i');
            }
        }

        return array_merge($base, [
            'rtc_status'     => (int) ($rtc->status ?? 0),
            'rtc_term'       => $term ?? 'short',
            'rtc_created_at' => $createdText,
            'rtc_id'         => $rtc->id ?? null,
            'human_assets'   => $humanAssests
        ]);
    }

    /**
     * Bandingkan 2 person-array (hasil formatPerson) untuk menghindari duplikasi node.
     */
    public static function isSamePerson($personA, $personB): bool
    {
        if (!$personA || !$personB) {
            return false;
        }

        if (
            isset($personA['id'], $personB['id'])
            && $personA['id'] && $personB['id']
        ) {
            return (int) $personA['id'] === (int) $personB['id'];
        }

        return ($personA['name'] ?? null) === ($personB['name'] ?? null)
            && ($personA['grade'] ?? null) === ($personB['grade'] ?? null)
            && ($personA['position'] ?? null) === ($personB['position'] ?? null)
            && ($personA['name'] ?? '-') !== '-';
    }

    /**
     * Helper internal: cari RTC terbaru untuk area+area_id+term tertentu.
     */
    protected static function latestRtcForTerm(string $area, int $areaId, string $term): ?Rtc
    {
        $normalizedArea = strtolower($area);

        return Rtc::with('employee')
            ->whereRaw('LOWER(area) = ?', [$normalizedArea])
            ->where('area_id', $areaId)
            ->where('term', $term)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();
    }
}
