@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@push('custom-css')
    <style>
        /* ===== Status Chip ===== */
        .status-chip {
            --bg: #eef2ff;
            --fg: #312e81;
            --bd: #c7d2fe;
            --dot: #6366f1;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .9rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: .9rem;
            line-height: 1;
            border: 1px solid var(--bd);
            background: var(--bg);
            color: var(--fg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .status-chip i {
            font-size: 1rem;
            opacity: .95
        }

        .status-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dot);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--dot) 20%, transparent)
        }

        .status-chip[data-status="approved"] {
            --bg: #ecfdf5;
            --fg: #065f46;
            --bd: #a7f3d0;
            --dot: #10b981
        }

        .status-chip[data-status="checked"],
        .status-chip[data-status="waiting"] {
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b
        }

        .status-chip[data-status="draft"] {
            --bg: #f8fafc;
            --fg: #334155;
            --bd: #e2e8f0;
            --dot: #94a3b8
        }

        .status-chip[data-status="not_created"],
        .status-chip[data-status="unknown"] {
            --bg: #f4f4f5;
            --fg: #27272a;
            --bd: #e4e4e7;
            --dot: #a1a1aa
        }

        @keyframes pulseDot {
            0% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 30%, transparent)
            }

            70% {
                box-shadow: 0 0 0 8px color-mix(in srgb, var(--dot) 0%, transparent)
            }

            100% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 0%, transparent)
            }
        }

        .status-chip[data-status="waiting"]::before {
            animation: pulseDot 1.25s infinite
        }

        @media (max-width:768px) {
            .status-chip {
                max-width: 210px
            }
        }
    </style>
@endpush

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Sukses!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ $table }} List</h3>
                        <div class="d-flex align-items-center">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Search ..."
                                style="width:200px;">
                            <button type="button" class="btn btn-primary me-3" id="searchButton">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                                data-kt-menu-placement="bottom-end">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th class="text-center">{{ $table }}</th>
                                    @if ($showPlanColumns)
                                        <th class="text-center">Short Term</th>
                                        <th class="text-center">Mid Term</th>
                                        <th class="text-center">Long Term</th>
                                    @endif
                                    @if ($showStatusColumn)
                                        <th class="text-center">Status</th>
                                    @endif
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($divisions as $division)
                                    @php
                                        // Guard variabel status/plan hanya saat kolomnya tampil
                                        $shortName = $showPlanColumns
                                            ? $division->short->name ?? ($division->st_name ?? null)
                                            : null;
                                        $midName = $showPlanColumns
                                            ? $division->mid->name ?? ($division->mt_name ?? null)
                                            : null;
                                        $longName = $showPlanColumns
                                            ? $division->long->name ?? ($division->lt_name ?? null)
                                            : null;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $division->name }}</td>

                                        @if ($showPlanColumns)
                                            <td class="text-center">{{ $shortName ?: __('not set') }}</td>
                                            <td class="text-center">{{ $midName ?: __('not set') }}</td>
                                            <td class="text-center">{{ $longName ?: __('not set') }}</td>
                                        @endif

                                        @if ($showStatusColumn)
                                            @php
                                                $badge = [
                                                    'text' => $division->overall_label ?? 'Not Set',
                                                    'data_status' => match ($division->overall_code ?? 'not_set') {
                                                        'approved' => 'approved',
                                                        'checked' => 'checked',
                                                        'submitted' => 'waiting',
                                                        'partial', 'complete_no_submit' => 'draft',
                                                        default => 'not_created',
                                                    },
                                                ];
                                            @endphp
                                            <td class="text-center">
                                                <span class="status-chip" data-status="{{ $badge['data_status'] }}">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    <span>{{ $badge['text'] }}</span>
                                                </span>
                                            </td>
                                        @endif

                                        <td class="text-center">
                                            {{-- Detail untuk level Company → ke list plant --}}
                                            @if ($table === 'Company')
                                                <a href="{{ route('rtc.list', ['id' => $division->id, 'filter' => $table]) }}"
                                                    class="btn btn-sm btn-info" title="View" target="_blank">
                                                    Preview
                                                </a>
                                                <a href="{{ route('rtc.list', ['level' => 'company', 'id' => $division->id]) }}"
                                                    class="btn btn-sm btn-primary" title="Detail">
                                                    Detail
                                                </a>
                                            @elseif ($table === 'Plant')
                                                <a class="btn btn-sm btn-info"
                                                    href="{{ route('rtc.list', ['id' => $division->id]) }}?filter=plant"
                                                    title="RTC Summary" target="_blank">
                                                    Preview
                                                </a>
                                                <a href="{{ route('rtc.list', ['level' => 'plant', 'id' => $division->id]) }}"
                                                    class="btn btn-sm btn-primary" title="Detail">
                                                    Detail
                                                </a>
                                            @else
                                                <a href="{{ route('rtc.list', ['id' => $division->id, 'filter' => $table]) }}"
                                                    class="btn btn-sm btn-info" title="View" target="_blank">
                                                    Preview
                                                </a>
                                                <a href="{{ route('rtc.list', ['id' => $division->id]) }}"
                                                    class="btn btn-sm btn-primary" title="Detail">
                                                    Detail
                                                </a>
                                                @if ($showPlanColumns)
                                                    <a href="#" class="btn btn-sm btn-success open-add-plan-modal"
                                                        data-id="{{ $division->id }}" data-bs-toggle="modal"
                                                        data-bs-target="#addPlanModal" title="Add">
                                                        Detail
                                                    </a>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        @php $colspan = 2 + ($showPlanColumns ? 3 : 0) + ($showStatusColumn ? 1 : 0) + 1; @endphp
                                        <td colspan="{{ $colspan }}" class="text-center text-muted">No data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add ditampilkan hanya jika kolom plan aktif --}}
    @if ($showPlanColumns)
        <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="addPlanForm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Plan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            {{-- area level yang sedang diisi --}}
                            <input type="hidden" name="filter" value="{{ strtolower($table) }}">
                            {{-- term hasil kalkulasi dari kandidat terpilih (short|mid|long) --}}
                            <input type="hidden" id="selected_term" name="term" value="">

                            <div class="mb-3">
                                <label for="kode_rtc" class="form-label">Kode RTC</label>
                                <select id="kode_rtc" class="form-select select2-in-modal">
                                    <option value="">-- Pilih Kode --</option>
                                    @foreach (['AS', 'S', 'SS', 'AM', 'M', 'SM', 'AGM', 'GM', 'SGM'] as $kode)
                                        <option value="{{ $kode }}">{{ $kode }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">
                                    Pilih target posisi (AS/S/SS/AM/M/SM/AGM/GM/SGM) untuk menarik kandidat dari ICP.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="candidate_select" class="form-label">Kandidat (otomatis dari ICP)</label>
                                <select id="candidate_select" class="form-select select2-in-modal">
                                    <option value="">-- Pilih kandidat --</option>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    Daftar sudah difilter berdasar ICP: posisi, level, dan plan_year (≥ tahun ini).
                                </small>
                            </div>

                            {{-- Panel info kandidat terpilih --}}
                            <div id="candidate_info" class="border rounded p-3 d-none">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-semibold" id="ci_name">-</div>
                                        <div class="text-muted small" id="ci_company">-</div>
                                    </div>
                                    <span id="ci_term_badge" class="badge rounded-pill text-uppercase"></span>
                                </div>
                                <hr class="my-2">
                                <div class="row g-2 small">
                                    <div class="col-6">Job Function: <span id="ci_job_function"
                                            class="fw-semibold">-</span></div>
                                    <div class="col-3">Level: <span id="ci_level" class="fw-semibold">-</span></div>
                                    <div class="col-3">Plan Year: <span id="ci_plan_year" class="fw-semibold">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" id="submitPlanBtn" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
@dd($showPlanColumns)

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if ($showPlanColumns)
        <script>
            let currentDivisionId = null; // area_id yang sedang dibuka modalnya
            const areaFilter = @json(strtolower($table)); // company/direksi/division/department/section/sub_section

            // buka modal
            $(document).on('click', '.open-add-plan-modal', function() {
                currentDivisionId = $(this).data('id');
                const $modal = $('#addPlanModal');
                // reset form
                $('#kode_rtc').val('').trigger('change');
                $('#candidate_select').empty().append('<option value="">-- Pilih kandidat --</option>').trigger(
                    'change');
                $('#candidate_info').addClass('d-none');
                $('#selected_term').val('');
                if ($modal.length) $modal.modal('show');
            });

            // init select2 setiap modal tampil
            $('#addPlanModal').on('shown.bs.modal', function() {
                $(this).find('.select2-in-modal').each(function() {
                    const $sel = $(this);
                    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
                    $sel.select2({
                        dropdownParent: $('#addPlanModal'),
                        width: '100%',
                        placeholder: '-- Select --',
                        allowClear: true
                    });
                });
            });

            // helper: render badge term
            function renderTermBadge(term) {
                const $b = $('#ci_term_badge');
                $b.removeClass('bg-success bg-warning bg-secondary');
                if (term === 'short') $b.addClass('bg-success');
                else if (term === 'mid') $b.addClass('bg-warning');
                else $b.addClass('bg-secondary');
                $b.text((term || '-').toUpperCase());
            }

            // saat Kode RTC berubah → load kandidat
            $('#kode_rtc').on('change', function() {
                const kode = $(this).val();
                const $cand = $('#candidate_select');
                $('#selected_term').val('');
                $('#candidate_info').addClass('d-none');
                $cand.empty().append('<option value="">Loading...</option>').trigger('change');

                if (!kode) {
                    $cand.empty().append('<option value="">-- Pilih kandidat --</option>').trigger('change');
                    return;
                }

                $.getJSON('{{ route('rtc.candidates') }}', {
                    kode: kode
                }, function(resp) {
                    $cand.empty().append('<option value="">-- Pilih kandidat --</option>');
                    if (resp.status === 'ok') {
                        resp.data.forEach(row => {
                            console.log(row);

                            // simpan payload ringkas di data-attributes
                            const opt = $('<option>')
                                .val(row.employee_id)
                                .text(
                                    `${row.name} • ${row.job_function} • ${row.level} • ${row.plan_year} (${row.term.toUpperCase()})`
                                )
                                .attr('data-term', row.term)
                                .attr('data-name', row.name)
                                .attr('data-company', row.company_name)
                                .attr('data-job_function', row.job_function)
                                .attr('data-level', row.level)
                                .attr('data-year', row.plan_year);
                            $cand.append(opt);
                        });
                    }
                    $cand.trigger('change');
                }).fail(function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire('Error', 'Gagal memuat kandidat ICP', 'error');
                    $cand.empty().append('<option value="">-- Pilih kandidat --</option>').trigger('change');
                });
            });

            // saat kandidat dipilih → tampilkan panel info + set hidden term
            $('#candidate_select').on('change', function() {
                const $opt = $(this).find('option:selected');
                const id = $(this).val();
                if (!id) {
                    $('#candidate_info').addClass('d-none');
                    $('#selected_term').val('');
                    return;
                }

                const term = $opt.data('term') || '';
                $('#selected_term').val(term);

                $('#ci_name').text($opt.data('name') || '-');
                $('#ci_company').text($opt.data('company') || '-');
                $('#ci_job_function').text($opt.data('job_function') || '-');
                $('#ci_level').text($opt.data('level') || '-');
                $('#ci_plan_year').text($opt.data('year') || '-');
                renderTermBadge(term);
                $('#candidate_info').removeClass('d-none');
            });

            // submit: petakan employee ke field ST/MT/LT sesuai term (backend tetap sama)
            $('#submitPlanBtn').on('click', function() {
                const kode = $('#kode_rtc').val();
                const employeeId = $('#candidate_select').val();
                const term = $('#selected_term').val();

                if (!kode) return Swal.fire('Validasi', 'Pilih Kode RTC terlebih dahulu.', 'warning');
                if (!employeeId) return Swal.fire('Validasi', 'Pilih kandidat terlebih dahulu.', 'warning');
                if (!term) return Swal.fire('Validasi', 'Term kandidat tidak terbaca.', 'warning');

                const formData = {
                    filter: @json($table), // area (Division/Department/...)
                    id: currentDivisionId, // area_id
                    short_term: '',
                    mid_term: '',
                    long_term: ''
                };
                if (term === 'short') formData.short_term = employeeId;
                else if (term === 'mid') formData.mid_term = employeeId;
                else formData.long_term = employeeId;

                $.ajax({
                    url: '{{ route('rtc.update') }}',
                    type: 'GET', // (idealnya POST/PUT, mengikuti kode kamu sekarang)
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).done(function() {
                    $('#addPlanModal').modal('hide');
                    window.location.reload();
                }).fail(function(xhr) {
                    console.error(xhr.responseText);
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Something went wrong';
                    Swal.fire('Error', msg, 'error');
                });
            });
        </script>
    @endif


    <script>
        // Simple search di client
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('searchInput');
            const tbody = document.querySelector('#kt_table_users tbody');
            input.addEventListener('keyup', function() {
                const q = this.value.toLowerCase();
                [...tbody.querySelectorAll('tr')].forEach(tr => {
                    const text = tr.innerText.toLowerCase();
                    tr.style.display = text.includes(q) ? '' : 'none';
                });
            });
        });
    </script>
@endpush
