<div class="modal fade" id="deleteEducationModal{{ $education->id }}" tabindex="-1"
    aria-labelledby="deleteEducationModalLabel{{ $education->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEducationModalLabel{{ $education->id }}">
                    Delete Educaction History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('education.destroy', $education->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus
                        <strong>{{ $education->educational_level }} -
                            {{ $education->major }}</strong> dari riwayat
                        pendidikan?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
