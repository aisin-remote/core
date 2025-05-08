<div class="modal fade" id="detailExternalTrainingModal" tabindex="-1" aria-labelledby="detailExternalTrainingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> {{-- modal-xl untuk tabel lebih lebar --}}
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title fw-bold" id="detailExternalTrainingModalLabel">External Training History Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($externalTrainings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="bg-light fw-semibold">
                                <tr>
                                    <th>Training</th>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">Vendor</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @foreach ($externalTrainings as $training)
                                    <tr>
                                        <td>{{ $training->program }}</td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($training->date_end)->format('Y') }}</td>
                                        <td class="text-center">{{ $training->vendor }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted">No External Training data available.</div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
