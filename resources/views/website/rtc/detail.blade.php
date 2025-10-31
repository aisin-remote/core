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
        <div style="position: absolute; top: 20px; right: 20px; z-index: 10000; display: flex; gap: 8px;">
            <button id="btn-pdf" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">PDF</button>
            <button id="btn-png" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">PNG</button>
            <button id="btn-svg" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">SVG</button>
            <button id="btn-csv" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">CSV</button>
        </div>

        <div id="orgchart-container" style="width: 100%; height: 100vh;"></div>
    </div>
@endsection

@push('custom-css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            background: #101114;
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
            padding: .4rem .7rem;
            border-radius: .5rem;
            border: 1px solid #374151;
        }

        .export-btn:hover {
            background: #111827;
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
                '<feGaussianBlur in="SourceAlpha" stdDeviation="3"></feGaussianBlur>' +
                '<feOffset dx="0" dy="2" result="offsetblur"></feOffset>' +
                '<feComponentTransfer><feFuncA type="linear" slope=".35"/></feComponentTransfer>' +
                '<feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>' +
                '</filter>' +
                '<clipPath id="clipPhoto"><circle cx="' + CX + '" cy="' + CY + '" r="' + (AV / 2) +
                '"/></clipPath>';

            // default node (punya ST/MT/LT rows)
            OrgChart.templates.factory.node =
                '<rect x="0" y="0" rx="16" ry="16" width="' + W + '" height="' + H +
                '" fill="#fff" stroke="#e5e7eb" filter="url(#dropShadow)"></rect>' +
                '<rect x="0" y="0" rx="16" ry="16" width="' + W + '" height="' + HDR + '" fill="{val}"></rect>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="155" text-anchor="start">Grade</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="175" text-anchor="start">Age</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="195" text-anchor="start">LOS</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="215" text-anchor="start">LCP</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="245" text-anchor="start">S/T</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
                '" y="265" text-anchor="start">M/T</text>' +
                '<text style="font-size:13px;fill:#111827" x="' + LEFTX +
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
            OrgChart.templates.factory.field_6 =
                '<text style="font-size:13px;fill:#111827" x="' + RIGHTX +
                '" y="245" text-anchor="end">{val}</text>';
            OrgChart.templates.factory.field_7 =
                '<text style="font-size:13px;fill:#111827" x="' + RIGHTX +
                '" y="265" text-anchor="end">{val}</text>';
            OrgChart.templates.factory.field_8 =
                '<text style="font-size:13px;fill:#111827" x="' + RIGHTX +
                '" y="285" text-anchor="end">{val}</text>';
            OrgChart.templates.factory.field_9 =
                '<text style="font-size:11px;fill:#9ca3af" x="' + CX + '" y="' + (H - 14) +
                '" text-anchor="middle">(grade, age, HAV)</text>';

            // template tanpa ST/MT/LT, dipakai PRESIDENT, VPD, PLANT, dll
            OrgChart.templates.factoryRoot = Object.assign({}, OrgChart.templates.factory);
            OrgChart.templates.factoryRoot.node =
                '<rect x="0" y="0" rx="16" ry="16" width="' + W + '" height="' + H +
                '" fill="#fff" stroke="#e5e7eb" filter="url(#dropShadow)"></rect>' +
                '<rect x="0" y="0" rx="16" ry="16" width="' + W + '" height="' + HDR + '" fill="{val}"></rect>' +
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

            const clamp = (s, max = 42) => (s && s.length > max ? (s.slice(0, max - 1) + '…') : (s || '-'));

            /* ================== buildChartData ================== */
            function buildChartData(mainObj, managersArr) {
                const nodes = [];
                let ai = 0;
                const nid = () => 'n' + (ai++);

                const rootId = 'root';

                // selalu render root (phantom / normal)
                const rootTags = [];
                if (mainObj.phantom) {
                    rootTags.push('phantom'); // jadi invisible
                }
                if (hideMainPlans) {
                    // hide ST/MT/LT
                    rootTags.push('no-plans');
                }

                // bikin root node
                nodes.push({
                    id: rootId,
                    department: clamp(mainObj.title, 64),
                    name: clamp(mainObj.person?.name, 48),
                    grade: mainObj.person?.grade ?? '-',
                    age: mainObj.person?.age ?? '-',
                    los: mainObj.person?.los ?? '-',
                    lcp: mainObj.person?.lcp ?? '-',
                    cand_st: hideMainPlans ? '' : clamp(
                        `${mainObj.shortTerm?.name ?? '-'} (${mainObj.shortTerm?.grade ?? '-'}, ${mainObj.shortTerm?.age ?? '-'})`,
                        48),
                    cand_mt: hideMainPlans ? '' : clamp(
                        `${mainObj.midTerm?.name ?? '-'} (${mainObj.midTerm?.grade ?? '-'}, ${mainObj.midTerm?.age ?? '-'})`,
                        48),
                    cand_lt: hideMainPlans ? '' : clamp(
                        `${mainObj.longTerm?.name ?? '-'} (${mainObj.longTerm?.grade ?? '-'}, ${mainObj.longTerm?.age ?? '-'})`,
                        48),
                    color: colorMap[mainObj.colorClass] || '#0ea5e9',
                    img: mainObj.person?.photo || null,
                    tags: rootTags.length ? rootTags : undefined
                });

                // helper rekursif utk subtree normal
                function emitStd(node, parentId) {
                    const thisId = nid();
                    const baseTags = [];
                    if (node.no_plans) baseTags.push('no-plans'); // render template factoryRoot

                    nodes.push({
                        id: thisId,
                        pid: parentId,
                        department: clamp(node.title, 64),
                        name: clamp(node.person?.name, 48),
                        grade: node.person?.grade ?? '-',
                        age: node.person?.age ?? '-',
                        los: node.person?.los ?? '-',
                        lcp: node.person?.lcp ?? '-',
                        cand_st: node.no_plans ? '' : clamp(
                            `${node.shortTerm?.name ?? '-'} (${node.shortTerm?.grade ?? '-'}, ${node.shortTerm?.age ?? '-'})`,
                            48),
                        cand_mt: node.no_plans ? '' : clamp(
                            `${node.midTerm?.name ?? '-'} (${node.midTerm?.grade ?? '-'}, ${node.midTerm?.age ?? '-'})`,
                            48),
                        cand_lt: node.no_plans ? '' : clamp(
                            `${node.longTerm?.name ?? '-'} (${node.longTerm?.grade ?? '-'}, ${node.longTerm?.age ?? '-'})`,
                            48),
                        color: colorMap[node.colorClass] || '#22c55e',
                        img: node.person?.photo || null,
                        tags: baseTags.length ? baseTags : undefined
                    });

                    (node.supervisors || []).forEach(ch => emitStd(ch, thisId));
                    return thisId;
                }

                // PRESIDENT dan VPD diletakkan sebagai anak langsung root
                // lalu subtree plant turun dari PRESIDENT
                managersArr.forEach(m => {
                    const idManager = emitStd(m, rootId);
                    // emitStd juga akan rekursif ke supervisors[], jadi subtree plant bakal otomatis jadi anak PRESIDENT.
                    // VPD tidak punya supervisors, jadi akan cuma berdiri di bawah root sejajar PRESIDENT.
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
                    enableSearch: false,
                    enableDragDrop: false,
                    enableZoom: true,
                    enablePan: true,
                    scaleInitial: OrgChart.match.boundary,
                    scaleMin: 0.2,
                    scaleMax: 2.2,
                    nodeMouseClick: OrgChart.action.none,

                    // mapping tag -> template
                    tags: {
                        'no-plans': {
                            template: 'factoryRoot'
                        }, // hilangin S/T–M/T–L/T
                        'phantom': {
                            template: 'phantom',
                            subTreeConfig: {
                                columns: 2
                            } // anak2 root (PRESIDENT & VPD) sejajar horizontal
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
                    }), 800);
                });
                $('#btn-png').addClass('export-btn').on('click', () => {
                    chart.fit();
                    setTimeout(() => chart.exportPNG({
                        filename: 'chart.png'
                    }), 800);
                });
                $('#btn-svg').addClass('export-btn').on('click', () => {
                    chart.fit();
                    setTimeout(() => chart.exportSVG({
                        filename: 'chart.svg'
                    }), 800);
                });
                $('#btn-csv').addClass('export-btn').on('click', () => {
                    chart.fit();
                    setTimeout(() => chart.exportCSV({
                        filename: 'chart.csv'
                    }), 800);
                });

                chart.fit();
            }).catch(err => {
                console.error(err);
                loadingOverlay.addClass('d-none').removeClass('d-flex');

                const fallbackNodes = buildChartData(main, managers);

                new OrgChart(document.getElementById('orgchart-container'), {
                    template: "factory",
                    mode: "dark",
                    enableSearch: false,
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
