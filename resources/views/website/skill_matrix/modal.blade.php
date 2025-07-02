<div class="modal fade" id="uploadEvidenceModal-{{ $id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Evidence Skill Matrix</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      {{-- Body: file & status --}}
      <div class="modal-body">
        @if($file)
          @php
            $filename    = \Illuminate\Support\Str::afterLast($file, '/');
            $extension   = pathinfo($filename, PATHINFO_EXTENSION);
            $url         = asset('storage/' . $file);
            $exists = Storage::disk('public')->exists($file);
            if ($exists) {
                $sizeInBytes = Storage::disk('public')->size($file);
                $sizeReadable = number_format($sizeInBytes / 1024, 2) . ' KB';
                $uploadedAt = date('d M Y H:i', Storage::disk('public')->lastModified($file));
            } else {
                $sizeReadable = 'Unknown';
                $uploadedAt = 'Unknown';
            }
            $iconClass = match(strtolower($extension)) {
                'pdf'  => 'far fa-file-pdf text-danger',
                'doc','docx' => 'far fa-file-word text-primary',
                'png','jpg','jpeg','gif' => 'far fa-file-image text-success',
                'zip' => 'far fa-file-archive text-warning',
                default => 'far fa-file-alt text-secondary'
            };
          @endphp
      
          <div class="card mb-3">
            <div class="row g-0 align-items-center">
              <div class="col-auto ps-3">
                <i class="{{ $iconClass }} fa-3x"></i>
              </div>
              <div class="col">
                <div class="card-body py-2">
                  <h6 class="card-title mb-1">{{ $filename }}</h6>
                  <p class="card-text mb-0">
                    <small class="text-muted">
                      {{ $sizeReadable }} &middot; Uploaded at {{ $uploadedAt }}
                    </small>
                  </p>
                </div>
              </div>
              <div class="col-auto pe-3">
                <a href="{{ $url }}" class="btn btn-outline-primary btn-sm" download>
                  <i class="fas fa-download"></i> Download
                </a>
              </div>
            </div>
          </div>
        @else
          <p class="text-center text-muted fst-italic">
            Belum ada evidence yang di-upload.
          </p>
        @endif
      
        @if($act == 1)
          <div class="alert alert-success text-center">
            ✅ Evidence telah di-approve.
          </div>
        @endif

        @php
          $ec = \App\Models\EmployeeCompetency::with('evidenceHistories.actor')
                  ->findOrFail($id);
        @endphp

        @if($ec->evidenceHistories->count())
          <hr>
          <h6>Riwayat Persetujuan</h6>
          <ul class="list-group">
            @foreach($ec->evidenceHistories as $hist)
              <li class="list-group-item py-2">
                {{ $hist->created_at->format('d M Y H:i') }} –
                <strong>{{ $hist->actor->name }}</strong>
                {{ $hist->action === 'approve' ? 'approved' : 'unapproved' }}.
              </li>
            @endforeach
          </ul>
        @endif
      </div>

      {{-- Form upload hanya jika belum di-approve --}}
      @if($act == 0)
      <form action="{{ route('skillMatrix.uploadEvidence', $id) }}"
            method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="evidence_file" class="form-label">
              Choose File
            </label>
            <input type="file" 
                   name="evidence_file" 
                   id="evidence_file" 
                   class="form-control" 
                   required
                   accept="*">
            <div class="form-text">
              Max. File Size 10MB.
            </div>
            @error('evidence_file')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Batal
          </button>
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cek jika ada flash message sukses
            const successMessage = "{{ session('success') }}";
            
            if(successMessage) {
                // Tutup modal yang masih terbuka
                const openModal = document.querySelector('.modal.show');
                if(openModal) {
                    const modal = bootstrap.Modal.getInstance(openModal);
                    modal.hide();
                }
        
                // Tampilkan Sweet Alert
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: successMessage,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
        </script>
      @endif

    </div>
  </div>
</div>