<div class="modal fade" id="detailPromotionHistoryModal" tabindex="-1" aria-labelledby="detailPromotionHistoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title fw-bold" id="detailPromotionHistoryModalLabel">Promotion History Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($promotionHistories->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="bg-light fw-semibold">
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">Previous Grade</th>
                                    <th class="text-center">Previous Position</th>
                                    <th class="text-center">Current Grade</th>
                                    <th class="text-center">Current Position</th>
                                    <th class="text-center">Last Promotion Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($promotionHistories as $promotion)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $promotion->previous_grade }}</td>
                                        <td class="text-center">{{ $promotion->previous_position }}</td>
                                        <td class="text-center">{{ $promotion->current_grade }}</td>
                                        <td class="text-center">{{ $promotion->current_position }}</td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($promotion->last_promotion_date)->format('j F Y, g:i A') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">No promotion history available.</p>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
