<div class="modal fade" id="deleteAstraTrainingModal{{ $astraTraining->id }}" tabindex="-1"
    aria-labelledby="deleteAstraLabel{{ $astraTraining->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAstraLabel{{ $astraTraining->id }}">
                    Delete Astra Training History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('astra_training.destroy', $astraTraining->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data
                        <strong>{{ $astraTraining->id }}</strong> dari
                        riwayat ini?
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
