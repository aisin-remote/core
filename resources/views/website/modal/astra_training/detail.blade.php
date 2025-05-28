<div class="modal fade" id="detailAstraTrainingModal" tabindex="-1" aria-labelledby="detailAstraTrainingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> {{-- modal-xl karena tampilkan table --}}
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title fw-bold" id="detailAstraTrainingModalLabel">Astra Training History Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($astraTrainings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="bg-light fw-semibold">
                                <tr>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">Program</th>
                                    <th class="text-center">ICT</th>
                                    <th class="text-center">Project</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @foreach ($astraTrainings as $astraTraining)
                                    <tr>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($astraTraining->date_end)->format('Y') }}
                                        </td>
                                        <td class="text-center">{{ $astraTraining->program }}</td>
                                        <td class="text-center">{{ $astraTraining->ict_score }}</td>
                                        <td class="text-center">{{ $astraTraining->project_score }}</td>
                                        <td class="text-center">{{ $astraTraining->total_score }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light-warning" data-bs-toggle="modal"
                                                data-bs-target="#editAstraTrainingModal{{ $astraTraining->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteAstraTrainingModal{{ $astraTraining->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                @else
                    <div class="text-center text-muted">No Astra Training data available.</div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
