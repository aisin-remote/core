@extends('layouts.root.main')

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header">
      <h3 class="mb-0">
        Evaluasi Kompetensi: {{ $employeeCompetency->competency->name }}
        <span class="badge bg-light text-dark fs-6 ms-2">
          {{ $employeeCompetency->position }}
        </span>
      </h3>
      
      @if($hasScores)
        <div class="mt-2">
          @if($isPassed)
            <span class="badge bg-success fs-6">Lulus</span>
          @else
            <span class="badge bg-warning text-dark fs-6">Belum Lulus</span>
          @endif
          <span class="ms-2 fs-6">Total Nilai: <strong>{{ $percentage }}%</strong></span>
        </div>
      @endif
    </div>
    
    <div class="card-body">
      @if($employeeCompetency->act == 2)
        <div class="alert alert-success">
          <h4 class="alert-heading">Evaluasi Selesai!</h4>
          <p>Anda telah menyelesaikan evaluasi ini dengan nilai yang memuaskan.</p>
        </div>
      @endif
      
      @if($hasScores && !$isPassed)
        <div class="alert alert-warning">
          <h4 class="alert-heading">Perlu Perbaikan</h4>
          <p>Total nilai Anda <strong>{{ $percentage }}%</strong>, di bawah persyaratan kelulusan (70%).</p>
          <p class="mb-0">Silakan perbaiki jawaban Anda berdasarkan catatan evaluator.</p>
        </div>
      @endif

      <form 
        id="evaluation-form" 
        method="POST" 
        action="{{ route('evaluation.store') }}" 
        enctype="multipart/form-data"
      >
        @csrf
        <input type="hidden" name="employee_competency_id" value="{{ $employeeCompetency->id }}">

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

              @if($showScores)
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Nilai:</label>
                  <div class="d-flex align-items-center">
                    <div class="score-display bg-light rounded p-2 text-center" style="width: 50px;">
                      <span class="fw-bold fs-5 {{ $eval->score >= 4 ? 'text-success' : ($eval->score <= 2 ? 'text-danger' : 'text-warning') }}">
                        {{ $eval->score ?? '-' }}
                      </span>
                    </div>
                    <div class="ms-3">
                      @if($eval->score >= 4)
                        <span class="badge bg-success">Baik</span>
                      @elseif($eval->score <= 2)
                        <span class="badge bg-danger">Perlu Perbaikan</span>
                      @else
                        <span class="badge bg-warning text-dark">Cukup</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
              @endif

              <div class="row mb-3">
                <div class="col">
                  <label class="form-label fw-bold">Jawaban Anda:</label>
                  <textarea 
                    class="form-control" 
                    name="answer[{{ $eval->id }}]" 
                    rows="3" 
                    placeholder="Tulis jawaban Anda di sini..."
                    {{ $allowEdit ? '' : 'readonly' }}
                    style="{{ $allowEdit ? '' : 'background-color: #f8f9fa;' }}"
                  >{{ $eval->answer ?? '' }}</textarea>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Upload Bukti:</label>
                  @if($eval->file)
                    <div class="mb-2">
                      <a href="{{ asset('storage/'.$eval->file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i> Lihat File
                      </a>
                    </div>
                  @endif
                  <input 
                    type="file" 
                    class="form-control" 
                    name="file[{{ $eval->id }}]"
                    {{ $allowEdit ? '' : 'disabled' }}
                  >
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-between border-top pt-3">
          <a href="{{ route('skillMatrix.index') }}" class="btn btn-secondary px-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali
          </a>
          
          @if($allowEdit)
            <button type="submit" class="btn btn-success px-4">
              <i class="fas fa-save me-2"></i>Simpan Evaluasi
            </button>
          @else
            @if($employeeCompetency->act == 1)
              <div class="alert alert-info mb-0 py-2">
                <i class="fas fa-info-circle me-2"></i> Jawaban telah disimpan dan sedang menunggu penilaian.
              </div>
            @elseif($employeeCompetency->act == 2)
              <div class="alert alert-success mb-0 py-2">
                <i class="fas fa-check-circle me-2"></i> Evaluasi telah selesai dan Anda dinyatakan lulus.
              </div>
            @endif
          @endif
        </div>
      </form>
    </div>
  </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
  Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    confirmButtonText: 'OK'
  });
</script>
@endif

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

@if($allowEdit)
<script>
  document.getElementById('evaluation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let unanswered = [];
    document.querySelectorAll('textarea[name^="answer"]').forEach(textarea => {
      if (!textarea.value.trim()) {
        const num = textarea.closest('.evaluation-item').querySelector('.badge').textContent;
        unanswered.push(num);
      }
    });

    if (unanswered.length) {
      Swal.fire({
        icon: 'warning',
        title: 'Pertanyaan Belum Terjawab',
        html: `Pertanyaan berikut belum dijawab: <strong>${unanswered.join(', ')}</strong>`,
        confirmButtonText: 'OK'
      });
      return;
    }

    Swal.fire({
      title: 'Konfirmasi Penyimpanan',
      text: 'Apakah Anda yakin ingin menyimpan evaluasi ini?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Simpan',
      cancelButtonText: 'Batal'
    }).then(result => {
      if (result.isConfirmed) this.submit();
    });
  });
</script>
@endif

<style>
  .evaluation-item {
    background-color: #f8faff;
    border: 1px solid #d1e0ff;
    border-radius: 8px;
    transition: all 0.3s ease;
  }
  .evaluation-item:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,0.1);
    background-color: #edf3ff;
  }
  .card-header { 
    border-radius: 8px 8px 0 0!important; 
    background-color: #f8fafc;
  }
  .score-display {
    border: 2px solid #dee2e6;
    min-width: 50px;
  }
</style>
@endsection