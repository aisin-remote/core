<!-- Modal untuk Detail Pengalaman Kerja -->
<div class="modal fade" id="experienceModal{{ $experience->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $experience->position }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Department:</strong> {{ $experience->department }}</p>
                <p><strong>Period:</strong>
                    {{ \Illuminate\Support\Carbon::parse($experience->start_date)->format('d M Y') }}
                    -
                    {{ $experience->end_date ? \Illuminate\Support\Carbon::parse($experience->end_date)->format('d M Y') : 'Present' }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
