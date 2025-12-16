@extends('layouts.root.main')

@push('custom-css')
    <style>
        .legend-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .section-title {
            font-weight: 600;
            font-size: 1.1rem;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #0d6efd;
            font-size: 1.1rem;
        }

        .table-sm-custom th,
        .table-sm-custom td {
            padding: 0.5rem 0.75rem;
            vertical-align: top;
        }

        .badge-pill {
            border-radius: 999px;
            padding-inline: 0.7rem;
        }

        .text-muted-small {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .card-header-sticky {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #fff;
        }

        .invalid-feedback {
            display: none;
            font-size: 0.875em;
            color: #dc3545;
        }

        .is-invalid~.invalid-feedback {
            display: block;
        }

        @media (max-width: 768px) {
            .flex-wrap-sm {
                flex-wrap: wrap;
            }
        }

        /* ====== LOCKED ONE-YEAR TAB ====== */
        .nav-link-one-locked {
            opacity: 0.6;
        }

        .locked-wrapper {
            position: relative;
            min-height: 220px;
        }

        .locked-blur {
            filter: blur(2px);
            pointer-events: none;
            user-select: none;
            min-height: 220px;
        }

        .locked-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.85);
            z-index: 2;
            text-align: center;
        }

        /* ============================
           IMPROVED EMPLOYEE INFO CARD
           ============================ */
        .emp-card {
            border: 1px solid #eef2f7;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .emp-card .emp-header {
            background: linear-gradient(90deg, rgba(13, 110, 253, .12), rgba(13, 110, 253, .02));
            border-bottom: 1px solid rgba(13, 110, 253, .12);
        }

        .emp-avatar {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 1.2rem;
            color: #0d6efd;
            background: rgba(13, 110, 253, .10);
            border: 1px solid rgba(13, 110, 253, .18);
            box-shadow: inset 0 0 0 4px rgba(255, 255, 255, .65);
        }

        .emp-name {
            font-weight: 800;
            font-size: 1.2rem;
            line-height: 1.2;
        }

        .emp-sub {
            color: #64748b;
            font-size: .9rem;
        }

        .emp-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .55rem;
        }

        .emp-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: .85rem;
            color: #334155;
            white-space: nowrap;
        }

        .emp-chip i {
            color: #0d6efd;
        }

        .ass-grid {
            display: grid;
            gap: .75rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        @media (max-width: 992px) {
            .ass-grid {
                grid-template-columns: 1fr;
            }
        }

        .ass-box {
            border: 1px solid #eef2f7;
            border-radius: 12px;
            background: #fff;
            padding: .85rem 1rem;
        }

        .ass-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .8rem;
            color: #64748b;
            margin-bottom: .25rem;
            font-weight: 700;
        }

        .ass-label i {
            color: #0d6efd;
        }

        .ass-value {
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
            white-space: normal;
            word-break: break-word;
        }

        .emp-bc {
            font-weight: 700;
            color: #0f172a;
        }
    </style>
@endpush

@section('title')
    {{ $title ?? 'IDP Development' }}
@endsection

@section('breadcrumbs')
    <span id="bc-text">IDP / Development</span>
@endsection

@section('main')
    {{-- Meta CSRF untuk AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div id="kt_app_content_container" class="app-container container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap-sm gap-3">
            <h3 class="mb-0" id="page-title">{{ $title ?? 'IDP Development' }}</h3>
            <a href="{{ route('idp.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Back to IDP List
            </a>
        </div>

        {{-- CARD: Employee Info (Improved) --}}
        <div class="card mb-5 emp-card" id="card-employee" style="display:none;">
            <div class="card-body p-0">

                <div class="p-4 emp-header">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="emp-avatar" id="emp-initial">?</div>

                            <div>
                                <div class="emp-name" id="emp-name">-</div>
                                <div class="emp-sub" id="emp-meta">-</div>

                                <div class="emp-chips" id="emp-chips">
                                    {{-- diisi JS --}}
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="text-muted-small mb-1">Path</div>
                            <div class="emp-bc" id="emp-bc">-</div>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="ass-grid">
                        <div class="ass-box">
                            <div class="ass-label"><i class="bi bi-bullseye"></i> Assessment Purpose</div>
                            <div class="ass-value" id="ass-purpose">-</div>
                        </div>
                        <div class="ass-box">
                            <div class="ass-label"><i class="bi bi-building"></i> Assessor</div>
                            <div class="ass-value" id="ass-lembaga">-</div>
                        </div>
                        <div class="ass-box">
                            <div class="ass-label"><i class="bi bi-calendar-event"></i> Date</div>
                            <div class="ass-value" id="ass-date">-</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- CARD: IDP List (Read Only) --}}
        <div class="card mb-5">
            <div class="card-header card-header-sticky">
                <h4 class="card-title mb-0">Individual Development Program (IDP)</h4>
            </div>
            <div class="card-body" id="idp-list-wrapper">
                <div class="text-muted">Loading...</div>
            </div>
        </div>

        {{-- CARD: Development Progress --}}
        <div class="card">
            <div class="card-header card-header-sticky">
                <ul class="nav nav-tabs card-header-tabs" role="tablist" style="cursor:pointer">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-mid" role="tab" id="tab-mid-link">
                            Mid-Year Review
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-one" role="tab" id="tab-one-link">
                            One-Year Review <i class="bi bi-lock-fill ms-1 small d-none" id="one-lock-icon"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">

                    {{-- TAB MID --}}
                    <div class="tab-pane fade show active" id="tab-mid" role="tabpanel">
                        <div id="mid-wrapper">
                            <div class="text-muted">Loading...</div>
                        </div>
                    </div>

                    {{-- TAB ONE --}}
                    <div class="tab-pane fade" id="tab-one" role="tabpanel">
                        <div id="one-wrapper">
                            <div class="text-muted">Loading...</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const EMPLOYEE_ID = @json($employee_id);

            const URL_JSON = @json(route('development.json', ['employee_id' => $employee_id]));
            const URL_SAVE_MID = @json(route('development.storeMidYear', ['employee_id' => $employee_id]));
            const URL_SAVE_ONE = @json(route('development.storeOneYear', ['employee_id' => $employee_id]));
            const URL_SUBMIT_MID = @json(route('development.submitMidYear', ['employee_id' => $employee_id]));
            const URL_SUBMIT_ONE = @json(route('development.submitOneYear', ['employee_id' => $employee_id]));

            const TAB_STORE_KEY = `dev_active_tab_${EMPLOYEE_ID}`;

            let STATE = {
                idpRows: [],
                midHistory: [],
                oneHistory: [],
                latestMidByIdp: {},
                latestOneByIdp: {},
                flags: {},
                draftIds: {
                    midDraftIdpIds: [],
                    oneDraftIdpIds: []
                }
            };

            function csrfToken() {
                return $('meta[name="csrf-token"]').attr('content');
            }

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;')
                    .replaceAll('`', '&#96;');
            }

            function titleCase(s) {
                if (!s) return '-';
                return s.charAt(0).toUpperCase() + s.slice(1);
            }

            function badgeClass(status) {
                if (status === 'draft') return 'badge-warning';
                if (status === 'submitted') return 'badge-info';
                if (status === 'approved') return 'badge-success';
                if (status === 'checked') return 'badge-primary';
                if (status === 'revised') return 'badge-danger';
                return 'badge-secondary';
            }

            function getActiveTabId() {
                const active = $('.nav-tabs .nav-link.active').attr('href');
                return active || '#tab-mid';
            }

            function activateTab(tabId) {
                const el = document.querySelector(`.nav-tabs a[href="${tabId}"]`);
                if (!el) return;
                const tab = new bootstrap.Tab(el);
                tab.show();
            }

            // simpan tab aktif
            $(document).on('shown.bs.tab', '.nav-tabs a[data-bs-toggle="tab"]', function(e) {
                const href = $(e.target).attr('href');
                if (href) localStorage.setItem(TAB_STORE_KEY, href);
            });

            /* ==========================
               UPDATED: Employee Card Fill
               ========================== */
            function renderEmployee(emp, assessment, pageTitle) {
                $('#page-title').text(pageTitle || 'IDP Development');
                document.title = pageTitle || document.title;

                const name = emp?.name || '-';
                const initial = (name && name !== '-') ? name.substring(0, 1).toUpperCase() : '?';

                $('#emp-initial').text(initial);
                $('#emp-name').text(name);

                const metaLine = [
                    emp?.position ? escapeHtml(emp.position) : null,
                    emp?.department.name ? escapeHtml(emp.department.name) : null
                ].filter(Boolean).join(' • ');

                $('#emp-meta').text(metaLine || '-');

                const npk = emp?.npk ? escapeHtml(emp.npk) : '-';
                const grade = emp?.grade ? escapeHtml(emp.grade) : '-';
                const dept = emp?.department.name ? escapeHtml(emp.department.name) : '-';
                const pos = emp?.position ? escapeHtml(emp.position) : '-';

                $('#emp-chips').html(`
                    <span class="emp-chip"><i class="bi bi-hash"></i> NPK: <b>${npk}</b></span>
                    <span class="emp-chip"><i class="bi bi-briefcase"></i> Position: <b>${pos}</b></span>
                    <span class="emp-chip"><i class="bi bi-diagram-3"></i> Dept: <b>${dept}</b></span>
                    <span class="emp-chip"><i class="bi bi-award"></i> Grade: <b>${grade}</b></span>
                `);

                $('#ass-purpose').text(assessment?.purpose || '-');
                $('#ass-lembaga').text(assessment?.lembaga || '-');
                $('#ass-date').text(assessment?.date || '-');

                $('#card-employee').show();

                const bc = `IDP / ${name} / Development`;
                $('#bc-text').text(bc);
                $('#emp-bc').text(bc);
            }

            function renderIdpList(idpRows) {
                if (!Array.isArray(idpRows) || !idpRows.length) {
                    $('#idp-list-wrapper').html(`<p class="text-muted mb-0">Belum ada IDP.</p>`);
                    return;
                }

                let rowsHtml = '';
                idpRows.forEach((row, idx) => {
                    const idp = row.idp || {};
                    rowsHtml += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${escapeHtml(row.alc_name || '-')}</td>
                            <td>${escapeHtml(idp.category || '-')}</td>
                            <td>${escapeHtml(idp.development_program || '-')}</td>
                            <td>${escapeHtml(idp.development_target || '-')}</td>
                            <td>${escapeHtml(idp.date || '-')}</td>
                        </tr>
                    `;
                });

                $('#idp-list-wrapper').html(`
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm-custom">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%;">No</th>
                                    <th style="width:20%;">ALC</th>
                                    <th style="width:15%;">Category</th>
                                    <th>Development Program</th>
                                    <th style="width:20%;">Development Target</th>
                                    <th style="width:12%;">Due Date</th>
                                </tr>
                            </thead>
                            <tbody>${rowsHtml}</tbody>
                        </table>
                    </div>
                `);
            }

            // ambil latest per idp_id, prioritas draft
            function buildLatestByIdp(historyList) {
                const mapDraft = {};
                const mapAny = {};

                (historyList || []).forEach(item => {
                    const idpId = item?.idp_id;
                    if (!idpId) return;

                    if (!mapAny[idpId]) mapAny[idpId] = item;

                    if (item.status === 'draft' && !mapDraft[idpId]) {
                        mapDraft[idpId] = item;
                    }
                });

                const final = {};
                Object.keys(mapAny).forEach(idpId => {
                    final[idpId] = mapDraft[idpId] || mapAny[idpId];
                });

                return final;
            }

            function hasAnyRevised(latestMap) {
                try {
                    return Object.values(latestMap || {}).some(it => (it?.status || '') === 'revised');
                } catch (e) {
                    return false;
                }
            }

            function renderMid() {
                const idpRows = STATE.idpRows || [];
                const flags = STATE.flags || {};

                const hasMidRevised = hasAnyRevised(STATE.latestMidByIdp);
                const midLocked = (!!flags.midLocked) && !hasMidRevised;

                const hasMidDraft = !!flags.hasMidDraft;

                let formHtml = '';
                if (!idpRows.length) {
                    formHtml = `<p class="text-muted">Tidak ada IDP.</p>`;
                } else {
                    let body = '';
                    idpRows.forEach((row, idx) => {
                        const idp = row.idp || {};
                        const idpId = String(idp.id || '');
                        const latest = STATE.latestMidByIdp[idpId] || null;

                        const valAch = latest?.development_achievement ?? '';
                        const valNext = latest?.next_action ?? '';

                        body += `
                            <tr>
                                <td>${idx + 1}</td>
                                <td>${escapeHtml(row.alc_name || '-')}</td>
                                <td>
                                    ${escapeHtml(idp.development_program || '-')}
                                    <input type="hidden" name="idp_id[]" value="${escapeHtml(idpId)}">
                                    <input type="hidden" name="development_program[]" value="${escapeHtml(idp.development_program || '')}">
                                </td>
                                <td>
                                    <textarea name="development_achievement[]" data-index="${idx}" class="form-control form-control-sm"
                                        rows="3" placeholder="Tuliskan capaian pengembangan" ${midLocked ? 'disabled' : ''}>${escapeHtml(valAch)}</textarea>
                                    <div class="invalid-feedback" id="error-development_achievement-${idx}"></div>
                                </td>
                                <td>
                                    <textarea name="next_action[]" data-index="${idx}" class="form-control form-control-sm"
                                        rows="3" placeholder="Next action" ${midLocked ? 'disabled' : ''}>${escapeHtml(valNext)}</textarea>
                                    <div class="invalid-feedback" id="error-next_action-${idx}"></div>
                                </td>
                            </tr>
                        `;
                    });

                    formHtml = `
                        <form id="form-mid-year" method="POST" action="${URL_SAVE_MID}">
                            <input type="hidden" name="_token" value="${csrfToken()}">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-hover table-sm-custom align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:5%;">No</th>
                                            <th style="width:20%;">ALC</th>
                                            <th style="width:25%;">Development Program</th>
                                            <th style="width:25%;">Achievement (Mid-Year)</th>
                                            <th style="width:25%;">Next Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>${body}</tbody>
                                </table>
                            </div>

                            ${midLocked ? '' : `
                            <div class="d-flex justify-content-end gap-2 mb-4" id="mid-save-wrapper">
                                <button type="submit" class="btn btn-primary" id="btn-save-mid">
                                    <i class="fas fa-save me-2"></i> ${hasMidDraft ? 'Update Draft' : 'Save Draft'}
                                </button>
                            </div>
                        `}
                        </form>
                    `;
                }

                const hist = STATE.midHistory || [];
                let histRows = '';
                if (!hist.length) {
                    histRows = `
                        <tr class="mid-empty-row">
                            <td colspan="5" class="text-center text-muted">Belum ada history Mid-Year Development.</td>
                        </tr>
                    `;
                } else {
                    hist.forEach(d => {
                        histRows += `
                            <tr data-mid-idp-id="${escapeHtml(d.idp_id)}">
                                <td>${escapeHtml(d.alc || '-')}</td>
                                <td>${escapeHtml(d.development_program || '-')}</td>
                                <td>${escapeHtml(d.development_achievement || '-')}</td>
                                <td>${escapeHtml(d.next_action || '-')}</td>
                                <td>
                                    <span class="badge status-badge ${badgeClass(d.status)}" data-status="${escapeHtml(d.status)}">
                                        ${escapeHtml(titleCase(d.status))}
                                    </span>
                                </td>
                                <td>${escapeHtml(d.created_at || '-')}</td>
                            </tr>
                        `;
                    });
                }

                const showSubmit = (STATE.draftIds?.midDraftIdpIds || []).length > 0;

                $('#mid-wrapper').html(`
                    ${formHtml}

                    <hr class="my-4">
                    <div class="section-title mb-0"><i class="bi bi-bar-chart-line-fill"></i> Mid-Year History</div>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered table-hover table-sm-custom">
                            <thead class="table-light">
                                <tr>
                                    <th>ALC</th>
                                    <th>Development Program</th>
                                    <th>Achievement</th>
                                    <th>Next Action</th>
                                    <th>Status</th>
                                    <th style="width:120px;">Date</th>
                                </tr>
                            </thead>
                            <tbody id="mid-history-body">${histRows}</tbody>
                        </table>

                        <div class="d-flex justify-content-end align-items-center">
                            ${showSubmit ? `
                            <button type="button" class="btn btn-success" id="btn-submit-mid">
                                <i class="fas fa-paper-plane me-2"></i> Submit Draft
                            </button>
                        ` : ''}
                        </div>
                    </div>
                `);
            }

            function renderOne() {
                const idpRows = STATE.idpRows || [];
                const flags = STATE.flags || {};

                const hasMidRevised = hasAnyRevised(STATE.latestMidByIdp);
                const forceLockOneBecauseMidRevised = hasMidRevised;

                const canAccessOne = (!!flags.canAccessOne) && !forceLockOneBecauseMidRevised;

                const hasOneRevised = hasAnyRevised(STATE.latestOneByIdp);
                const oneLocked = (!!flags.oneLocked) && !hasOneRevised;

                const hasOneDraft = !!flags.hasOneDraft;

                if (!canAccessOne) {
                    $('#tab-one-link').addClass('nav-link-one-locked');
                    $('#one-lock-icon').removeClass('d-none');
                } else {
                    $('#tab-one-link').removeClass('nav-link-one-locked');
                    $('#one-lock-icon').addClass('d-none');
                }

                if (!canAccessOne) {
                    const reason = forceLockOneBecauseMidRevised ?
                        `Mid-Year direvisi. Silakan perbaiki dan <strong>submit ulang Mid-Year</strong> terlebih dahulu sebelum akses One-Year.` :
                        `Silakan lengkapi dan <strong>submit Mid-Year Development</strong> terlebih dahulu sebelum mengisi <strong>One-Year Development</strong>.`;

                    $('#one-wrapper').html(`
                        <div class="locked-wrapper">
                            <div class="locked-blur"><div style="height:220px;"></div></div>
                            <div class="locked-overlay">
                                <i class="bi bi-lock-fill fs-1 mb-3 text-primary"></i>
                                <h5 class="fw-bold mb-2">One-Year Review Locked</h5>
                                <p class="text-muted mb-0">${reason}</p>
                            </div>
                        </div>
                    `);
                    return;
                }

                let formHtml = '';
                if (!idpRows.length) {
                    formHtml = `<p class="text-muted">Tidak ada IDP.</p>`;
                } else {
                    let body = '';
                    idpRows.forEach((row, idx) => {
                        const idp = row.idp || {};
                        const idpId = String(idp.id || '');
                        const latest = STATE.latestOneByIdp[idpId] || null;

                        const valEval = latest?.evaluation_result ?? '';

                        body += `
                            <tr>
                                <td>${idx + 1}</td>
                                <td>${escapeHtml(row.alc_name || '-')}</td>
                                <td>
                                    ${escapeHtml(idp.development_program || '-')}
                                    <input type="hidden" name="idp_id[]" value="${escapeHtml(idpId)}">
                                    <input type="hidden" name="development_program[]" value="${escapeHtml(idp.development_program || '')}">
                                </td>
                                <td>
                                    <textarea name="evaluation_result[]" data-index="${idx}" class="form-control form-control-sm"
                                        rows="3" placeholder="Tuliskan hasil evaluasi" ${oneLocked ? 'disabled' : ''}>${escapeHtml(valEval)}</textarea>
                                    <div class="invalid-feedback" id="error-evaluation_result-${idx}"></div>
                                </td>
                            </tr>
                        `;
                    });

                    formHtml = `
                        <form id="form-one-year" method="POST" action="${URL_SAVE_ONE}">
                            <input type="hidden" name="_token" value="${csrfToken()}">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-hover table-sm-custom align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:5%;">No</th>
                                            <th style="width:20%;">ALC</th>
                                            <th style="width:30%;">Development Program</th>
                                            <th style="width:45%;">Evaluation Result (One-Year)</th>
                                        </tr>
                                    </thead>
                                    <tbody>${body}</tbody>
                                </table>
                            </div>

                            ${oneLocked ? '' : `
                            <div class="d-flex justify-content-end gap-2 mb-4" id="one-save-wrapper">
                                <button type="submit" class="btn btn-primary" id="btn-save-one">
                                    <i class="fas fa-save me-2"></i> ${hasOneDraft ? 'Update Draft' : 'Save Draft'}
                                </button>
                            </div>
                        `}
                        </form>
                    `;
                }

                const hist = STATE.oneHistory || [];
                let histRows = '';
                if (!hist.length) {
                    histRows = `
                        <tr class="one-empty-row">
                            <td colspan="5" class="text-center text-muted">Belum ada history One-Year Development.</td>
                        </tr>
                    `;
                } else {
                    hist.forEach(d => {
                        histRows += `
                            <tr data-one-idp-id="${escapeHtml(d.idp_id)}">
                                <td>${escapeHtml(d.alc || '-')}</td>
                                <td>${escapeHtml(d.development_program || '-')}</td>
                                <td>${escapeHtml(d.evaluation_result || '-')}</td>
                                <td>
                                    <span class="badge status-badge ${badgeClass(d.status)}" data-status="${escapeHtml(d.status)}">
                                        ${escapeHtml(titleCase(d.status))}
                                    </span>
                                </td>
                                <td>${escapeHtml(d.created_at || '-')}</td>
                            </tr>
                        `;
                    });
                }

                const showSubmit = (STATE.draftIds?.oneDraftIdpIds || []).length > 0;

                $('#one-wrapper').html(`
                    ${formHtml}

                    <hr class="my-4">
                    <div class="section-title mb-0"><i class="bi bi-calendar-check-fill"></i> One-Year History</div>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered table-hover table-sm-custom">
                            <thead class="table-light">
                                <tr>
                                    <th>ALC</th>
                                    <th>Development Program</th>
                                    <th>Evaluation Result</th>
                                    <th>Status</th>
                                    <th style="width:120px;">Date</th>
                                </tr>
                            </thead>
                            <tbody id="one-history-body">${histRows}</tbody>
                        </table>

                        <div class="d-flex justify-content-end align-items-center">
                            ${showSubmit ? `
                            <button type="button" class="btn btn-success" id="btn-submit-one">
                                <i class="fas fa-paper-plane me-2"></i> Submit Draft
                            </button>
                        ` : ''}
                        </div>
                    </div>
                `);
            }

            function renderAll(payload) {
                renderEmployee(payload.employee, payload.assessment, payload.title);

                STATE.idpRows = payload.idpRows || [];
                STATE.midHistory = payload.midHistory || [];
                STATE.oneHistory = payload.oneHistory || [];
                STATE.flags = payload.flags || {};
                STATE.draftIds = payload.draftIds || { midDraftIdpIds: [], oneDraftIdpIds: [] };

                STATE.latestMidByIdp = buildLatestByIdp(STATE.midHistory);
                STATE.latestOneByIdp = buildLatestByIdp(STATE.oneHistory);

                renderIdpList(STATE.idpRows);
                renderMid();
                renderOne();
            }

            function refreshData(keepTabId) {
                const tabToRestore = keepTabId || getActiveTabId() || localStorage.getItem(TAB_STORE_KEY) || '#tab-mid';

                $.ajax({
                    url: URL_JSON,
                    method: 'GET',
                    success: function(res) {
                        if (!res || res.status !== 'success') {
                            $('#idp-list-wrapper').html(`<div class="text-danger">Gagal load data.</div>`);
                            return;
                        }
                        renderAll(res);
                        activateTab(tabToRestore);
                    },
                    error: function() {
                        $('#idp-list-wrapper').html(`<div class="text-danger">Gagal load data (server error).</div>`);
                    }
                });
            }

            // =========================
            // SAVE DRAFT (delegated)
            // =========================
            $(document).on('submit', '#form-mid-year', function(e) {
                e.preventDefault();
                const tabId = '#tab-mid';

                const $form = $(this);
                const $btn = $('#btn-save-mid');
                const original = $btn.html();

                $form.find('.form-control').removeClass('is-invalid');
                $form.find('.invalid-feedback').text('');

                $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm"></span> Saving...`);

                const fd = new FormData(this);

                $.ajax({
                    url: URL_SAVE_MID,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $btn.prop('disabled', false).html(original);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message || 'Mid-Year Development berhasil disimpan.'
                        });
                        refreshData(tabId);
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(original);

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, messages) {
                                const parts = key.split('.');
                                const fieldName = parts[0];
                                const index = parts[1];
                                const $input = $form.find(`[name="${fieldName}[]"][data-index="${index}"]`);
                                if ($input.length) {
                                    $input.addClass('is-invalid');
                                    $(`#error-${fieldName}-${index}`).text(messages[0] || '');
                                }
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                text: 'Mohon periksa kembali isian form Anda.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan pada server. Silakan coba lagi.'
                            });
                        }
                    }
                });
            });

            $(document).on('submit', '#form-one-year', function(e) {
                e.preventDefault();
                const tabId = '#tab-one';

                const $form = $(this);
                const $btn = $('#btn-save-one');
                const original = $btn.html();

                $form.find('.form-control').removeClass('is-invalid');
                $form.find('.invalid-feedback').text('');

                $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm"></span> Saving...`);

                const fd = new FormData(this);

                $.ajax({
                    url: URL_SAVE_ONE,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $btn.prop('disabled', false).html(original);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message || 'One-Year Development berhasil disimpan.'
                        });
                        refreshData(tabId);
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(original);

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, messages) {
                                const parts = key.split('.');
                                const fieldName = parts[0];
                                const index = parts[1];
                                const $input = $form.find(`[name="${fieldName}[]"][data-index="${index}"]`);
                                if ($input.length) {
                                    $input.addClass('is-invalid');
                                    $(`#error-${fieldName}-${index}`).text(messages[0] || '');
                                }
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                text: 'Mohon periksa kembali isian form Anda.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan saat submit.'
                            });
                        }
                    }
                });
            });

            // =========================
            // SUBMIT (delegated)
            // =========================
            $(document).on('click', '#btn-submit-mid', function() {
                const tabId = '#tab-mid';
                const ids = (STATE.draftIds?.midDraftIdpIds || []);

                if (!ids.length) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Tidak ada Draft baru untuk disubmit.' });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Submit?',
                    text: `Anda akan mengirim ${ids.length} item Draft. Pastikan semua data sudah benar!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Submit!',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    const $btn = $('#btn-submit-mid');
                    const original = $btn.html();
                    $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm"></span> Submitting...`);

                    $.ajax({
                        url: URL_SUBMIT_MID,
                        method: 'POST',
                        data: { _token: csrfToken(), idp_id: ids },
                        success: function(res) {
                            $btn.prop('disabled', false).html(original);
                            Swal.fire({ icon: 'success', title: 'Success!', text: res.message || 'Draft Mid-Year berhasil disubmit.' });
                            refreshData(tabId);
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html(original);
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat submit.';
                            Swal.fire({ icon: 'error', title: 'Error!', text: msg });
                        }
                    });
                });
            });

            $(document).on('click', '#btn-submit-one', function() {
                const tabId = '#tab-one';
                const ids = (STATE.draftIds?.oneDraftIdpIds || []);

                if (!ids.length) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Tidak ada Draft baru untuk disubmit.' });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Submit?',
                    text: `Anda akan mengirim ${ids.length} item Draft. Pastikan semua data sudah benar!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Submit!',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    const $btn = $('#btn-submit-one');
                    const original = $btn.html();
                    $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm"></span> Submitting...`);

                    $.ajax({
                        url: URL_SUBMIT_ONE,
                        method: 'POST',
                        data: { _token: csrfToken(), idp_id: ids },
                        success: function(res) {
                            $btn.prop('disabled', false).html(original);
                            Swal.fire({ icon: 'success', title: 'Success!', text: res.message || 'Draft One-Year berhasil disubmit.' });
                            refreshData(tabId);
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html(original);
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat submit.';
                            Swal.fire({ icon: 'error', title: 'Error!', text: msg });
                        }
                    });
                });
            });

            // ============= INIT load ============
            const savedTab = localStorage.getItem(TAB_STORE_KEY) || '#tab-mid';
            refreshData(savedTab);
        });
    </script>
@endpush
