<?php

namespace App\Helpers;

use Carbon\Carbon;

class RtcHelper
{
    public static function formatPerson($person)
    {
        $workExperience = collect($person->workExperiences)
            ->whereNull('end_date')
            ->where('start_date')
            ->first();

        return [
            'name'            => $person->name ?? '-',
            'photo'           => $person->photo ?? null,
            'grade'           => $person->grade ?? '-',
            'age'             => $person && $person->birthday_date ? Carbon::parse($person->birthday_date)->age : '-',
            'los'             => $person && $person->aisin_entry_date ? Carbon::parse($person->aisin_entry_date)->diffInYears(Carbon::now()) : '-',
            'lcp'             => $workExperience ? Carbon::parse($workExperience->start_date)->diffInYears(Carbon::now()) : '-',
            'position'        => $person->position ?? '-',
            'department'      => $person->department ?? '-',
            'work_experience' => $workExperience ? "{$workExperience->position} - {$workExperience->department}" : '-',
        ];
    }

    public static function formatCandidate($candidate)
    {
        return [
            'name'  => $candidate->name ?? '-',
            'grade' => $candidate->grade ?? '-',
            'age'   => $candidate && $candidate->birthday_date ? Carbon::parse($candidate->birthday_date)->age : '-',
        ];
    }
}
