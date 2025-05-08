<!-- Modal Konfirmasi Delete -->
<div class="modal fade" id="deleteExperienceModal{{ $experience->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Work Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the experience
                    <strong>{{ $experience->position }}</strong> at
                    <strong>{{ $experience->company }}</strong>?
                </p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('work-experience.destroy', $experience->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
