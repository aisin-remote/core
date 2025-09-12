@extends('layouts.root.main')

@section('title', $title ?? 'IPP')
@section('breadcrumbs', $title ?? 'IPP')

@push('custom-css')
    <style>
        .section-sticky {
            position: sticky;
            top: 1rem;
            z-index: 100;
        }

        .point-row.adding {
            opacity: 0;
            transform: translateY(-6px);
        }

        .point-row.adding.show {
            opacity: 1;
            transform: translateY(0);
            transition: all .25s ease;
        }

        .point-row.removing {
            opacity: 0;
            transform: translateX(12px);
            transition: all .2s ease;
        }

        .weight-warning {
            color: #b02a37;
            font-weight: 600;
            display: none;
        }

        .badge-cap {
            background: #f1f3f5;
            color: #6c757d;
        }

        .table thead th {
            white-space: nowrap;
        }

        .req::after {
            content: "*";
            color: #dc3545;
            margin-left: .25rem;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-3">

        {{-- ====== Top Bar / Summary sticky ====== --}}
        <div class="row g-3">
            <div class="col-lg-9">
                {{-- IDENTITAS --}}
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Identitas</h5>
                        <span class="badge text-bg-secondary">Personal & Confidential</span>
                    </div>
                    <div class="card-body row g-3">
                        {{-- Pastikan variable backend tersedia, misal: $user, $ipp --}}
                        <div class="col-md-6">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" value="{{ $identitas['nama'] ?? '' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" value="{{ $identitas['department'] ?? '' }}"
                                readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Division</label>
                            <input type="text" class="form-control" value="{{ $identitas['division'] ?? '' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section / Sub</label>
                            <input type="text" class="form-control" value="{{ $identitas['section'] ?? '' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Review</label>
                            <input type="date" class="form-control" value="{{ $identitas['date_review'] ?? '' }}"
                                readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PIC Review</label>
                            <input type="text" class="form-control" value="{{ $identitas['pic_review'] ?? '' }}"
                                readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">On Year</label>
                            <input type="number" class="form-control" value="{{ $identitas['on_year'] ?? date('Y') }}"
                                readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No Form</label>
                            <input type="text" class="form-control" value="{{ $identitas['no_form'] ?? '' }}" readonly>
                        </div>
                    </div>
                </div>

                {{-- PROGRAM / ACTIVITY --}}
                @php
                    $categories = [
                        ['key' => 'activity_management', 'title' => 'I. Activity Management', 'cap' => 70],
                        ['key' => 'people_development', 'title' => 'II. People Development', 'cap' => 10],
                        ['key' => 'crp', 'title' => 'III. CRP', 'cap' => 10],
                        ['key' => 'special_assignment', 'title' => 'IV. Special Assignment & Improvement', 'cap' => 10],
                    ];
                @endphp

                <div class="accordion mt-3" id="accordionPrograms">
                    @foreach ($categories as $i => $cat)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $cat['key'] }}">
                                <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse-{{ $cat['key'] }}"
                                    aria-expanded="{{ $i == 0 ? 'true' : 'false' }}">
                                    <div class="d-flex flex-column w-100">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <span>{{ $cat['title'] }}</span>
                                            <span class="small text-muted">
                                                <span class="me-2">Cap <span
                                                        class="badge badge-cap">{{ $cat['cap'] }}%</span></span>
                                                <span>Used <span class="badge badge-primary"><span class="js-used"
                                                            data-cat="{{ $cat['key'] }}">0</span>%</span></span>
                                            </span>
                                        </div>
                                        <div class="progress mt-2" style="height:8px;">
                                            <div class="progress-bar js-progress" role="progressbar" style="width:0%;"
                                                aria-valuemin="0" aria-valuemax="{{ $cat['cap'] }}"
                                                data-cap="{{ $cat['cap'] }}" data-cat="{{ $cat['key'] }}"></div>
                                        </div>
                                        <small class="weight-warning mt-1" data-cat="{{ $cat['key'] }}">Bobot kategori
                                            melebihi {{ $cat['cap'] }}% — kurangi weight pada point.</small>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $cat['key'] }}"
                                class="accordion-collapse collapse {{ $i == 0 ? 'show' : '' }}"
                                data-bs-parent="#accordionPrograms">
                                <div class="accordion-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Gunakan tombol <strong>Tambah Point</strong> untuk
                                            mengisi. Klik <strong>Edit</strong> pada baris untuk ubah.</small>
                                        <button type="button" class="btn btn-sm btn-primary js-open-modal"
                                            data-cat="{{ $cat['key'] }}">
                                            <i class="bi bi-plus-lg"></i> Tambah Point
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 js-table"
                                            data-cat="{{ $cat['key'] }}">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:34%">Program / Activity</th>
                                                    <th style="width:22%">Target MID</th>
                                                    <th style="width:22%">Target One</th>
                                                    <th style="width:10%">Due</th>
                                                    <th style="width:6%">W%</th>
                                                    <th style="width:6%"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="js-tbody">
                                                {{-- baris akan diisi via JS --}}
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ACTIONS --}}
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-primary" id="btnSubmit">
                        <i class="bi bi-send"></i> Simpan IPP
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnDraft">
                        <i class="bi bi-save"></i> Simpan Draft
                    </button>
                </div>
            </div>

            {{-- Sidebar Summary --}}
            <div class="col-lg-3">
                <div class="section-sticky">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0">Ringkasan Bobot</h6>
                        </div>
                        <div class="card-body">
                            @foreach ($categories as $cat)
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ $cat['title'] }}</span>
                                    <span><strong><span class="js-used" data-cat="{{ $cat['key'] }}">0</span>%</strong>
                                        / {{ $cat['cap'] }}%</span>
                                </div>
                                <div class="progress mb-3" style="height:6px;">
                                    <div class="progress-bar js-progress" role="progressbar" style="width:0%"
                                        aria-valuemin="0" aria-valuemax="{{ $cat['cap'] }}"
                                        data-cap="{{ $cat['cap'] }}" data-cat="{{ $cat['key'] }}"></div>
                                </div>
                            @endforeach

                            <hr>
                            <div class="d-flex justify-content-between">
                                <div>Total</div>
                                <div><strong><span id="totalUsed">0</span>%</strong> / 100%</div>
                            </div>
                            <div class="progress mt-2" style="height:8px;">
                                <div class="progress-bar" id="totalProgress" style="width:0%"></div>
                            </div>
                            <small id="totalWarn" class="weight-warning d-block mt-2">Total bobot harus tepat
                                100%.</small>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" id="btnExpandAll"><i
                                        class="bi bi-arrows-expand"></i> Expand All</button>
                                <button type="button" class="btn btn-outline-secondary" id="btnCollapseAll"><i
                                        class="bi bi-arrows-collapse"></i> Collapse All</button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    @include('website.ipp.modal.create')
@endsection

@push('scripts')
    <script>
        (function($) {
            const CAT_CAP = {
                activity_management: 70,
                people_development: 10,
                crp: 10,
                special_assignment: 10,
            };

            const pointModal = new bootstrap.Modal(document.getElementById('pointModal'));

            // ===== Helpers Progress =====
            function sumWeights(cat) {
                let sum = 0;
                $(`table.js-table[data-cat="${cat}"] tbody tr`).each(function() {
                    sum += parseFloat($(this).data('weight')) || 0;
                });
                return sum;
            }

            function updateCategoryProgress(cat) {
                const used = sumWeights(cat);
                const cap = CAT_CAP[cat];
                $(`.js-used[data-cat="${cat}"]`).text(used.toFixed(0));
                const pct = Math.min((used / cap) * 100, 100);
                const $bar = $(`.js-progress[data-cat="${cat}"]`);
                $bar.css('width', pct + '%')
                    .toggleClass('bg-danger', used > cap)
                    .attr('aria-valuenow', used);
                $(`.weight-warning[data-cat="${cat}"]`).toggle(used > cap);
            }

            function updateTotal() {
                let total = 0;
                Object.keys(CAT_CAP).forEach(cat => total += sumWeights(cat));
                $('#totalUsed').text(total.toFixed(0));
                $('#totalProgress').css('width', `${Math.min(total,100)}%`)
                    .toggleClass('bg-danger', total !== 100);
                $('#totalWarn').toggle(total !== 100);
            }

            function recalcAll() {
                Object.keys(CAT_CAP).forEach(updateCategoryProgress);
                updateTotal();
            }

            // ===== Row Renderer =====
            let autoRowId = 0;

            function makeRowHtml(rowId, data) {
                const midShort = (data.target_mid || '').length > 110 ? (data.target_mid.slice(0, 110) + '…') : (data
                    .target_mid || '-');
                const oneShort = (data.target_one || '').length > 110 ? (data.target_one.slice(0, 110) + '…') : (data
                    .target_one || '-');
                const dueTxt = data.due_date ? data.due_date : '-';
                const w = isNaN(parseFloat(data.weight)) ? 0 : parseFloat(data.weight);

                return `
      <tr class="align-middle"
          data-row-id="${rowId}"
          data-activity="${_.escape(data.activity||'')}"
          data-mid="${_.escape(data.target_mid||'')}"
          data-one="${_.escape(data.target_one||'')}"
          data-due="${_.escape(dueTxt)}"
          data-weight="${w}">
        <td class="fw-semibold">${_.escape(data.activity||'-')}</td>
        <td class="text-muted">${_.escape(midShort)}</td>
        <td class="text-muted">${_.escape(oneShort)}</td>
        <td><span class="badge text-bg-light">${_.escape(dueTxt)}</span></td>
        <td><span class="badge ${w>0?'text-bg-primary':'text-bg-secondary'}">${w}</span></td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary js-edit" title="Edit"><i class="bi bi-pencil-square"></i></button>
            <button type="button" class="btn btn-outline-danger js-remove" title="Hapus"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      </tr>
    `;
            }

            // Lodash escape ringan (fallback kalau lodash tidak ada)
            window._ = window._ || {
                escape: (s) => String(s).replace(/[&<>"'`=\/]/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '/': '&#x2F;',
                    '`': '&#x60;',
                        '=': '&#x3D;'
                    } [c];
                })
            };

            // ===== Modal Open (Create) =====
            $(document).on('click', '.js-open-modal', function() {
                const cat = $(this).data('cat');
                $('#pmCat').val(cat);
                $('#pmMode').val('create');
                $('#pmRowId').val('');
                $('#pointModalLabel').text('Tambah Point — ' + $(this).closest('.accordion-item').find(
                    '.accordion-button span:first').text());
                // reset form
                $('#pointForm')[0].reset();
                setTimeout(() => $('#pmActivity').trigger('focus'), 150);
                pointModal.show();
            });

            // ===== Modal Open (Edit) =====
            $(document).on('click', '.js-edit', function() {
                const $tr = $(this).closest('tr');
                const cat = $(this).closest('table').data('cat');

                $('#pmCat').val(cat);
                $('#pmMode').val('edit');
                $('#pmRowId').val($tr.data('row-id'));
                $('#pointModalLabel').text('Edit Point');

                $('#pmActivity').val($tr.data('activity'));
                $('#pmTargetMid').val($tr.data('mid'));
                $('#pmTargetOne').val($tr.data('one'));
                $('#pmDue').val($tr.data('due'));
                $('#pmWeight').val($tr.data('weight'));

                setTimeout(() => $('#pmActivity').trigger('focus'), 150);
                pointModal.show();
            });

            // ===== Simpan Point (Create/Edit) =====
            $('#pointForm').on('submit', function(e) {
                e.preventDefault();
                const mode = $('#pmMode').val();
                const cat = $('#pmCat').val();

                const data = {
                    activity: $('#pmActivity').val().trim(),
                    target_mid: $('#pmTargetMid').val().trim(),
                    target_one: $('#pmTargetOne').val().trim(),
                    due_date: $('#pmDue').val(),
                    weight: parseFloat($('#pmWeight').val())
                };

                // Validasi dasar
                if (!data.activity) {
                    return toast('Isi "Program / Activity".', 'danger');
                }
                if (!data.due_date) {
                    return toast('Pilih "Due Date".', 'danger');
                }
                if (isNaN(data.weight)) {
                    return toast('Isi "Weight (%)" dengan angka.', 'danger');
                }

                // Cek cap kategori (allow sementara: kalau lewat, tetap boleh simpan tapi indikator merah)
                const before = sumWeights(cat);
                const delta = (mode === 'edit') ? computeEditDelta(cat, $('#pmRowId').val(), data.weight) : data
                    .weight;
                if (before + delta > CAT_CAP[cat]) {
                    // Beri peringatan, tetap lanjut jika user ingin
                    if (!confirm(`Bobot kategori akan melebihi ${CAT_CAP[cat]}%.\nLanjutkan?`)) return;
                }

                if (mode === 'create') {
                    const rowId = 'row-' + (++autoRowId);
                    const html = makeRowHtml(rowId, data);
                    const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                    const $row = $(html).css({
                        opacity: 0,
                        transform: 'translateY(-6px)'
                    });
                    $tbody.append($row);
                    requestAnimationFrame(() => $row.css({
                        opacity: 1,
                        transform: 'translateY(0)',
                        transition: 'all .25s ease'
                    }));
                } else {
                    const rowId = $('#pmRowId').val();
                    const $tr = $(`table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`);
                    // update dataset
                    $tr.attr('data-activity', data.activity)
                        .attr('data-mid', data.target_mid)
                        .attr('data-one', data.target_one)
                        .attr('data-due', data.due_date)
                        .attr('data-weight', (isNaN(data.weight) ? 0 : data.weight));
                    // redraw cells
                    $tr.replaceWith(makeRowHtml(rowId, data));
                }

                recalcAll();
                pointModal.hide();
                toast('Point tersimpan.');
            });

            function computeEditDelta(cat, rowId, newW) {
                const $tr = $(`table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`);
                const oldW = parseFloat($tr.data('weight')) || 0;
                return (newW - oldW);
            }

            // ===== Hapus
            $(document).on('click', '.js-remove', function() {
                const $tr = $(this).closest('tr');
                $tr.css({
                    opacity: 0,
                    transform: 'translateX(12px)',
                    transition: 'all .2s ease'
                });
                setTimeout(() => {
                    const cat = $(this).closest('table').data('cat');
                    $tr.remove();
                    recalcAll();
                    toast('Point dihapus.', 'warning');
                }, 180);
            });

            // ===== Init: tambah 1 placeholder row (opsional, boleh dihapus)
            $(document).ready(function() {
                Object.keys(CAT_CAP).forEach(cat => {
                    // tidak menambahkan row kosong, biar user mulai dari tombol "Tambah Point"
                    updateCategoryProgress(cat);
                });
                updateTotal();

                $('#btnExpandAll').on('click', function() {
                    $('.accordion-collapse').addClass('show');
                    $('.accordion-button').removeClass('collapsed');
                });
                $('#btnCollapseAll').on('click', function() {
                    $('.accordion-collapse').removeClass('show');
                    $('.accordion-button').addClass('collapsed');
                });
            });

            // ===== Kumpulkan Payload untuk AJAX Store (tanpa perubahan)
            function collectPayload(status = 'submitted') {
                const identitas = {
                    nama: @json($identitas['nama'] ?? ''),
                    department: @json($identitas['department'] ?? ''),
                    division: @json($identitas['division'] ?? ''),
                    section: @json($identitas['section'] ?? ''),
                    date_review: @json($identitas['date_review'] ?? ''),
                    pic_review: @json($identitas['pic_review'] ?? ''),
                    on_year: @json($identitas['on_year'] ?? date('Y')),
                    no_form: @json($identitas['no_form'] ?? ''),
                };

                const programs = {};
                Object.keys(CAT_CAP).forEach(cat => {
                    programs[cat] = [];
                    $(`table.js-table[data-cat="${cat}"] tbody tr`).each(function() {
                        const $tr = $(this);
                        programs[cat].push({
                            activity: $tr.data('activity') || '',
                            target_mid: $tr.data('mid') || '',
                            target_one: $tr.data('one') || '',
                            due_date: $tr.data('due') || '',
                            weight: parseFloat($tr.data('weight')) || 0,
                        });
                    });
                });

                const summary = {};
                Object.keys(CAT_CAP).forEach(cat => summary[cat] = sumWeights(cat));
                summary.total = Object.values(summary).reduce((a, b) => a + b, 0);
                return {
                    identitas,
                    programs,
                    summary,
                    status
                };
            }

            function validatePayload(payload) {
                for (const cat in CAT_CAP) {
                    if (payload.summary[cat] > CAT_CAP[cat]) return {
                        ok: false,
                        msg: `Bobot ${cat.replace('_',' ')} melebihi ${CAT_CAP[cat]}%.`
                    };
                }
                if (payload.status === 'submitted' && payload.summary.total !== 100) {
                    return {
                        ok: false,
                        msg: 'Total bobot harus tepat 100% sebelum submit.'
                    };
                }
                const hasAny = Object.values(payload.programs).some(list => list.length > 0);
                if (!hasAny) {
                    return {
                        ok: false,
                        msg: 'Tambahkan minimal satu point aktivitas.'
                    };
                }
                return {
                    ok: true
                };
            }

            function ajaxStore(payload) {
                return $.ajax({
                    url: "{{ route('ipp.store') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        payload: JSON.stringify(payload)
                    },
                    dataType: "json"
                });
            }

            $('#btnSubmit').on('click', function() {
                const payload = collectPayload('submitted');
                const valid = validatePayload(payload);
                if (!valid.ok) {
                    toast(valid.msg, 'danger');
                    return;
                }
                $(this).prop('disabled', true);
                ajaxStore(payload)
                    .done(res => toast(res.message || 'Berhasil menyimpan IPP.'))
                    .fail(err => toast(err?.responseJSON?.message || 'Gagal menyimpan.', 'danger'))
                    .always(() => $(this).prop('disabled', false));
            });

            $('#btnDraft').on('click', function() {
                const payload = collectPayload('draft');
                const valid = validatePayload(payload);
                if (!valid.ok && valid.msg.includes('point')) {
                    toast(valid.msg, 'danger');
                    return;
                }
                $(this).prop('disabled', true);
                ajaxStore(payload)
                    .done(res => toast(res.message || 'Draft tersimpan.'))
                    .fail(() => toast('Gagal simpan draft.', 'danger'))
                    .always(() => $(this).prop('disabled', false));
            });

            // ===== Toast util sederhana
            function toast(msg, type = 'success') {
                const id = 'toast-' + Date.now();
                const $t = $(`
      <div class="toast align-items-center text-bg-${type} border-0" id="${id}" role="status" aria-live="polite" aria-atomic="true" style="position:fixed;top:1rem;right:1rem;z-index:1080;">
        <div class="d-flex">
          <div class="toast-body">${msg}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    `);
                $('body').append($t);
                const t = new bootstrap.Toast($t[0], {
                    delay: 2500
                });
                t.show();
                $t.on('hidden.bs.toast', () => $t.remove());
            }

        })(jQuery);
    </script>
@endpush
