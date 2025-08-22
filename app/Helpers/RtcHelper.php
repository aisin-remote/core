<?php

namespace App\Helpers;

use App\Models\Rtc;
use Carbon\Carbon;

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

    /**
     * Format kandidat (Short/Mid/Long Term) + sisipkan info RTC terbaru per term
     * untuk area/id yang telah di-set via setAreaContext().
     *
     * @param  mixed        $candidate  Relasi Employee? boleh null
     * @param  string|null  $term       'short' | 'mid' | 'long' (WAJIB agar override RTC tepat)
     */
    public static function formatCandidate($candidate, ?string $term = null): array
    {
        // Nilai dasar dari kandidat relasi (kalau ada)
        $base = [
            'name'  => $candidate->name  ?? '-',
            'grade' => $candidate->grade ?? '-',
            'age'   => ($candidate && $candidate->birthday_date) ? Carbon::parse($candidate->birthday_date)->age : '-',
        ];

        // Jika belum ada context area atau term tak diberikan, kembalikan base + meta default
        if (!self::$area || !self::$areaId || !$term) {
            return array_merge($base, [
                'rtc_status'     => 0,
                'rtc_term'       => $term ?? 'short',
                'rtc_created_at' => null,
                'rtc_id'         => null,
            ]);
        }

        // Ambil RTC terbaru untuk konteks area & term
        $rtc = self::latestRtcForTerm(self::$area, self::$areaId, $term);

        // Jika tidak ada RTC, tetap kembalikan base + meta default
        if (!$rtc) {
            return array_merge($base, [
                'rtc_status'     => 0,
                'rtc_term'       => $term,
                'rtc_created_at' => null,
                'rtc_id'         => null,
            ]);
        }

        // Override field kandidat dari employee di RTC (jika ada)
        $emp = $rtc->employee;
        if ($emp) {
            $base['name']  = $emp->name  ?: $base['name'];
            $base['grade'] = $emp->grade ?: $base['grade'];
            $base['age']   = ($emp->birthday_date) ? Carbon::parse($emp->birthday_date)->age : $base['age'];
        }

        // created_at RTC — gunakan updated_at jika ada (sering jadi "waktu terakhir set")
        $createdText = null;
        $ts = $rtc->updated_at ?? $rtc->created_at;
        if ($ts) {
            $createdText = Carbon::parse($ts)->timezone('Asia/Jakarta')->format('d M Y, H:i');
        }

        return array_merge($base, [
            'rtc_status'     => (int) ($rtc->status ?? 0),
            'rtc_term'       => $term,
            'rtc_created_at' => $createdText,
            'rtc_id'         => $rtc->id,
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
        // normalisasi kecil-besar huruf sesuai data
        // (di controller kamu sudah kirim 'Division' / 'department' / 'section' / 'sub_section')
        return Rtc::with('employee')
            ->where('area', $area)
            ->where('area_id', $areaId)
            ->where('term', $term)         // 'short' | 'mid' | 'long'
            ->orderByDesc('updated_at')    // pakai updated_at supaya terlihat paling baru
            ->orderByDesc('created_at')
            ->first();
    }
}
