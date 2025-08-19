<?php

namespace App\Helpers;

use Carbon\Carbon;

class RtcHelper
{
    public static function formatPerson($person)
    {
        if (!$person) {
            return [
                'id' => null,
                'name' => '-',
                'photo' => null,
                'grade' => '-',
                'age' => '-',
                'los' => '',
                'lcp' => '',
                'position' => '-',
                'department' => '-',
                'work_experience' => '-',
            ];
        }

        $workExperience = collect($person?->workExperiences)
            ->whereNull('end_date')
            ->where('start_date')
            ->first();

        return [
            'id' => $person->id,
            'name' => $person->name ?? '-',
            'photo' => $person?->photo ? asset('storage/' . $person->photo) : null,
            'grade' => $person->grade ?? '-',
            'age' => $person && $person->birthday_date ? Carbon::parse($person->birthday_date)->age : '-',
            'los' => $person && $person->aisin_entry_date ? Carbon::parse($person->aisin_entry_date)->diffInYears(Carbon::now()) : '',
            'lcp' => $workExperience ? Carbon::parse($workExperience->start_date)->diffInYears(Carbon::now()) : '',
            'position' => $person->position ?? '-',
            'department' => $person->department ?? '-',
            'work_experience' => $workExperience ? "{$workExperience->position} - {$workExperience->department}" : '-',
        ];
    }

    public static function formatCandidate($candidate)
    {
        return [
            'name' => $candidate->name ?? '-',
            'grade' => $candidate->grade ?? '-',
            'age' => $candidate && $candidate->birthday_date ? Carbon::parse($candidate->birthday_date)->age : '-',
        ];
    }

    public static function isSamePerson($personA, $personB)
    {
        // If either person is null/empty, they're not the same
        if (!$personA || !$personB) {
            return false;
        }

        // If both have IDs, compare by ID (most reliable)
        if (isset($personA['id']) && isset($personB['id']) && $personA['id'] && $personB['id']) {
            return $personA['id'] === $personB['id'];
        }

        // Fallback to comparing by name and other attributes
        return $personA['name'] === $personB['name']
            && $personA['grade'] === $personB['grade']
            && $personA['position'] === $personB['position']
            && $personA['name'] !== '-'; // Don't consider empty records as same person
    }
}
