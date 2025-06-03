@php
    // Default values if not provided
    $title = $title ?? '';
    $person = $person ?? [
        'name' => '-',
        'grade' => '-',
        'age' => '-',
        'los' => '-',
        'lcp' => '-',
    ];
    $shortTerm = $shortTerm ?? ['name' => '-', 'grade' => '-', 'age' => '-'];
    $midTerm = $midTerm ?? ['name' => '-', 'grade' => '-', 'age' => '-'];
    $longTerm = $longTerm ?? ['name' => '-', 'grade' => '-', 'age' => '-'];
    $cardClass = $cardClass ?? '';
@endphp

<div class="{{ $cardClass }}">
    <div class="rtc-header">
        <h3
            class="text-center {{ $cardClass === 'rtc-main-card' ? 'rtc-title' : ($cardClass === 'rtc-manager-card' ? 'rtc-department' : 'rtc-section') }}">
            {{ $title }}
        </h3>
    </div>

    <div class="rtc-current-person">
        <h4 class="rtc-person-name">{{ $person['name'] }} <span class="rtc-person-grade">[{{ $person['grade'] }}]</span>
        </h4>

        <div class="rtc-person-details">
            <div class="rtc-detail-item">
                <span class="rtc-detail-label">Age</span>
                <span class="rtc-detail-value">{{ $person['age'] }}</span>
            </div>
            <div class="rtc-detail-item">
                <span class="rtc-detail-label">LOS</span>
                <span class="rtc-detail-value">{{ $person['los'] }}</span>
            </div>
            <div class="rtc-detail-item">
                <span class="rtc-detail-label">LCP</span>
                <span class="rtc-detail-value">{{ $person['lcp'] }}</span>
            </div>
        </div>
    </div>

    <div class="rtc-candidates">
        <h4 class="rtc-section-title">Candidates:</h4>
        <div class="rtc-candidate-list">
            <div class="rtc-candidate-item">
                <span class="rtc-candidate-label">S/T</span>
                <span class="rtc-candidate-value">{{ $shortTerm['name'] }} ({{ $shortTerm['grade'] }},
                    {{ $shortTerm['age'] }})</span>
            </div>
            <div class="rtc-candidate-item">
                <span class="rtc-candidate-label">M/T</span>
                <span class="rtc-candidate-value">{{ $midTerm['name'] }} ({{ $midTerm['grade'] }},
                    {{ $midTerm['age'] }})</span>
            </div>
            <div class="rtc-candidate-item">
                <span class="rtc-candidate-label">L/T</span>
                <span class="rtc-candidate-value">{{ $longTerm['name'] }} ({{ $longTerm['grade'] }},
                    {{ $longTerm['age'] }})</span>
            </div>
        </div>
        <div class="rtc-note">(gol, usia, HAV)</div>
    </div>
</div>
