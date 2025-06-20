@extends('layouts.root.main')

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header text-white">
      @php
          // Hitung ulang status kelulusan
          $totalChecksheets = $checksheets->count();
          $score3Count = 0;
          
          foreach ($existingAssessments as $assessment) {
              if ($assessment->score == 3) {
                  $score3Count++;
              }
          }
          
          $percentage = $totalChecksheets > 0 ? ($score3Count / $totalChecksheets) * 100 : 0;
          $isPassed = $percentage >= 70;
      @endphp

      <h3>Lihat Penilaian Checksheet - {{ $competency->name }}</h3>
      
      {{-- TAMBAHKAN ALERT UNTUK STATUS LULUS --}}
      @if($isPassed)
        <div class="alert alert-success mt-3">
          <i class="bi bi-check-circle me-2"></i>
          <strong>Status: LOLOS</strong> | 
          <strong>Persentase Skor "Selalu":</strong> {{ number_format($percentage, 2) }}% ({{ $score3Count }}/{{ $totalChecksheets }})
          <br>
          <small class="d-block mt-1">
            <strong>Syarat Kelulusan:</strong> Minimal 70% skor "Selalu"
          </small>
        </div>
      @else
        <div class="alert alert-warning mt-3">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <strong>Status: BELUM LOLOS</strong> | 
          <strong>Persentase Skor "Selalu":</strong> {{ number_format($percentage, 2) }}% ({{ $score3Count }}/{{ $totalChecksheets }})
          <br>
          <small class="d-block mt-1">
            <strong>Syarat Kelulusan:</strong> Minimal 70% skor "Selalu"
          </small>
          
          <div class="mt-2">
            Belum memenuhi syarat kelulusan. Silakan perbaiki nilai karyawan 
            @if($isAuthorizedSuperior)
              <a href="{{ route('checksheet-assessment.index', [
                'employeeId' => $employeeCompetency->employee_id,
                'competencyId' => $competency->id
              ]) }}" class="btn btn-warning btn-sm mt-2 me-2">
                Improve Assessment
              </a>
            @endif

            {{-- Link ke history spesifik kompetensi --}}
            <a href="{{ route('checksheet-assessment.competency-history', [
              'employeeId' => $employeeCompetency->employee_id,
              'competencyId' => $competency->id
            ]) }}" class="btn btn-info btn-sm mt-2">
              <i class="bi bi-clock-history me-1"></i> Lihat Histori Kompetensi
            </a>
          </div>
        </div>
      @endif
    </div>
    <div class="card-body">
      <div class="checksheet-container">
        @foreach($checksheets as $key => $cs)
        @php
          $assessment = $existingAssessments->get($cs->id);
          $isScore3 = $assessment && $assessment->score == 3;
        @endphp
        <div class="checksheet-item mb-4 p-4 border rounded {{ $isScore3 ? 'border-success border-2' : '' }}">
          <div class="row mb-3">
            <div class="col">
              <h5 class="mb-0">
                {{ $key+1 }}. {{ $cs->name }}
                @if($isScore3)
                  <span class="badge bg-success ms-2">Selalu</span>
                @endif
              </h5>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Penilaian:</label>
              <div class="d-flex gap-4">
                @foreach([1 => 'Tidak pernah', 2 => 'Kadang-kadang', 3 => 'Selalu'] as $value => $label)
                <div class="form-check">
                  <input class="form-check-input" type="radio" 
                    disabled
                    {{ $assessment && $assessment->score == $value ? 'checked' : '' }}>
                  <label class="form-check-label {{ $value == 3 ? 'fw-bold' : '' }}">
                    {{ $label }}
                  </label>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>

      <div class="mt-4">
        <a href="{{ route('employeeCompetencies.index', ['company' => request()->route('company', 'aii')]) }}" class="btn btn-secondary px-4 ms-2">
          <i class="bi bi-arrow-left-circle me-2"></i>Back
        </a>
      </div>          
    </div>
  </div>
</div>
<style>
    .checksheet-item {
      background-color: #f8f9fa;
      transition: background-color 0.2s;
    }

    .checksheet-item:hover {
      background-color: #f8f9fa;
    }

    .form-check-input[disabled] {
      opacity: 1;
      cursor: default;
    }

    .form-check-input[disabled]:checked {
      background-color: #0d6efd;
      border-color: #0d6efd;
    }

    textarea[readonly] {
      background-color: #fff;
      opacity: 1;
      color: #212529;
    }
    
    .border-success {
      border-color: #198754 !important;
    }
    
    .fw-bold {
      font-weight: 600 !important;
    }
  </style>
@endsection