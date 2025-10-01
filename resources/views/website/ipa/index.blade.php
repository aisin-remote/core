@extends('layouts.root.main')

@section('title', $title ?? 'IPP')
@section('breadcrumbs', $title ?? 'IPP')

@section('main')
    <div class="container-xxl py-3 px-6">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 align-items-end">
                    <div>
                        <label class="form-label mb-1">Tahun</label>
                        <select id="filter-year" class="form-select form-select-sm" style="min-width:110px">
                            @php
                                $nowY = now()->format('Y');
                                $startY = $nowY - 3;
                                $endY = $nowY + 1;
                            @endphp
                            <option value="">Semua</option>
                            @for ($y = $endY; $y >= $startY; $y--)
                                <option value="{{ $y }}" {{ $y == $nowY ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="ms-auto small text-muted" id="table-meta"></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light-primary border-0">
                <h3 class="fw-bolder m-0">IPP Saya & Status IPA</h3>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle ipp-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:80px">Tahun</th>
                            <th style="width:130px">Status IPP</th>
                            <th style="width:130px">Status IPA</th>
                            <th style="width:160px" class="text-end">Totals</th>
                            <th style="width:160px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ipa-tbody">
                        <tr class="empty-row">
                            <td colspan="5">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-muted" id="page-info">—</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pager">
                        <li class="page-item disabled"><a class="page-link" href="javascript:;" data-page="prev">«</a></li>
                        <li class="page-item disabled"><span class="page-link" id="page-current">1</span></li>
                        <li class="page-item disabled"><a class="page-link" href="javascript:;" data-page="next">»</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('custom-css')
    <style>
        /* === Badges (IPP/IPA status) === */
        .badge-pill {
            border-radius: 9999px;
            padding: .35rem .6rem;
            font-weight: 600;
            font-size: .78rem
        }

        .badge-ipp-approved {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0
        }

        .badge-ipp-draft {
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e5e7eb
        }

        .badge-ipp-submitted {
            background: #fff7ed;
            color: #92400e;
            border: 1px solid #fde68a
        }

        .badge-ipp-rejected {
            background: #fef2f2;
            color: #7f1d1d;
            border: 1px solid #fecaca
        }

        .badge-ipa-exists {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd
        }

        .badge-ipa-none {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e5e7eb
        }

        /* === Tiny buttons in table === */
        .btn-table {
            padding: .35rem .55rem;
            border-radius: 8px
        }

        .btn-create {
            background: #3b82f6;
            color: #fff;
            border: 1px solid #3b82f6
        }

        .btn-create[disabled] {
            opacity: .5;
            cursor: not-allowed
        }

        .btn-view {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e5e7eb
        }

        .btn-view:hover {
            background: #f3f4f6
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function() {
            const $tbody = $('#ipa-tbody');
            const $year = $('#filter-year');
            const $meta = $('#table-meta');
            const $pageInfo = $('#page-info');
            const $pager = $('#pager');
            const $pageCur = $('#page-current');

            let state = {
                page: 1,
                per_page: 10,
                total: 0
            };

            function badgeIPP(status) {
                const s = (status || '').toLowerCase();
                if (s === 'approved') return '<span class="badge-pill badge-ipp-approved">Approved</span>';
                if (s === 'submitted') return '<span class="badge-pill badge-ipp-submitted">Submitted</span>';
                if (s === 'rejected') return '<span class="badge-pill badge-ipp-rejected">Rejected</span>';
                return '<span class="badge-pill badge-ipp-draft">Draft</span>';
            }

            function badgeIPA(ipa) {
                return (ipa && ipa.id) ?
                    '<span class="badge-pill badge-ipa-exists">Sudah Dibuat</span>' :
                    '<span class="badge-pill badge-ipa-none">Belum Ada</span>';
            }

            function fmt(n) {
                if (!n) return '0.00';
                return Number(n).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function render(rows) {
                if (!rows.length) {
                    $tbody.html('<tr class="empty-row"><td colspan="5">Tidak ada data.</td></tr>');
                    return;
                }
                $tbody.html(rows.map(r => {
                    const year = r.ipp.on_year;
                    const ipp = badgeIPP(r.ipp.status);
                    const ipa = badgeIPA(r.ipa);
                    const totals = r.ipa?.id ?
                        `<div class="text-end small">
                        <div>Act: <strong>${fmt(r.ipa.activity_total)}</strong></div>
                        <div>Ach: <strong>${fmt(r.ipa.achievement_total)}</strong></div>
                        <div>Grand: <strong>${fmt(r.ipa.grand_total)}</strong></div>
                   </div>` :
                        `<span class="text-muted small">—</span>`;

                    let actions = '';
                    if (r.actions.can_create_ipa) {
                        actions += `<button class="btn btn-sm btn-table btn-create me-1"
                                data-action="create"
                                data-url="${r.actions.create_url}">Create IPA</button>`;
                    } else {
                        actions +=
                            `<button class="btn btn-sm btn-table btn-create me-1" disabled>Create IPA</button>`;
                    }
                    if (r.actions.view_url) {
                        actions +=
                            `<a class="btn btn-sm btn-table btn-view" href="${r.actions.view_url}" target="_blank">Lihat IPA</a>`;
                    } else {
                        actions +=
                            `<button class="btn btn-sm btn-table btn-view" disabled>Lihat IPA</button>`;
                    }

                    return `<tr>
                <td><strong>${year}</strong></td>
                <td>${ipp}</td>
                <td>${ipa}</td>
                <td class="text-end">${totals}</td>
                <td class="text-center">${actions}</td>
            </tr>`;
                }).join(''));
            }

            function setPager(page, perPage, total) {
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                $pageCur.text(page + ' / ' + totalPages);
                $pager.find('[data-page="prev"]').parent().toggleClass('disabled', page <= 1);
                $pager.find('[data-page="next"]').parent().toggleClass('disabled', page >= totalPages);
                const from = total ? ((page - 1) * perPage + 1) : 0;
                const to = Math.min(page * perPage, total);
                $pageInfo.text(`Menampilkan ${from}-${to} dari ${total}`);
            }

            function load(page = 1) {
                $tbody.html('<tr class="empty-row"><td colspan="5">Memuat...</td></tr>');
                $.getJSON('{{ route('ipa.init') }}', {
                    page: page,
                    per_page: state.per_page,
                    year: $year.val() || ''
                }).done(res => {
                    if (res.ok) {
                        state.page = res.page;
                        state.per_page = res.per_page;
                        state.total = res.total;
                        render(res.rows);
                        setPager(res.page, res.per_page, res.total);
                        $meta.text(`Total: ${res.total} baris`);
                    } else {
                        $tbody.html(`<tr class="empty-row"><td colspan="5">${res.message}</td></tr>`);
                    }
                }).fail(() => $tbody.html('<tr class="empty-row"><td colspan="5">Error server.</td></tr>'));
            }

            // pager
            $pager.on('click', '.page-link', function() {
                const which = $(this).data('page');
                if ($(this).closest('.page-item').hasClass('disabled')) return;
                let next = state.page;
                const maxPage = Math.ceil(state.total / state.per_page);
                if (which === 'prev') next = Math.max(1, state.page - 1);
                if (which === 'next') next = Math.min(maxPage, state.page + 1);
                load(next);
            });

            // filter
            $year.on('change', () => load(1));

            // create IPA
            $tbody.on('click', '[data-action="create"]', function() {
                const $btn = $(this);
                const url = $btn.data('url');
                console.log(url);

                $btn.prop('disabled', true).text('Membuat...');
                $.ajax({
                        url: url,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        dataType: 'json'
                    }).done(res => {
                        if (res.ok && res.redirect_url) {
                            window.open(res.redirect_url, '_blank');
                        }
                        load(state.page);
                    }).fail(() => alert('Gagal membuat IPA.'))
                    .always(() => $btn.prop('disabled', false).text('Create IPA'));
            });

            load(1);
        })();
    </script>
@endpush
