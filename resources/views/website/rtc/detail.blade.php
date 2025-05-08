@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@section('main')
    <div class="d-flex justify-content-center align-items-center flex-column">
        {{-- Card utama --}}
        <div class="card rounded-2 p-3 text-center mb-5 " style="max-width: 260px; width: 100%; font-size: 16px;">
            <div class="mb-3 text-muted pb-2 border-bottom display-7">
                <span class="text-muted">{{ $data->name ?? '-' }}</span>
            </div>

            @php
                use Carbon\Carbon;

                $filter = 'division';

                $field = match ($filter) {
                    'division' => 'gm',
                    'department' => 'manager',
                    'section' => 'supervisor',
                    'sub_section' => 'leader',
                    default => null,
                };

                $person = $data->{$field} ?? null;

                $name = $person?->name ?? '-';
                $grade = $person?->grade ?? '-';
                $age = $person && $person->birthday_date ? Carbon::parse($person->birthday_date)->age : '-';
                $los = $person ? '13' : '-';
                $lcp = $person ? '-' : '-';

                $shortPerson = $data?->short;
                $midPerson = $data?->mid;
                $longPerson = $data?->long;

                $shortTerm = $shortPerson?->name ?? '-';
                $shortGrade = $shortPerson?->grade ?? '-';
                $shortAge =
                    $shortPerson && $shortPerson->birthday_date ? Carbon::parse($shortPerson->birthday_date)->age : '-';

                $midTerm = $midPerson?->name ?? '-';
                $midGrade = $midPerson?->grade ?? '-';
                $midAge = $midPerson && $midPerson->birthday_date ? Carbon::parse($midPerson->birthday_date)->age : '-';

                $longTerm = $longPerson?->name ?? '-';
                $longGrade = $longPerson?->grade ?? '-';
                $longAge =
                    $longPerson && $longPerson->birthday_date ? Carbon::parse($longPerson->birthday_date)->age : '-';
            @endphp

            <div class="mb-4 pt-2">
                <strong class="fs-4">{{ $name }}</strong> - <strong class="fs-5">[{{ $grade }}]</strong>
            </div>

            <div class="text-start mb-4" style="font-size: 16px;">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold text-muted">Age</span>
                    <span class="fw-bold">{{ $age }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold text-muted">LOS</span>
                    <span class="fw-bold">{{ $los }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold text-muted">LCP</span>
                    <span class="fw-bold">{{ $lcp }}</span>
                </div>
            </div>

            <div class="text-start" style="font-size: 16px;">
                <div class="fw-bold mb-3 fs-4">Candidates:</div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold text-muted">S/T</span>
                    <span class="fw-bold fs-5">{{ $shortTerm }} ({{ $shortGrade }}, {{ $shortAge }})</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold text-muted">M/T</span>
                    <span class="fw-bold fs-5">{{ $midTerm }} ({{ $midGrade }}, {{ $midAge }})</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold text-muted">L/T</span>
                    <span class="fw-bold fs-5">{{ $longTerm }} ({{ $longGrade }}, {{ $longAge }})</span>
                </div>
                <div class="mt-3 text-muted" style="font-size: 14px;">
                    (gol, usia, HAV)
                </div>
            </div>
        </div>

        {{-- Struktur horizontal: Manager dan Supervisor --}}
        <div class="row">
            @foreach ($bawahans as $manager)
                @php
                    $shortPerson = $manager?->planning?->short;
                    $midPerson = $manager?->planning?->mid;
                    $longPerson = $manager?->planning?->long;

                    $shortTerm = $shortPerson?->name ?? '-';
                    $shortGrade = $shortPerson?->grade ?? '-';
                    $shortAge =
                        $shortPerson && $shortPerson->birthday_date
                            ? Carbon::parse($shortPerson->birthday_date)->age
                            : '-';

                    $midTerm = $midPerson?->name ?? '-';
                    $midGrade = $midPerson?->grade ?? '-';
                    $midAge =
                        $midPerson && $midPerson->birthday_date ? Carbon::parse($midPerson->birthday_date)->age : '-';

                    $longTerm = $longPerson?->name ?? '-';
                    $longGrade = $longPerson?->grade ?? '-';
                    $longAge =
                        $longPerson && $longPerson->birthday_date
                            ? Carbon::parse($longPerson->birthday_date)->age
                            : '-';
                @endphp
                <!-- Card Manager -->
                <div class="col">
                    @if (empty($manager->supervisors) || count($manager->supervisors) == 0)
                        <div class="card p-3" style="width: 260px;">
                            <div class="mb-2 text-muted border-bottom pb-2 text-center">{{ $manager->department->name }}
                            </div>
                            <div class="fw-bold fs-5 text-center">{{ $manager->name }} ({{ $manager->grade ?? '-' }})
                            </div>

                            <div class="text-start mt-3 mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Age</span>
                                    <span class="fw-bold">
                                        {{ $manager->birthday_date ? \Carbon\Carbon::parse($manager->birthday_date)->age : '-' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">LOS</span>
                                    <span class="fw-bold">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">LCP</span>
                                    <span class="fw-bold">-</span>
                                </div>
                            </div>

                            <div class="text-start">
                                <div class="fw-bold mb-3">Candidates:</div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-semibold text-muted">S/T</span>
                                    <span class="fw-bold">{{ $shortTerm }} ({{ $shortGrade }},
                                        {{ $shortAge }})</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-semibold text-muted">M/T</span>
                                    <span class="fw-bold">{{ $midTerm }} ({{ $midGrade }},
                                        {{ $midAge }})</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-semibold text-muted">L/T</span>
                                    <span class="fw-bold">{{ $longTerm }} ({{ $longGrade }},
                                        {{ $longAge }})</span>
                                </div>
                                <div class="mt-3 text-muted" style="font-size: 14px;">
                                    (gol, usia, HAV)
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="row">
            @foreach ($bawahans as $manager)
                @php
                    $shortPerson = $manager?->planning?->short;
                    $midPerson = $manager?->planning?->mid;
                    $longPerson = $manager?->planning?->long;

                    $shortTerm = $shortPerson?->name ?? '-';
                    $shortGrade = $shortPerson?->grade ?? '-';
                    $shortAge =
                        $shortPerson && $shortPerson->birthday_date
                            ? Carbon::parse($shortPerson->birthday_date)->age
                            : '-';

                    $midTerm = $midPerson?->name ?? '-';
                    $midGrade = $midPerson?->grade ?? '-';
                    $midAge =
                        $midPerson && $midPerson->birthday_date ? Carbon::parse($midPerson->birthday_date)->age : '-';

                    $longTerm = $longPerson?->name ?? '-';
                    $longGrade = $longPerson?->grade ?? '-';
                    $longAge =
                        $longPerson && $longPerson->birthday_date
                            ? Carbon::parse($longPerson->birthday_date)->age
                            : '-';
                @endphp
                <!-- Card Supervisor/Section Head -->
                <div class="col">
                    @if ($manager->supervisors && count($manager->supervisors))
                        <div class="mt-4">
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                @foreach ($manager->supervisors as $spv)
                                    <div class="card p-3" style="width: 260px;">
                                        <div class="mb-2 text-muted border-bottom pb-2 text-center">
                                            {{ $spv->leadingSection->name }}
                                        </div>
                                        <div class="fw-bold fs-5 text-center">{{ $spv->name }}
                                            ({{ $spv->grade ?? '-' }})
                                        </div>

                                        <div class="text-start mt-3 mb-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">Age</span>
                                                <span class="fw-bold">
                                                    {{ $spv->birthday_date ? \Carbon\Carbon::parse($spv->birthday_date)->age : '-' }}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">LOS</span>
                                                <span class="fw-bold">-</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">LCP</span>
                                                <span class="fw-bold">-</span>
                                            </div>
                                        </div>

                                        <div class="text-start">
                                            <div class="fw-bold mb-3">Candidates:</div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="fw-semibold text-muted">S/T</span>
                                                <span class="fw-bold">{{ $shortTerm }} ({{ $shortGrade }},
                                                    {{ $shortAge }})</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="fw-semibold text-muted">M/T</span>
                                                <span class="fw-bold">{{ $midTerm }} ({{ $midGrade }},
                                                    {{ $midAge }})</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="fw-semibold text-muted">L/T</span>
                                                <span class="fw-bold">{{ $longTerm }} ({{ $longGrade }},
                                                    {{ $longAge }})</span>
                                            </div>
                                            <div class="mt-3 text-muted" style="font-size: 14px;">
                                                (gol, usia, HAV)
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection
