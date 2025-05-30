<div class="modal fade" id="detailEducationModal" tabindex="-1" aria-labelledby="detailEducationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-primary">
                <h5 class="modal-title">Educational Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @forelse ($educations as $edu)
                    <div class="mb-3 d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">{{ $edu->educational_level }} - {{ $edu->major }}</div>
                            <div class="text-muted small">{{ $edu->institute }}</div>
                            <div class="text-muted small">
                                {{ \Carbon\Carbon::parse($edu->start_date)->format('Y') }} -
                                {{ \Carbon\Carbon::parse($edu->end_date)->format('Y') }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-light-warning edit-education-btn" data-bs-toggle="modal"
                                data-bs-target="#editEducationModal{{ $edu->id }}"
                                data-education-id="{{ $edu->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-light-danger delete-education-btn" data-bs-toggle="modal"
                                data-bs-target="#deleteEducationModal{{ $edu->id }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    @unless ($loop->last)
                        <hr class="my-2">
                    @endunless
                @empty
                    <div class="text-center text-muted">No data available.</div>
                @endforelse
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
