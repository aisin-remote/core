@extends('layouts.root.main')

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header">
      <h3 class="mb-0">
        Hasil Evaluasi: {{ $employeeCompetency->competency->name }}
      </h3>
      <div class="d-flex align-items-center mt-2">
        <span class="badge bg-light text-dark fs-6 me-3">
          {{ $employeeCompetency->employee->name }} ({{ $employeeCompetency->position }})
        </span>
        
        @if($isPassed)
          <span class="badge bg-success fs-6">Lulus</span>
        @else
          <span class="badge bg-warning text-dark fs-6">Belum Lulus</span>
        @endif
        
        <span class="ms-3 fs-6">Total Nilai: <strong>{{ $percentage }}%</strong></span>
      </div>
    </div>
    
    <div class="card-body">
      @if($isPassed)
        <div class="alert alert-success">
          <h4 class="alert-heading">Lulus!</h4>
          <p>Karyawan telah mencapai nilai kelulusan.</p>
        </div>
      @else
        <div class="alert alert-warning">
          <h4 class="alert-heading">Belum Lulus</h4>
          <p>Total nilai <strong>{{ $percentage }}%</strong>, di bawah persyaratan minimal 70%.</p>
          <p class="mb-0">Harap minta karyawan untuk memperbaiki jawaban kemudian nilai kembali.</p>
        </div>
      @endif

      @if(!$isPassed)
      <form 
        id="scoring-form" 
        method="POST" 
        action="{{ route('evaluation.updateScores', $employeeCompetency->id) }}"
      >
        @csrf
      @endif
      
        <div class="evaluation-container">
          @foreach($evaluations as $key => $eval)
            <div class="evaluation-item mb-4 p-4 border rounded bg-light">
              <div class="row mb-3">
                <div class="col">
                  <h5 class="mb-0">
                    <span class="badge bg-primary me-2">{{ $key + 1 }}</span>
                    {{ $eval->checksheet->question }}
                  </h5>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col">
                  <label class="form-label fw-bold">Jawaban Karyawan:</label>
                  <p class="form-control-static bg-white p-3 rounded">{{ $eval->answer }}</p>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Bukti:</label>
                  @if($eval->file)
                    <div>
                      <a href="{{ asset('storage/'.$eval->file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i> Lihat File
                      </a>
                    </div>
                  @else
                    <p class="text-muted">Tidak ada file diunggah</p>
                  @endif
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Penilaian:</label>
                  @if($isPassed)
                    <div class="d-flex align-items-center">
                      <div class="score-display bg-light rounded p-2 text-center" style="width: 50px;">
                        <span class="fw-bold fs-5 {{ $eval->score >= 4 ? 'text-success' : ($eval->score <= 2 ? 'text-danger' : 'text-warning') }}">
                          {{ $eval->score }}
                        </span>
                      </div>
                      <div class="ms-3">
                        @if($eval->score >= 4)
                          <span class="badge bg-success"></span>
                        @elseif($eval->score <= 2)
                          <span class="badge bg-danger"></span>
                        @else
                          <span class="badge bg-warning text-dark"></span>
                        @endif
                      </div>
                    </div>
                  @else
                    <select class="form-select" name="score[{{ $eval->id }}]">
                      @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ $eval->score == $i ? 'selected' : '' }}>
                          {{ $i }}
                        </option>
                      @endfor
                    </select>
                    <div class="mt-2">
                      <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        5 = Sangat Baik, 4 = Baik, 3 = Cukup, 2 = Kurang, 1 = Sangat Kurang
                      </small>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-between border-top pt-3">
            <a href="{{ route('employeeCompetencies.index', ['company' => request()->route('company', 'aii')]) }}" class="btn btn-secondary px-4 ms-2">
                <i class="bi bi-arrow-left-circle me-2"></i>Kembali
            </a>
            
          @if(!$isPassed)
            <button type="submit" class="btn btn-success px-4">
              <i class="fas fa-save me-2"></i>Simpan Penilaian
            </button>
          @endif
        </div>
      @if(!$isPassed)
      </form>
      @endif
    </div>
  </div>
</div>

@if(!$isPassed)
<script>
  document.getElementById('scoring-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let ungraded = [];
    document.querySelectorAll('select[name^="score"]').forEach(select => {
      if (!select.value) {
        const questionNum = select.closest('.evaluation-item').querySelector('.badge').textContent;
        ungraded.push(questionNum);
      }
    });
    
    if (ungraded.length) {
      Swal.fire({
        icon: 'warning',
        title: 'Penilaian Belum Lengkap',
        html: `Pertanyaan berikut belum dinilai: <strong>${ungraded.join(', ')}</strong>`,
        confirmButtonText: 'OK'
      });
      return;
    }
    
    this.submit();
  });
</script>
@endif

<style>
  .evaluation-item {
    background-color: #f8faff;
    border: 1px solid #d1e0ff;
    border-radius: 8px;
  }
  .card-header {
    border-radius: 8px 8px 0 0 !important;
  }
  .form-control-static {
    padding: 0.375rem 0.75rem;
    background-color: #f8f9fa;
    border-radius: 4px;
  }
  .score-display {
    border: 2px solid #dee2e6;
    min-width: 50px;
  }
</style>
@endsection