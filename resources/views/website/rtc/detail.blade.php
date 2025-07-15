@extends('layouts.root.blank')

@section('title', $title ?? 'RTC')
@section('breadcrumbs', $title ?? 'RTC')

@section('main')
    <div class="rtc-container">

        @include('components.rtc-card', [
            'title' => $main['title'],
            'person' => $main['person'],
            'shortTerm' => $main['shortTerm'],
            'midTerm' => $main['midTerm'],
            'longTerm' => $main['longTerm'],
            'cardClass' => 'rtc-main-card',
        ])

        {{-- Display managers and supervisors --}}
        <div class="rtc-level-container">
            @foreach ($managers as $manager)
                @include('components.rtc-card', [
                    'title' => $manager['title'] ?? '',
                    'person' => $manager['person'] ?? '',
                    'shortTerm' => $manager['shortTerm'] ?? '',
                    'midTerm' => $manager['midTerm'] ?? '',
                    'longTerm' => $manager['longTerm'] ?? '',
                    'cardClass' => "rtc-manager-card {$manager['colorClass']}",
                ])
            @endforeach
        </div>

        <div class="rtc-supervisor-container">
            @foreach ($managers as $manager)
                @if (!empty($manager['supervisors']))
                    <div class="rtc-supervisor-group">
                        @foreach ($manager['supervisors'] as $spv)
                            @include('components.rtc-card', [
                                'title' => $spv['title'],
                                'person' => $spv['person'],
                                'shortTerm' => $spv['shortTerm'],
                                'midTerm' => $spv['midTerm'],
                                'longTerm' => $spv['longTerm'],
                                'cardClass' => "rtc-supervisor-card {$spv['colorClass']}",
                            ])
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>

    </div>

    {{-- Styles tetap di sini seperti sebelumnya --}}
    @include('website.rtc.style.index')
@endsection
