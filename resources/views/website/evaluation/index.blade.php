@extends('layouts.root.main')

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header">
      <h3 class="mb-0">
        Evaluation Competency : {{ $competency->name }}
        <span class="badge bg-light text-dark fs-6 ms-2">
          {{ $employeeCompetency->position }}
        </span>
      </h3>
    </div>
    <div class="card-body">
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
                    {{ $eval->question_text }}
                  </h5>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col">
                  <label class="form-label fw-bold">Answer :</label>
                  <textarea 
                    class="form-control" 
                    name="answer[{{ $eval->id }}]" 
                    rows="3" 
                    placeholder="Tulis jawaban Anda di sini..."
                  >{{ old("answer.{$eval->id}", $eval->answer) }}</textarea>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Upload Evidence:</label>
                  <input 
                    type="file" 
                    class="form-control" 
                    name="file[{{ $eval->id }}]"
                  >
                </div>
                <div class="col-md-6">
                  @if($eval->file)
                    <label class="form-label fw-bold">File Terupload:</label>
                    <div>
                      <a 
                        href="{{ asset('storage/'.$eval->file) }}" 
                        target="_blank" 
                        class="btn btn-sm btn-outline-primary"
                      >
                        <i class="fas fa-download me-1"></i> Download File
                      </a>
                    </div>
                  @endif
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Penilaian:</label>
                  <select class="form-select" name="score[{{ $eval->id }}]">
                    @for($i=1; $i<=4; $i++)
                      <option 
                        value="{{ $i }}" 
                        {{ ($eval->score == $i) ? 'selected' : '' }}
                      >
                        {{ $i }} - 
                        @switch($i)
                          @case(1) Tidak Memenuhi @break
                          @case(2) Cukup Memenuhi @break
                          @case(3) Memenuhi @break
                          @case(4) Sangat Memenuhi @break
                        @endswitch
                      </option>
                    @endfor
                  </select>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-between border-top pt-3">
          {{-- KEMBALI KE SKILL MATRIX INDEX --}}
          <a href="{{ route('skillMatrix.index') }}" class="btn btn-secondary px-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali
          </a>
          <button type="submit" class="btn btn-success px-4">
            <i class="fas fa-save me-2"></i>Simpan Evaluasi
          </button>
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

<script>
  document.getElementById('evaluation-form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validasi semua pertanyaan terjawab
    let unanswered = [];
    document.querySelectorAll('textarea[name^="answer"]').forEach(textarea => {
      if (!textarea.value.trim()) {
        const num = textarea.closest('.evaluation-item')
                             .querySelector('.badge')
                             .textContent;
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

    // Konfirmasi penyimpanan
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
  .card-header { border-radius: 8px 8px 0 0!important; }
</style>
@endsection
