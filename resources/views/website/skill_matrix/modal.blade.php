<div class="modal fade" id="uploadEvidenceModal-{{ $id }}" tabindex="-1" aria-labelledby="uploadEvidenceLabel-{{ $id }}" aria-hidden="true">
    <div class="modal-dialog">
      <form action="{{ route('skillMatrix.uploadEvidence', $id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="uploadEvidenceLabel-{{ $id }}">Upload Evidence</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="evidence_file" class="form-label">Choose File (pdf/jpg/png/docx)</label>
              <input type="file" name="evidence_file" id="evidence_file" class="form-control" required>
              @error('evidence_file')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Upload</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  