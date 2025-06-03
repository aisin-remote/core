@extends('layouts.root.main')

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header">
      <h3>Checksheet Assessment - {{ $competency->name }}</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('checksheet-assessment.store') }}">
        @csrf
        <input type="hidden" name="competency_id" value="{{ $competency->id }}">
        
        <input type="hidden" name="employee_competency_id" value="{{ $employeeCompetency->id }}">
        <div class="checksheet-container">
          @foreach($checksheets as $key => $cs)
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
                  <div class="form-check">
                    <input class="form-check-input" type="radio" 
                      name="score[{{ $cs->id }}]" 
                      value="1" 
                      id="score-{{ $cs->id }}-1">
                    <label class="form-check-label" for="score-{{ $cs->id }}-1">
                      Tidak pernah
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" 
                      name="score[{{ $cs->id }}]" 
                      value="2" 
                      id="score-{{ $cs->id }}-2">
                    <label class="form-check-label" for="score-{{ $cs->id }}-2">
                      Kadang - kadang
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" 
                      name="score[{{ $cs->id }}]" 
                      value="3" 
                      id="score-{{ $cs->id }}-3">
                    <label class="form-check-label" for="score-{{ $cs->id }}-3">
                      Selalu
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col">
                <textarea name="description[{{ $cs->id }}]" 
                  class="form-control" 
                  rows="2" 
                  placeholder="Keterangan..."></textarea>
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-2"></i>Simpan Penilaian
          </button>
          
          <a href="{{ route('employeeCompetencies.index') }}" class="btn btn-secondary px-4 ms-2">
            <i class="bi bi-arrow-left-circle me-2"></i>Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location.href = "{{ route('employeeCompetencies.index') }}";
    });
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('beforeunload', function(e) {
        if(document.querySelector('form').checkValidity()) {
            return undefined;
        }
        e.preventDefault();
        e.returnValue = '';
    });
});
</script>
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