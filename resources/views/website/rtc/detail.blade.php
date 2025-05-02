@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@section('main')
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid ">

            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-fluid ">
                <div class="d-flex justify-content-center align-items-center bg-light">
                    <div class="card shadow-lg rounded-2 p-5 text-center"
                        style="max-width: 600px; width: 100%; font-size: 18px;">
                        <div class="mb-4 text-muted pb-3 border-bottom display-7">
                            Dept : <strong class="text-dark">{{ $data->name ?? '-' }}</strong>
                        </div>

                        <!-- Dynamic section based on filter -->
                        @php
                            use Carbon\Carbon;

                            // Mapped fields based on the filter type
                            $field = match ($filter) {
                                'department' => 'manager',
                                'section' => 'supervisor',
                                'sub_section' => 'leader',
                                default => null,
                            };

                            // Accessing related model
                            $person = $data->{$field} ?? null;

                            // Prepare the fallback values
                            $name = $person?->name ?? '-';
                            $grade = $person?->grade ?? '-';

                            // Calculate age from birthday
                            $age = $person && $person->birthday_date ? Carbon::parse($person->birthday_date)->age : '-';

                            // Hardcoded or calculated LOS & LCP if available
                            $los = $person ? '13' : '-';
                            $lcp = $person ? '-' : '-';

                            // Candidate plans
                            $shortPerson = $data?->short;
                            $midPerson = $data?->mid;
                            $longPerson = $data?->long;

                            $shortTerm = $shortPerson?->name ?? '-';
                            $shortGrade = $shortPerson?->grade ?? '-';
                            $shortAge =
                                $shortPerson && $shortPerson->birthday_date
                                    ? Carbon::parse($shortPerson->birthday_date)->age
                                    : '-';

                            $midTerm = $midPerson?->name ?? '-';
                            $midGrade = $midPerson?->grade ?? '-';
                            $midAge =
                                $midPerson && $midPerson->birthday_date
                                    ? Carbon::parse($midPerson->birthday_date)->age
                                    : '-';

                            $longTerm = $longPerson?->name ?? '-';
                            $longGrade = $longPerson?->grade ?? '-';
                            $longAge =
                                $longPerson && $longPerson->birthday_date
                                    ? Carbon::parse($longPerson->birthday_date)->age
                                    : '-';
                        @endphp

                        <div class="mb-5 pt-2" style="margin-bottom: 50px !important">
                            <strong class="fs-2">{{ $name }}</strong> - <strong
                                class="fs-2">[{{ $grade }}]</strong>
                        </div>

                        <div class="text-start mb-5" style="font-size: 20px;">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-semibold text-muted">Age</span>
                                <span class="fw-bold">{{ $age }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-semibold text-muted">LOS</span>
                                <span class="fw-bold">{{ $los }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold text-muted">LCP</span>
                                <span class="fw-bold">{{ $lcp }}</span>
                            </div>
                        </div>

                        <div class="text-start" style="font-size: 20px;">
                            <div class="fw-bold mb-4 fs-2">Candidates:</div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-semibold text-muted">S/T</span>
                                <span class="fw-bold fs-3">{{ $shortTerm }} ({{ $shortGrade }},
                                    {{ $shortAge }})</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-semibold text-muted">M/T</span>
                                <span class="fw-bold fs-3">{{ $midTerm }} ({{ $midGrade }},
                                    {{ $midAge }})</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold text-muted">L/T</span>
                                <span class="fw-bold fs-3">{{ $longTerm }} ({{ $longGrade }},
                                    {{ $longAge }})</span>
                            </div>
                            <div class="mt-4 text-muted" style="font-size: 16px;">
                                (gol, usia, HAV)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
