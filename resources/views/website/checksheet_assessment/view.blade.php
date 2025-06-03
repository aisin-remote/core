@extends('layouts.root.main')

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header text-white">
      <h3>Lihat Penilaian Checksheet - {{ $competency->name }}</h3>
    </div>
    <div class="card-body">
      <div class="checksheet-container">
        @foreach($checksheets as $key => $cs)
        @php
          $assessment = $existingAssessments->get($cs->id);
        @endphp
        <div class="checksheet-item mb-4 p-4 border rounded">
          <div class="row mb-3">
            <div class="col">
              <h5 class="mb-0">{{ $key+1 }}. {{ $cs->name }}</h5>
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
                  <label class="form-check-label">
                    {{ $label }}
                  </label>
                </div>
                @endforeach
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col">
              <textarea class="form-control" 
                rows="2" 
                readonly>{{ $assessment->description ?? '' }}</textarea>
            </div>
          </div>
        </div>
        @endforeach
      </div>

      <div class="mt-4">
        <a href="{{ route('employeeCompetencies.index') }}" class="btn btn-secondary px-4">
          <i class="bi bi-arrow-left-circle me-2"></i>Kembali
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
  </style>
@endsection