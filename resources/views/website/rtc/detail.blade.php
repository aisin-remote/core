@extends('layouts.root.blank')

@section('title', $title ?? 'RTC')
@section('breadcrumbs', $title ?? 'RTC')

@section('main')
    <div class="rtc-container">
        @php
            use Carbon\Carbon;

            $departmentColors = [];

            function getDepartmentColorClass($departmentId)
            {
                static $colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7'];
                static $assigned = [];

                if (!isset($assigned[$departmentId])) {
                    $assigned[$departmentId] = $colors[count($assigned) % count($colors)];
                }

                return $assigned[$departmentId];
            }

            // Helper function to get person data
            function getPersonData($person)
            {
                return [
                    'name' => $person?->name ?? '-',
                    'grade' => $person?->grade ?? '-',
                    'age' => $person && $person->birthday_date ? Carbon::parse($person->birthday_date)->age : '-',
                    'los' => $person ? '13' : '-',
                    'lcp' => $person ? '-' : '-',
                ];
            }

            // Helper function to get candidate data
            function getCandidateData($candidate)
            {
                return [
                    'name' => $candidate?->name ?? '-',
                    'grade' => $candidate?->grade ?? '-',
                    'age' =>
                        $candidate && $candidate->birthday_date ? Carbon::parse($candidate->birthday_date)->age : '-',
                ];
            }

            // Get current person based on filter
            $field = match ($filter) {
                'division' => 'gm',
                'department' => 'manager',
                'section' => 'supervisor',
                'sub_section' => 'leader',
                default => null,
            };

            $currentPerson = getPersonData($data->{$field} ?? null);
            $shortTerm = getCandidateData($data?->short);
            $midTerm = getCandidateData($data?->mid);
            $longTerm = getCandidateData($data?->long);
        @endphp

        <!-- Main Card Component -->
        @include('components.rtc-card', [
            'title' => $data->name ?? '-',
            'person' => $currentPerson,
            'shortTerm' => $shortTerm,
            'midTerm' => $midTerm,
            'longTerm' => $longTerm,
            'cardClass' => 'rtc-main-card',
        ])

        <!-- Manager Level -->
        <div class="rtc-level-container">
            @foreach ($bawahans as $manager)
                @if ($manager->leadingDepartment)
                    @php
                        $departmentId = $manager->department->id;
                        $colorClass = getDepartmentColorClass($departmentId);

                        $shortTerm = getCandidateData($manager?->planning?->short);
                        $midTerm = getCandidateData($manager?->planning?->mid);
                        $longTerm = getCandidateData($manager?->planning?->long);
                    @endphp

                    @include('components.rtc-card', [
                        'title' => $manager->department->name,
                        'person' => getPersonData($manager),
                        'shortTerm' => $shortTerm,
                        'midTerm' => $midTerm,
                        'longTerm' => $longTerm,
                        'cardClass' => "rtc-manager-card $colorClass",
                    ])
                @endif
            @endforeach
        </div>

        <!-- Supervisor Level -->
        <div class="rtc-supervisor-container">
            @foreach ($bawahans as $manager)
                @if ($manager->supervisors && count($manager->supervisors))
                    <div class="rtc-supervisor-group">
                        @foreach ($manager->supervisors as $spv)
                            @php
                                $departmentId = $manager->department->id; // dari manager-nya
                                $colorClass = getDepartmentColorClass($departmentId);

                                $shortTerm = getCandidateData($manager?->planning?->short);
                                $midTerm = getCandidateData($manager?->planning?->mid);
                                $longTerm = getCandidateData($manager?->planning?->long);
                            @endphp

                            @include('components.rtc-card', [
                                'title' => $spv->leadingSection->name,
                                'person' => getPersonData($spv),
                                'shortTerm' => $shortTerm,
                                'midTerm' => $midTerm,
                                'longTerm' => $longTerm,
                                'cardClass' => "rtc-supervisor-card $colorClass",
                            ])
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <style>
        .rtc-header {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            padding: 12px;
        }

        /* Same CSS as before */
        .color-1 .rtc-header {
            background-color: #4CAF50;
            color: white !important;
            padding: 12px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;

        }

        .color-2 .rtc-header {
            background-color: #2196F3;
            color: white !important;
            padding: 12px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .color-3 .rtc-header {
            background-color: #FF9800;
            color: white !important;
            padding: 12px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .color-4 .rtc-header {
            background-color: #9C27B0;
            color: white !important;
            padding: 12px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .color-5 .rtc-header {
            background-color: #E91E63;
            color: white !important;
            padding: 12px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .color-6 .rtc-header {
            background-color: #00BCD4;
            color: white !important;
            padding: 12px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .color-7 .rtc-header {
            background-color: #FFC107;
            color: white !important;
            padding: 12px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .rtc-container {
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .rtc-main-card,
        .rtc-manager-card,
        .rtc-supervisor-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .rtc-main-card {
            border-top: #333 solid 4px;
            max-width: 300px;
            margin: 0 auto 30px;
        }

        .rtc-manager-card {
            width: 300px;
            margin: 10px;
        }

        .rtc-supervisor-card {
            width: 300px;
            margin: 8px;
        }

        .rtc-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .rtc-section,
        .rtc-department {
            color: white;
        }

        .rtc-title.rtc-section {
            color: #333;
            margin: 0;
        }

        .rtc-title {
            font-size: 1.5rem;
        }

        .rtc-department {
            font-size: 1.2rem;
        }

        .rtc-section {
            font-size: 1.1rem;
        }

        .rtc-current-person {
            margin-bottom: 20px;
        }

        .rtc-person-name {
            font-size: 1.2rem;
            margin: 0 0 10px 0;
            text-align: center;
        }

        .rtc-person-grade {
            color: #666;
        }

        .rtc-person-details {
            margin: 15px 0;
        }

        .rtc-detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .rtc-detail-label {
            color: #666;
        }

        .rtc-detail-value {
            font-weight: bold;
        }

        .rtc-candidates {
            margin-top: 20px;
        }

        .rtc-section-title {
            font-size: 1rem;
            margin-bottom: 10px;
            color: #444;
        }

        .rtc-candidate-list {
            margin-bottom: 10px;
        }

        .rtc-candidate-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .rtc-candidate-label {
            color: #666;
            font-weight: 500;
        }

        .rtc-candidate-value {
            font-weight: bold;
        }

        .rtc-note {
            font-size: 0.85rem;
            color: #999;
            text-align: right;
        }

        .rtc-level-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 30px;
        }

        .rtc-supervisor-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .rtc-supervisor-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 20px;
        }
    </style>
@endsection
