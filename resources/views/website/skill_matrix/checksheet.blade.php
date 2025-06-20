@extends('layouts.root.main')

@section('main')
@php
    // Pastikan variabel terdefinisi
    $score3Count = $score3Count ?? 0;
    $checksheetsCount = $checksheets->count() ?? 0;
@endphp

<div class="container">
  <div class="card">
    <div class="card-header">
      <h3>Assessment Checksheet – {{ $competency->name }}</h3>
    </div>
    <div class="card-body">
      {{-- Tampilkan status kelulusan --}}
      @if($isPassed)
        <div class="alert alert-success mb-4">
          <i class="bi bi-check-circle me-2"></i>
          <strong>Status: LOLOS</strong> | 
          <strong>Persentase Skor "Selalu":</strong> {{ number_format($percentage, 2) }}% ({{ $score3Count }}/{{ $checksheetsCount }})
          <br>
          <small class="d-block mt-1">
            <strong>Syarat Kelulusan:</strong> Minimal 70% skor "Selalu"
            <br>
            <strong>Percobaan ke-{{ $lastAttempt }}</strong>
          </small>
        </div>
      @else
        <div class="alert alert-warning mb-4">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <strong>Status: BELUM LOLOS</strong> | 
          <strong>Persentase Skor "Selalu":</strong> {{ number_format($percentage, 2) }}% ({{ $score3Count }}/{{ $checksheetsCount }})
          <br>
          <small class="d-block mt-1">
            <strong>Syarat Kelulusan:</strong> Minimal 70% skor "Selalu"
            <br>
            <strong>Percobaan ke-{{ $lastAttempt }}</strong>
          </small>
        </div>
      @endif

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
        <a href="{{ route('skillMatrix.index') }}" class="btn btn-secondary px-4">
          <i class="bi bi-arrow-left-circle me-2"></i>Kembali ke Skill Matrix
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
    background-color: #e9ecef;
  }
  .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
  }
  .border-success {
    border-color: #198754 !important;
    border-width: 2px !important;
  }
  .fw-bold {
    font-weight: 600 !important;
  }
  .badge {
    font-size: 0.85em;
    padding: 0.4em 0.6em;
  }
</style>
@endsection