<div class="modal fade" id="alldetailAppraisalModal" tabindex="-1" aria-labelledby="detailAppraisalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title" id="detailAppraisalModalLabel">Performance Appraisal Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @forelse ($performanceAppraisals as $appraisal)
                    <div class="mb-3">
                        <div class="fw-bold">Score: {{ $appraisal->score }}</div>
                        <div class="text-muted small">
                            {{ \Carbon\Carbon::parse($appraisal->date)->format('d M Y') }}
                        </div>
                        @if ($appraisal->notes)
                            <div class="text-gray-700 small mt-1">
                                {{ $appraisal->notes }}
                            </div>
                        @endif
                    </div>
                    @unless ($loop->last)
                        <hr class="my-2">
                    @endunless
                @empty
                    <div class="text-center text-muted">No performance appraisal data available.</div>
                @endforelse
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
