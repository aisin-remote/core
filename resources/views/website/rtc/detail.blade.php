@extends('layouts.root.view-rtc')

@section('title', $title ?? 'RTC')
@section('breadcrumbs', $title ?? 'RTC')

@section('main')
    <!-- Loading Indicator Bootstrap 5 -->
    <div id="loading-overlay"
        class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-75 z-50">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5 class="text-white mb-1">Memuat Data Organisasi</h5>
            <p class="text-light mb-0" id="loading-progress">Mengunduh gambar 0/0</p>
        </div>
    </div>

    <div style="position: relative;">
        <!-- Floating export toolbar -->
        <div class="position-absolute top-0 end-0 p-3" style="z-index: 10000;">
            <div
                class="d-flex flex-wrap gap-2 align-items-center justify-content-end shadow-sm rounded-pill px-3 py-2 bg-dark bg-opacity-75">
                <span class="text-light small me-2 d-none d-md-inline">Export</span>
                <button id="btn-pdf"
                    class="btn btn-sm btn-outline-light border-0 px-3 py-1 d-flex align-items-center gap-1">
                    <i class="bi bi-file-earmark-pdf"></i><span class="d-none d-md-inline">PDF</span>
                </button>
                <button id="btn-png"
                    class="btn btn-sm btn-outline-light border-0 px-3 py-1 d-flex align-items-center gap-1">
                    <i class="bi bi-file-earmark-image"></i><span class="d-none d-md-inline">PNG</span>
                </button>
                <button id="btn-svg"
                    class="btn btn-sm btn-outline-light border-0 px-3 py-1 d-flex align-items-center gap-1">
                    <i class="bi bi-filetype-svg"></i><span class="d-none d-md-inline">SVG</span>
                </button>
                <button id="btn-csv"
                    class="btn btn-sm btn-outline-light border-0 px-3 py-1 d-flex align-items-center gap-1">
                    <i class="bi bi-table"></i><span class="d-none d-md-inline">CSV</span>
                </button>
            </div>
        </div>

        <div id="orgchart-container" style="width: 100%; height: 100vh;"></div>
    </div>
@endsection

@push('custom-css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
    {{-- Kalau pakai Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            background: radial-gradient(circle at top, #1f2933 0, #020617 45%, #020617 100%);
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Arial, sans-serif;
        }

        #orgchart-container {
            width: 100%;
            height: 100vh;
            overflow: auto;
        }

        .export-btn {
            background: #1f2937;
            color: #fff;
            padding: .35rem .75rem;
            border-radius: 999px;
            border: 1px solid #374151;
            font-size: .8rem;
        }

        .export-btn:hover {
            background: #111827;
        }

        /* Smooth scrollbars (optional) */
        #orgchart-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        #orgchart-container::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }

        #orgchart-container::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 999px;
        }

        #orgchart-container::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const main = @json($main);
        const managers = @json($managers);

        const hideMainPlans = @json($hideMainPlans ?? false);
        const NO_ROOT = @json($noRoot ?? false); // sekarang utk company = false
        const GROUP_TOP = false; // kita matikan group model lama
    </script>

    <script>
        $(function() {
            /* ================== TEMPLATE ================== */
            const W = 360,
                H = 430,
                HDR = 76;
            const CX = W / 2,
                AV = 64,
                CY = (AV / 2) + 18;
            const LEFTX = 26,
                RIGHTX = W - 26;

            OrgChart.templates.factory = Object.assign({}, OrgChart.templates.ana);
            OrgChart.templates.factory.size = [W, H];

            OrgChart.templates.factory.defs =
                '<filter id="dropShadow" x="-40%" y="-40%" width="180%" height="180%">' +
                '<feGaussianBlur in="SourceAlpha" stdDeviation="4"></feGaussianBlur>' +
                '<feOffset dx="0" dy="3" result="offsetblur"></feOffset>' +
                '<feComponentTransfer><feFuncA type="linear" slope=".35"/></feComponentTransfer>' +
                '<feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>' +
                '</filter>' +
                '<clipPath id="clipPhoto"><circle cx="' + CX + '" cy="' + CY + '" r="' + (AV / 2) +
                '"/></clipPath>';

            // ================= NODE DENGAN S/T M/T L/T =================
            OrgChart.templates.factory.node =
                '<rect x="0" y="0" rx="18" ry="18" width="' + W + '" height="' + H +
                '" fill="#ffffff" stroke="#e5e7eb" filter="url(#dropShadow)"></rect>' +
                '<rect x="0" y="0" rx="18" ry="18" width="' + W + '" height="' + HDR +
                '" fill="{val}"></rect>' +
                // label kiri
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="155" text-anchor="start">Grade</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="175" text-anchor="start">Age</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="195" text-anchor="start">LOS</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="215" text-anchor="start">LCP</text>' +
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + LEFTX +
                '" y="245" text-anchor="start">S/T</text>' +
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + LEFTX +
                '" y="265" text-anchor="start">M/T</text>' +
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + LEFTX +
                '" y="285" text-anchor="start">L/T</text>';

            OrgChart.templates.factory.img_0 =
                '<image clip-path="url(#clipPhoto)" xlink:href="{val}" x="' + (CX - AV / 2) + '" y="' + (CY - AV /
                    2) + '" width="' + AV + '" height="' + AV + '" preserveAspectRatio="xMidYMid slice"></image>';

            OrgChart.templates.factory.field_0 =
                '<text style="font-size:16px;font-weight:700;fill:#111827" x="' + CX +
                '" y="105" text-anchor="middle">{val}</text>';

            OrgChart.templates.factory.field_1 =
                '<text style="font-size:13px;fill:#6b7280" x="' + CX +
                '" y="128" text-anchor="middle">{val}</text>';

            // value kolom kanan (Grade / Age / LOS / LCP)
            OrgChart.templates.factory.field_2 =
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + RIGHTX +
                '" y="155" text-anchor="end">{val}</text>';
            OrgChart.templates.factory.field_3 =
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + RIGHTX +
                '" y="175" text-anchor="end">{val}</text>';
            OrgChart.templates.factory.field_4 =
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + RIGHTX +
                '" y="195" text-anchor="end">{val}</text>';
            OrgChart.templates.factory.field_5 =
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + RIGHTX +
                '" y="215" text-anchor="end">{val}</text>';

            // kandidat: geser sedikit ke kanan dari label, anchor start
            const CAND_X = LEFTX + 40;

            OrgChart.templates.factory.field_6 =
                '<text style="font-size:12px;fill:#374151" x="' + CAND_X +
                '" y="245" text-anchor="start">{val}</text>';
            OrgChart.templates.factory.field_7 =
                '<text style="font-size:12px;fill:#374151" x="' + CAND_X +
                '" y="265" text-anchor="start">{val}</text>';
            OrgChart.templates.factory.field_8 =
                '<text style="font-size:12px;fill:#374151" x="' + CAND_X +
                '" y="285" text-anchor="start">{val}</text>';

            OrgChart.templates.factory.field_9 =
                '<text style="font-size:10px;fill:#9ca3af" x="' + CX + '" y="' + (H - 14) +
                '" text-anchor="middle">S/T • M/T • L/T • HAV</text>';

            // ================= NODE ROOT TANPA S/T M/T L/T =================
            OrgChart.templates.factoryRoot = Object.assign({}, OrgChart.templates.factory);
            OrgChart.templates.factoryRoot.node =
                '<rect x="0" y="0" rx="18" ry="18" width="' + W + '" height="' + H +
                '" fill="#ffffff" stroke="#e5e7eb" filter="url(#dropShadow)"></rect>' +
                '<rect x="0" y="0" rx="18" ry="18" width="' + W + '" height="' + HDR +
                '" fill="{val}"></rect>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="155" text-anchor="start">Grade</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="175" text-anchor="start">Age</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="195" text-anchor="start">LOS</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="215" text-anchor="start">LCP</text>';

            // template phantom: node parent tak terlihat (dipakai utk company root)
            OrgChart.templates.phantom = Object.assign({}, OrgChart.templates.ana);
            OrgChart.templates.phantom.size = [1, 1]; // super kecil
            OrgChart.templates.phantom.node =
                '<rect x="0" y="0" width="1" height="1" fill="transparent" stroke="transparent"></rect>';
            OrgChart.templates.phantom.field_0 = '';
            OrgChart.templates.phantom.field_1 = '';
            OrgChart.templates.phantom.img_0 = '';

            /* ================== warna ================== */
            const colorMap = {
                'color-1': '#0ea5e9',
                'color-2': '#22c55e',
                'color-3': '#ef4444',
                'color-4': '#f59e0b',
                'color-5': '#8b5cf6',
                'color-6': '#14b8a6',
                'color-7': '#fb923c',
                'color-8': '#06b6d4',
                'color-9': '#a855f7',
                'color-10': '#10b981',
                'color-11': '#eab308',
                'color-12': '#ec4899',
                'color-13': '#34d399',
                'color-14': '#3b82f6'
            };

            // mapping quadrant -> title (HAV)
            const quadrantTitles = {
                1: 'Star',
                2: 'Future Star',
                3: 'Future Star',
                4: 'Potential Candidate',
                5: 'Raw Diamond',
                6: 'Candidate',
                7: 'Top Performer',
                8: 'Strong Performer',
                9: 'Career Person',
                10: 'Most Unfit Employee',
                11: 'Unfit Employee',
                12: 'Problem Employee',
                13: 'Maximal Contributor',
                14: 'Contributor',
                15: 'Minimal Contributor',
                16: 'Dead Wood'
            };

            const clamp = (s, max = 72) =>
                (s && s.length > max ? (s.slice(0, max - 1) + '…') : (s || '-'));

            // format label kandidat + HAV
            function buildCandidateLabel(candidate) {
                if (!candidate || !candidate.name || candidate.name === '-') {
                    return '-';
                }

                const base = `${candidate.name} (${candidate.grade ?? '-'}, ${candidate.age ?? '-'})`;

                const assets = Array.isArray(candidate.human_assets) ? candidate.human_assets : [];
                if (!assets.length) {
                    return base;
                }

                // asumsi backend sudah orderByDesc(year), ambil yang terbaru
                const asset = assets[0] || {};
                const q = parseInt(asset.quadrant ?? asset.quadrant_id ?? 0, 10);
                const year = asset.year ?? '';
                const title = quadrantTitles[q] || 'Unknown';

                return `${base} • ${title}${year ? ' ' + year : ''}`;
            }

            /* ================== buildChartData ================== */
            function buildChartData(mainObj, managersArr) {
                const nodes = [];
                let ai = 0;
                const nid = () => 'n' + (ai++);

                const rootId = 'root';

                const rootTags = [];
                if (mainObj.phantom) {
                    rootTags.push('phantom'); // jadi invisible
                }
                if (hideMainPlans) {
                    rootTags.push('no-plans');
                }

                nodes.push({
                    id: rootId,
                    department: clamp(mainObj.title, 64),
                    name: clamp(mainObj.person?.name, 48),
                    grade: mainObj.person?.grade ?? '-',
                    age: mainObj.person?.age ?? '-',
                    los: mainObj.person?.los ?? '-',
                    lcp: mainObj.person?.lcp ?? '-',
                    cand_st: hideMainPlans ? '' : clamp(buildCandidateLabel(mainObj.shortTerm), 90),
                    cand_mt: hideMainPlans ? '' : clamp(buildCandidateLabel(mainObj.midTerm), 90),
                    cand_lt: hideMainPlans ? '' : clamp(buildCandidateLabel(mainObj.longTerm), 90),
                    color: colorMap[mainObj.colorClass] || '#0ea5e9',
                    img: mainObj.person?.photo || null,
                    field_9: 'S/T • M/T • L/T • HAV',
                    tags: rootTags.length ? rootTags : undefined
                });

                function emitStd(node, parentId) {
                    const skip = node.skipManagerNode === true ||
                        node.skipManagerNode === 1 ||
                        node.skipManagerNode === '1';

                    const thisId = skip ? parentId : nid();
                    const baseTags = [];
                    if (node.no_plans) baseTags.push('no-plans');

                    if (!skip) {
                        nodes.push({
                            id: thisId,
                            pid: parentId,
                            department: clamp(node.title, 64),
                            name: clamp(node.person?.name, 48),
                            grade: node.person?.grade ?? '-',
                            age: node.person?.age ?? '-',
                            los: node.person?.los ?? '-',
                            lcp: node.person?.lcp ?? '-',
                            cand_st: node.no_plans ? '' : clamp(buildCandidateLabel(node.shortTerm), 90),
                            cand_mt: node.no_plans ? '' : clamp(buildCandidateLabel(node.midTerm), 90),
                            cand_lt: node.no_plans ? '' : clamp(buildCandidateLabel(node.longTerm), 90),
                            color: colorMap[node.colorClass] || '#22c55e',
                            img: node.person?.photo || null,
                            field_9: node.no_plans ? '' : 'S/T • M/T • L/T • HAV',
                            tags: baseTags.length ? baseTags : undefined
                        });
                    }

                    (node.supervisors || []).forEach(ch => emitStd(ch, thisId));
                    return thisId;
                }


                managersArr.forEach(m => {
                    emitStd(m, rootId);
                });

                return nodes;
            }

            /* ================== LOADING & IMAGE BASE64 ================== */
            const loadingOverlay = $('#loading-overlay');
            const loadingProgress = $('#loading-progress');

            async function countTotalImages(payload) {
                let c = 0;
                if (payload.main.person?.photo) c++;
                const walk = (arr) => {
                    (arr || []).forEach(n => {
                        if (n.person?.photo) c++;
                        walk(n.supervisors || []);
                    });
                };
                walk(payload.managers || []);
                return c;
            }

            function updateProgress(loaded, total) {
                loadingProgress.text(`Mengunduh gambar ${loaded}/${total}`);
            }

            async function safeConvertToBase64(url) {
                if (!url) return null;
                try {
                    const u = url.startsWith('//') ? (location.protocol + url) :
                        (url.startsWith('/') ? (location.origin + url) :
                            url);
                    const res = await fetch(u + (u.includes('?') ? '&' : '?') + 't=' + Date.now(), {
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error(res.status);
                    const blob = await res.blob();
                    return await new Promise(resolve => {
                        const r = new FileReader();
                        r.onloadend = () => resolve(r.result);
                        r.readAsDataURL(blob);
                    });
                } catch {
                    return null;
                }
            }

            async function prepareChartData(mainObj, managersArr) {
                const processedMain = {
                    ...mainObj
                };
                const deepManagers = JSON.parse(JSON.stringify(managersArr));

                let total = await countTotalImages({
                    main: mainObj,
                    managers: managersArr
                });
                let loaded = 0;
                updateProgress(0, total);

                if (processedMain.person?.photo) {
                    processedMain.person.photo = await safeConvertToBase64(processedMain.person.photo);
                    updateProgress(++loaded, total);
                }

                async function walk(list) {
                    for (const n of (list || [])) {
                        if (n.person?.photo) {
                            n.person.photo = await safeConvertToBase64(n.person.photo);
                            updateProgress(++loaded, total);
                        }
                        if (n.supervisors) await walk(n.supervisors);
                    }
                }

                await walk(deepManagers);

                return {
                    main: processedMain,
                    managers: deepManagers
                };
            }

            /* ================== RENDER ================== */
            loadingOverlay.removeClass('d-none').addClass('d-flex');

            prepareChartData(main, managers).then(({
                main,
                managers
            }) => {
                const nodes = buildChartData(main, managers);

                loadingOverlay.fadeOut(250, () => loadingOverlay.addClass('d-none').removeClass('d-flex'));

                const chart = new OrgChart(document.getElementById('orgchart-container'), {
                    template: "factory",
                    mode: "dark",
                    enableSearch: true,
                    enableDragDrop: false,
                    enableZoom: true,
                    enablePan: true,
                    scaleInitial: OrgChart.match.boundary,
                    scaleMin: 0.3,
                    scaleMax: 2.2,
                    nodeMouseClick: OrgChart.action.none,

                    tags: {
                        'no-plans': {
                            template: 'factoryRoot'
                        },
                        'phantom': {
                            template: 'phantom',
                            subTreeConfig: {
                                columns: 2
                            }
                        }
                    },

                    nodeBinding: {
                        node: "color",
                        img_0: "img",
                        field_0: "department",
                        field_1: "name",
                        field_2: "grade",
                        field_3: "age",
                        field_4: "los",
                        field_5: "lcp",
                        field_6: "cand_st",
                        field_7: "cand_mt",
                        field_8: "cand_lt",
                        field_9: "field_9"
                    },
                    nodes
                });

                // Export
                $('#btn-pdf').addClass('export-btn').on('click', () => {
                    chart.fit();
                    setTimeout(() => chart.exportPDF({
                        filename: 'chart.pdf'
                    }), 600);
                });
                $('#btn-png').addClass('export-btn').on('click', () => {
                    chart.fit();
                    setTimeout(() => chart.exportPNG({
                        filename: 'chart.png'
                    }), 600);
                });
                $('#btn-svg').addClass('export-btn').on('click', () => {
                    chart.fit();
                    setTimeout(() => chart.exportSVG({
                        filename: 'chart.svg'
                    }), 600);
                });
                $('#btn-csv').addClass('export-btn').on('click', () => {
                    chart.fit();
                    setTimeout(() => chart.exportCSV({
                        filename: 'chart.csv'
                    }), 600);
                });

                chart.fit();
            }).catch(err => {
                console.error(err);
                loadingOverlay.addClass('d-none').removeClass('d-flex');

                const fallbackNodes = buildChartData(main, managers);

                new OrgChart(document.getElementById('orgchart-container'), {
                    template: "factory",
                    mode: "dark",
                    enableSearch: true,
                    nodeMouseClick: OrgChart.action.none,
                    tags: {
                        'no-plans': {
                            template: 'factoryRoot'
                        },
                        'phantom': {
                            template: 'phantom',
                            subTreeConfig: {
                                columns: 2
                            }
                        }
                    },
                    nodeBinding: {
                        node: "color",
                        img_0: "img",
                        field_0: "department",
                        field_1: "name",
                        field_2: "grade",
                        field_3: "age",
                        field_4: "los",
                        field_5: "lcp",
                        field_6: "cand_st",
                        field_7: "cand_mt",
                        field_8: "cand_lt",
                        field_9: "field_9"
                    },
                    nodes: fallbackNodes
                });
            });
        });
    </script>
@endpush
