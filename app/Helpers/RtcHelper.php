<?php

namespace App\Helpers;

use Carbon\Carbon;

class RtcHelper
{
    public static function formatPerson($person)
    {
        return [
            'name'  => $person->name ?? '-',
            'grade' => $person->grade ?? '-',
            'age'   => $person && $person->birthday_date ? Carbon::parse($person->birthday_date)->age : '-',
            'los'   => $person ? '13' : '-',
            'lcp'   => $person ? '-' : '-'
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
