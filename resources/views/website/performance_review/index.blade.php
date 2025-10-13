@extends('layouts.root.main')

@section('title', $title ?? 'Performance Reviews')
@section('breadcrumbs', $title ?? 'Performance Reviews')

@push('custom-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        .container-xxl {
            max-width: 1360px
        }

        .table thead th {
            white-space: nowrap
        }

        .form-text.mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace
        }

        .spinner {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid rgba(0, 0, 0, .15);
            border-top-color: rgba(0, 0, 0, .45);
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl">
        <div class="card">
            <div class="card-header bg-light d-flex align-items-center justify-content-between">
                <h3 class="card-title m-0">Performance Reviews (My Records)</h3>
                <div class="d-flex gap-2">
                    @php $yearNow = now()->year; @endphp
                    <select id="year" class="form-select" style="width:120px">
                        @for ($y = $yearNow + 1; $y >= $yearNow - 5; $y--)
                            <option value="{{ $y }}" @selected($y == $yearNow)>{{ $y }}</option>
                        @endfor
                    </select>
                    <select id="period" class="form-select" style="width:140px">
                        <option value="">All Periods</option>
                        <option value="mid">Mid Year</option>
                        <option value="one" selected>One Year</option>
                    </select>
                    <button id="btnFilter" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <button id="btnCreate" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> New Review
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div id="tableWrap" class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Year</th>
                                <th>Period</th>
                                <th class="text-end">IPA Grand %</th>
                                <th class="text-end">Result Value</th>
                                <th class="text-end">B1</th>
                                <th class="text-end">B2</th>
                                <th class="text-end">Final</th>
                                <th>Grade</th>
                                <th style="width:120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rows">
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="spinner d-inline-block me-2"></div> Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="pager" class="d-flex justify-content-between align-items-center">
                    <div><span id="metaText" class="text-muted small"></span></div>
                    <div class="btn-group">
                        <button id="prevPage" class="btn btn-outline-secondary btn-sm">« Prev</button>
                        <button id="nextPage" class="btn btn-outline-secondary btn-sm">Next »</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Create / Edit --}}
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="reviewForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">New Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="reviewId">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                <input type="number" id="year_input" class="form-control" value="{{ $yearNow }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Period <span class="text-danger">*</span></label>
                                <select id="period_input" class="form-select" required>
                                    <option value="mid">Mid Year</option>
                                    <option value="one" selected>One Year</option>
                                </select>
                            </div>

                            {{-- ====== B1. PDCA & Values (7 aspek) ====== --}}
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <div class="fw-bold mb-2">B1. PDCA & Values — One Year</div>
                                    <div class="row g-2">
                                        @php
                                            $b1Aspects = [
                                                'Plan',
                                                'Do',
                                                'Check',
                                                'Action',
                                                'Teamwork',
                                                'Customer Focus',
                                                'Passion for Excellence',
                                            ];
                                        @endphp
                                        @foreach ($b1Aspects as $i => $label)
                                            <div class="col-md-6">
                                                <label class="form-label small">{{ $i + 1 }}.
                                                    {{ $label }}</label>
                                                <input type="number" step="0.01" min="1" max="5"
                                                    class="form-control b1-item" data-index="{{ $i }}"
                                                    placeholder="1–5">
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 small text-muted">Isi skala 1–5. Rata-rata akan dihitung otomatis.
                                    </div>
                                </div>
                            </div>

                            {{-- ====== B2. People Management (4 aspek) ====== --}}
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <div class="fw-bold mb-2">B2. People Management — One Year</div>
                                    <div class="row g-2">
                                        @php
                                            $b2Aspects = [
                                                'Getting Commitment on IPP',
                                                'Delegating',
                                                'Coaching & Counseling',
                                                'Developing Subordinate',
                                            ];
                                        @endphp
                                        @foreach ($b2Aspects as $i => $label)
                                            <div class="col-md-6">
                                                <label class="form-label small">{{ $i + 1 }}.
                                                    {{ $label }}</label>
                                                <input type="number" step="0.01" min="1" max="5"
                                                    class="form-control b2-item" data-index="{{ $i }}"
                                                    placeholder="1–5">
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 small text-muted">Isi skala 1–5. Rata-rata akan dihitung otomatis.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">A. Result</label>
                                <input type="number" step="0.01" id="a_grand_total_ipa" class="form-control"
                                    placeholder="e.g. 136.00" disabled>
                                <input type="hidden" id="a_grand_total_ipa_hidden" name="a_grand_total_ipa">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">B1. PDCA & Values (avg) <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="1" max="5" id="b1_pdca_values"
                                    class="form-control" required readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">B2. People Management (avg) <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="1" max="5" id="b2_people_mgmt"
                                    class="form-control" required readonly>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-warning py-2">
                                    <div class="small mb-1 fw-semibold">Catatan:</div>
                                    <ul class="small mb-0">
                                        <li><strong>Result</strong> dari Grand Total IPA.</li>
                                        <li><strong>Final Value</strong> & <strong>Grading</strong> dihitung di server
                                            sesuai <em>grade_astra</em> Anda.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="save-text">Save</span>
                            <span class="save-spinner spinner d-none align-middle"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const LIST_URL = "{{ url('/reviews/init') }}";
            const API_BASE = "{{ url('/reviews') }}";
            const IPA = @json($ipa); // bisa null

            let currentPage = 1,
                lastPage = 1;
            const $rows = $('#rows'),
                $metaText = $('#metaText');

            toastr.options = {
                positionClass: 'toast-bottom-right',
                timeOut: 3000
            };

            function qs() {
                const p = new URLSearchParams();
                const year = $('#year').val();
                const period = $('#period').val();
                if (year) p.set('year', year);
                if (period) p.set('period', period);
                p.set('page', currentPage);
                p.set('per_page', 10);
                return p.toString();
            }

            function badgeGrade(g) {
                const map = {
                    'IST': 'bg-dark text-white',
                    'BS+': 'bg-primary text-white',
                    'BS': 'bg-primary-subtle',
                    'B+': 'bg-info text-dark',
                    'B': 'bg-info-subtle',
                    'C+': 'bg-warning text-dark',
                    'C': 'bg-warning-subtle',
                    'K': 'bg-danger text-white'
                };
                return `<span class="badge ${map[g]||'bg-secondary'}">${g||'-'}</span>`;
            }
            const fmt = (n, d = 2) => (n == null ? '-' : Number(n).toLocaleString(undefined, {
                minimumFractionDigits: d,
                maximumFractionDigits: d
            }));

            function loadReviews() {
                $rows.html(
                    `<tr><td colspan="10" class="text-center py-5"><div class="spinner d-inline-block me-2"></div> Loading...</td></tr>`
                );
                $.getJSON(`${LIST_URL}?${qs()}`)
                    .done(res => {
                        if (res.status !== 'success') {
                            $rows.html(
                                `<tr><td colspan="10" class="text-danger text-center">Failed to load data.</td></tr>`
                            );
                            return;
                        }
                        const p = res.data,
                            items = p.data || [];
                        if (!items.length) {
                            $rows.html(
                                `<tr><td colspan="10" class="text-center text-muted py-5">No data.</td></tr>`);
                        } else {
                            let i = (p.from || 1);
                            const tr = items.map(row => {
                                return `<tr data-id="${row.id}">
                                    <td>${i++}</td>
                                    <td>${row.year}</td>
                                    <td class="text-uppercase">${row.period}</td>
                                    <td class="text-end">${fmt(row.result_percent)}</td>
                                    <td class="text-end">${fmt(row.result_value)}</td>
                                    <td class="text-end">${fmt(row.b1_pdca_values)}</td>
                                    <td class="text-end">${fmt(row.b2_people_mgmt)}</td>
                                    <td class="text-end fw-semibold">${fmt(row.final_value)}</td>
                                    <td>${badgeGrade(row.grading)}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-light btn-edit"><i class="fas fa-pen"></i></button>
                                            <button class="btn btn-light btn-delete text-danger"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>`;
                            }).join('');
                            $rows.html(tr);
                        }
                        currentPage = p.current_page;
                        lastPage = p.last_page;
                        $('#prevPage').prop('disabled', currentPage <= 1);
                        $('#nextPage').prop('disabled', currentPage >= lastPage);
                        $metaText.text(`Showing ${p.from||0}–${p.to||0} of ${p.total||0}`);
                    })
                    .fail(xhr => {
                        console.error(xhr?.responseJSON || xhr);
                        $rows.html(
                            `<tr><td colspan="10" class="text-danger text-center">Error loading data.</td></tr>`
                        );
                        toastr.error('Failed to load data');
                    });
            }

            // ===== util avg & bindings =====
            function avgFromInputs($els) {
                const vals = [];
                $els.each(function() {
                    const v = parseFloat($(this).val());
                    if (!isNaN(v)) vals.push(v);
                });
                if (!vals.length) return null;
                const sum = vals.reduce((a, b) => a + b, 0);
                return Math.round((sum / vals.length) * 100) / 100;
            }

            function updateAvgsUI() {
                const a1 = avgFromInputs($('.b1-item'));
                const a2 = avgFromInputs($('.b2-item'));
                $('#b1_pdca_values').val(a1 ?? '');
                $('#b2_people_mgmt').val(a2 ?? '');
            }
            $(document).on('input', '.b1-item, .b2-item', updateAvgsUI);

            function resetAspectInputs() {
                $('.b1-item, .b2-item').val('');
                $('#b1_pdca_values, #b2_people_mgmt').val('');
            }

            function setAGrand(v) {
                const val = (v == null || v === '') ? '' : Number(v);
                $('#a_grand_total_ipa').val(val);
                $('#a_grand_total_ipa_hidden').val(val);
            }

            function setAspectValues(kind /* 'b1' | 'b2' */ , values) {
                const arr = Array.isArray(values) ? values : [];
                const $targets = kind === 'b1' ? $('.b1-item') : $('.b2-item');
                $targets.each(function(i) {
                    const v = (arr[i] ?? '');
                    $(this).val(v === null ? '' : v);
                });
            }

            function prefillFromRow(row) {
                setAGrand(row?.result_percent ?? '');
                setAspectValues('b1', row?.b1_items || []);
                setAspectValues('b2', row?.b2_items || []);
                if (row?.b1_pdca_values != null) $('#b1_pdca_values').val(row.b1_pdca_values);
                if (row?.b2_people_mgmt != null) $('#b2_people_mgmt').val(row.b2_people_mgmt);
                if ($('#b1_pdca_values').val() === '' || $('#b2_people_mgmt').val() === '') {
                    updateAvgsUI();
                }
            }

            // ===== Modal handlers =====
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));

            function openCreate() {
                $('#modalTitle').text('New Review');
                $('#reviewId').val('');
                $('#year_input').val($('#year').val());
                $('#period_input').val($('#period').val() || 'one');
                setAGrand(IPA?.grand_total ?? '');
                resetAspectInputs();
                setAspectValues('b1', []);
                setAspectValues('b2', []);
                modal.show();
            }

            function openEdit(row) {
                $('#modalTitle').text('Edit Review');
                $('#reviewId').val(row.id);
                $('#year_input').val(row.year);
                $('#period_input').val(row.period);
                resetAspectInputs();
                prefillFromRow(row);
                modal.show();
            }

            $('#btnCreate').on('click', openCreate);
            $('#btnFilter').on('click', () => {
                currentPage = 1;
                loadReviews();
            });

            // Edit
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).closest('tr').data('id');
                $.getJSON(`${API_BASE}/${id}`)
                    .done(res => {
                        if (res.status !== 'success') return toastr.error('Failed to load review');
                        openEdit(res.data);
                    })
                    .fail(() => toastr.error('Failed to load review'));
            });

            // Delete
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).closest('tr').data('id');
                if (!confirm('Delete this review?')) return;
                $.ajax({
                    url: `${API_BASE}/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                }).done(res => {
                    if (res.status === 'success') {
                        toastr.success('Deleted');
                        loadReviews();
                    } else {
                        toastr.error(res.message || 'Failed to delete');
                    }
                }).fail(xhr => toastr.error(xhr?.responseJSON?.message || 'Failed to delete'));
            });

            // Submit
            $('#reviewForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#reviewId').val();

                const b1_items = $('.b1-item').map(function() {
                    const v = $(this).val();
                    return v === '' ? null : Number(v);
                }).get().filter(v => v !== null);

                const b2_items = $('.b2-item').map(function() {
                    const v = $(this).val();
                    return v === '' ? null : Number(v);
                }).get().filter(v => v !== null);

                const yearVal = Number($('#year_input').val());
                const per = $('#period_input').val();

                let url, method, data;

                if (id) {
                    url = `${API_BASE}/${id}`;
                    method = 'PUT';
                    data = {
                        year: yearVal,
                        period: per,
                        grand_total_pct: $('#a_grand_total_ipa_hidden').val() ?
                            Number($('#a_grand_total_ipa_hidden').val()) : null,
                        b1_items: b1_items,
                        b2_items: b2_items,
                        b1_pdca_values: $('#b1_pdca_values').val() ? Number($('#b1_pdca_values').val()) :
                            null,
                        b2_people_mgmt: $('#b2_people_mgmt').val() ? Number($('#b2_people_mgmt').val()) :
                            null,
                    };
                } else {
                    url = API_BASE;
                    method = 'POST';
                    data = {
                        year: yearVal,
                        period: {}
                    };
                    data.period[per] = {
                        a_grand_total_ipa: $('#a_grand_total_ipa_hidden').val() ?
                            Number($('#a_grand_total_ipa_hidden').val()) : null,
                        b1_items: b1_items,
                        b2_items: b2_items,
                        b1_pdca_values: $('#b1_pdca_values').val() ? Number($('#b1_pdca_values').val()) :
                            null,
                        b2_people_mgmt: $('#b2_people_mgmt').val() ? Number($('#b2_people_mgmt').val()) :
                            null,
                    };
                }

                $('.save-text').addClass('d-none');
                $('.save-spinner').removeClass('d-none');

                $.ajax({
                    url,
                    method,
                    data,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                }).done(res => {
                    if (res.status === 'success') {
                        toastr.success(id ? 'Updated' : 'Created');
                        modal.hide();
                        loadReviews();
                    } else {
                        toastr.error(res.message || 'Failed to save');
                    }
                }).fail(xhr => {
                    const j = xhr?.responseJSON;
                    if (j?.errors) toastr.error(Object.values(j.errors).flat().join('<br>'));
                    else toastr.error(j?.message || 'Failed to save');
                }).always(() => {
                    $('.save-text').removeClass('d-none');
                    $('.save-spinner').addClass('d-none');
                });
            });

            // init
            loadReviews();
        })();
    </script>
@endpush
