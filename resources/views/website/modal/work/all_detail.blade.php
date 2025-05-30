<div class="modal fade" id="allExperienceDetailModal" tabindex="-1" aria-labelledby="detailExperienceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-primary">
                <h5 class="modal-title" id="detailExperienceModalLabel">Work Experience Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @forelse ($workExperiences as $exp)
                    <div class="mb-3 d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">{{ $exp->department }}</div>
                            <div class="text-muted small">{{ $exp->position }}</div>
                            <div class="text-muted small">
                                {{ \Carbon\Carbon::parse($exp->start_date)->format('Y') }} -
                                {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('Y') : 'Present' }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-light-warning edit-experience-btn" data-bs-toggle="modal"
                                data-bs-target="#editExperienceModal{{ $exp->id }}"
                                data-experience-id="{{ $exp->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-light-danger delete-experience-btn" data-bs-toggle="modal"
                                data-bs-target="#deleteExperienceModal{{ $exp->id }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    @unless ($loop->last)
                        <hr class="my-2">
                    @endunless
                @empty
                    <div class="text-center text-muted">No work experience data available.</div>
                @endforelse
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
