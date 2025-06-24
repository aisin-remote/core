@extends('layouts.root.main')

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header">
      <h3>Checksheet Assessment - {{ $competency->name }}</h3>
    </div>
    <div class="card-body">
      {{-- Alert jika ini adalah pengulangan tes --}}
      @if($employeeCompetency->act == 1)
      @endif      
      <div class="mb-4">
        <a href="{{ route('checksheet-assessment.competency-history', ['employeeId' => $employeeCompetency->employee_id, 'competencyId' => $competency->id]) }}" 
           class="btn btn-info">
          <i class="bi bi-clock-history me-2"></i>Lihat Histori Tidak Lulus
        </a>
      </div>
      <form id="assessment-form" method="POST" action="{{ route('checksheet-assessment.store') }}">
        @csrf
        <input type="hidden" name="employee_competency_id" value="{{ $employeeCompetency->id }}">

        <div class="checksheet-container">
          @foreach($checksheets as $key => $cs)
          @php
            $existingScore = isset($existingAssessments) ? ($existingAssessments[$cs->id]->score ?? null) : null;
            $existingDesc = isset($existingAssessments) ? ($existingAssessments[$cs->id]->description ?? null) : null;
            $isFailed = $existingScore && $existingScore < 3; // Tandai yang perlu perbaikan
          @endphp
          <div class="checksheet-item mb-4 p-4 border rounded {{ $isFailed ? 'border-danger border-2' : '' }}" style="position: relative;">
            @if($isFailed)
              <div class="badge bg-danger position-absolute top-0 start-0 mt-2 ms-2">
                Perlu Perbaikan
              </div>
            @endif

            <div class="row mb-3">
              <div class="col">
                <h5 class="mb-0">{{ $key + 1 }}. {{ $cs->name }}</h5>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col">
                <label class="form-label">Penilaian:</label>
                <div class="d-flex gap-4">
                  @for($score = 1; $score <= 3; $score++)
                    @php
                      $label = $score === 1 ? 'Tidak pernah' : ($score === 2 ? 'Kadang - kadang' : 'Selalu');
                    @endphp
                    <div class="form-check">
                      <input class="form-check-input" type="radio"
                             name="score[{{ $cs->id }}]"
                             value="{{ $score }}"
                             id="score-{{ $cs->id }}-{{ $score }}"
                             {{ $existingScore == $score ? 'checked' : '' }}>
                      <label class="form-check-label" for="score-{{ $cs->id }}-{{ $score }}">
                        {{ $label }}
                      </label>
                    </div>
                  @endfor
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-2"></i>Simpan Penilaian
          </button>
          <a href="{{ route('employeeCompetencies.index', ['company' => request()->route('company', 'aii')]) }}" class="btn btn-secondary px-4 ms-2">
            <i class="bi bi-arrow-left-circle me-2"></i>Back
          </a>        
        </div>
      </form>
    </div>
  </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Session Alerts --}}
@if(session('error'))
<script>
  Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '{{ session('error') }}',
    confirmButtonText: 'OK'
  });
</script>
@endif

@if(session('success'))
<script>
  Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    confirmButtonText: 'OK'
  }).then(() => {
    window.location.href = "{{ route('employeeCompetencies.index', ['company' => request()->query('company')]) }}";
  });
</script>
@endif

{{-- Validasi & Konfirmasi sebelum submit --}}
<script>
  document.getElementById('assessment-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const items = document.querySelectorAll('.checksheet-item');
    let invalid = [];

    items.forEach(item => {
      const nameAttr = item.querySelector('input[type=radio]').name;
      const matches = nameAttr.match(/score\[(\d+)\]/);
      if (!matches) return;
      const csId = matches[1];

      if (!item.querySelector(`input[name="score[${csId}]"]:checked`)) {
        invalid.push(csId);
      }
    });

    if (invalid.length > 0) {
      // Kalau masih ada yang kosong, warning seperti biasa
      Swal.fire({
        icon: 'warning',
        title: 'Oops...',
        text: 'Harap isi semua penilaian checksheet sebelum menyimpan!',
        confirmButtonText: 'OK'
      });
    } else {
      // Semua terisi → tanya konfirmasi dulu
      Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah kamu yakin ingin menyimpan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          // Jika user klik "Ya, Simpan", baru submit
          this.submit();
        }
      });
    }
  });
</script>

{{-- Optional: style hover & checked --}}
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
  .border-danger {
    border-color: #dc3545 !important;
  }
  .position-absolute {
    position: absolute;
  }
  .top-0 {
    top: 0;
  }
  .start-0 {
    left: 0;
  }
  .mt-2 {
    margin-top: 0.5rem;
  }
  .ms-2 {
    margin-left: 0.5rem;
  }
</style>
@endsection