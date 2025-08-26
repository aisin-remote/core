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
        <!-- Floating export buttons -->
        <div style="position: absolute; top: 20px; right: 20px; z-index: 10000; display: flex; gap: 8px;">
            <button id="btn-pdf" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">PDF</button>
            <button id="btn-png" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">PNG</button>
            <button id="btn-svg" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">SVG</button>
            <button id="btn-csv" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">CSV</button>
        </div>

        <!-- Chart container -->
        <div id="orgchart-container" style="width: 100%; height: 100vh;"></div>
    </div>
@endsection

@push('custom-css')
    <!-- Font untuk keterbacaan tinggi -->
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
    </script>

    <script>
        $(function() {
            /* ================== TEMPLATE ================== */
            const W = 360,
                H = 430,
                HDR = 76; // header lebih tinggi biar muat foto besar
            const CX = W / 2;
            const AV = 64; // diameter avatar (px) → 64px
            const CY = (AV / 2) + 18; // posisi Y pusat avatar di dalam header
            const LEFTX = 26,
                RIGHTX = W - 26;

            // Base dari template "ana" supaya kompatibel
            OrgChart.templates.factory = Object.assign({}, OrgChart.templates.ana);
            OrgChart.templates.factory.size = [W, H];

            // Defs: filter shadow + clip photo (tanpa newline diawal)
            OrgChart.templates.factory.defs =
                '<filter id="dropShadow" x="-40%" y="-40%" width="180%" height="180%">' +
                '<feGaussianBlur in="SourceAlpha" stdDeviation="3"></feGaussianBlur>' +
                '<feOffset dx="0" dy="2" result="offsetblur"></feOffset>' +
                '<feComponentTransfer><feFuncA type="linear" slope=".35"/></feComponentTransfer>' +
                '<feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>' +
                '</filter>' +
                // cincin putih tipis sebagai border avatar (opsional)
                '<clipPath id="clipPhoto"><circle cx="' + CX + '" cy="' + CY + '" r="' + (AV / 2) +
                '"/></clipPath>';

            // Node body + header + LABEL statis (label kiri dibuat statis disini)
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

            // Foto kecil
            OrgChart.templates.factory.img_0 =
                '<image clip-path="url(#clipPhoto)" xlink:href="{val}" ' +
                'x="' + (CX - AV / 2) + '" y="' + (CY - AV / 2) + '" ' +
                'width="' + AV + '" height="' + AV + '" preserveAspectRatio="xMidYMid slice"></image>';

            // Field dinamis (nilai-nilai di kanan & judul)
            OrgChart.templates.factory.field_0 =
                '<text style="font-size:16px;font-weight:700;fill:#111827" x="' + CX +
                '" y="105" text-anchor="middle">{val}</text>'; // Department/Title
            OrgChart.templates.factory.field_1 =
                '<text style="font-size:13px;fill:#6b7280" x="' + CX +
                '" y="128" text-anchor="middle">{val}</text>'; // Nama

            OrgChart.templates.factory.field_2 =
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + RIGHTX +
                '" y="155" text-anchor="end">{val}</text>'; // Grade
            OrgChart.templates.factory.field_3 =
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + RIGHTX +
                '" y="175" text-anchor="end">{val}</text>'; // Age
            OrgChart.templates.factory.field_4 =
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + RIGHTX +
                '" y="195" text-anchor="end">{val}</text>'; // LOS
            OrgChart.templates.factory.field_5 =
                '<text style="font-size:13px;font-weight:600;fill:#111827" x="' + RIGHTX +
                '" y="215" text-anchor="end">{val}</text>'; // LCP

            // Kandidat (sudah di-clamp via JS biar aman)
            OrgChart.templates.factory.field_6 =
                '<text style="font-size:13px;fill:#111827" x="' + RIGHTX +
                '" y="245" text-anchor="end">{val}</text>'; // ST
            OrgChart.templates.factory.field_7 =
                '<text style="font-size:13px;fill:#111827" x="' + RIGHTX +
                '" y="265" text-anchor="end">{val}</text>'; // MT
            OrgChart.templates.factory.field_8 =
                '<text style="font-size:13px;fill:#111827" x="' + RIGHTX +
                '" y="285" text-anchor="end">{val}</text>'; // LT
            OrgChart.templates.factory.field_9 =
                '<text style="font-size:11px;fill:#9ca3af" x="' + CX + '" y="' + (H - 14) +
                '" text-anchor="middle">(grade, age, HAV)</text>';

            /* ================== WARNA ================== */
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

            /* ================== BUILD DATA ================== */
            const clamp = (s, max = 42) => (s && s.length > max ? (s.slice(0, max - 1) + '…') : (s || '-'));

            function buildChartData(main, managers) {
                const nodes = [];
                const rootId = 'root';

                nodes.push({
                    id: rootId,
                    department: clamp(main.title, 64),
                    name: clamp(main.person?.name, 48),
                    grade: main.person?.grade ?? '-',
                    age: main.person?.age ?? '-',
                    los: main.person?.los ?? '-',
                    lcp: main.person?.lcp ?? '-',
                    cand_st: clamp(
                        `${main.shortTerm?.name ?? '-'} (${main.shortTerm?.grade ?? '-'}, ${main.shortTerm?.age ?? '-'})`,
                        48),
                    cand_mt: clamp(
                        `${main.midTerm?.name ?? '-'} (${main.midTerm?.grade ?? '-'}, ${main.midTerm?.age ?? '-'})`,
                        48),
                    cand_lt: clamp(
                        `${main.longTerm?.name ?? '-'} (${main.longTerm?.grade ?? '-'}, ${main.longTerm?.age ?? '-'})`,
                        48),
                    color: colorMap[main.colorClass] || '#0ea5e9',
                    img: main.person?.photo || null
                });

                managers.forEach((m, i) => {
                    const mid = `m-${i}`;

                    if (!m.skipManagerNode) {
                        nodes.push({
                            id: mid,
                            pid: rootId,
                            department: clamp(m.title, 64),
                            name: clamp(m.person?.name, 48),
                            grade: m.person?.grade ?? '-',
                            age: m.person?.age ?? '-',
                            los: m.person?.los ?? '-',
                            lcp: m.person?.lcp ?? '-',
                            cand_st: clamp(
                                `${m.shortTerm?.name ?? '-'} (${m.shortTerm?.grade ?? '-'}, ${m.shortTerm?.age ?? '-'})`,
                                48),
                            cand_mt: clamp(
                                `${m.midTerm?.name ?? '-'} (${m.midTerm?.grade ?? '-'}, ${m.midTerm?.age ?? '-'})`,
                                48),
                            cand_lt: clamp(
                                `${m.longTerm?.name ?? '-'} (${m.longTerm?.grade ?? '-'}, ${m.longTerm?.age ?? '-'})`,
                                48),
                            color: colorMap[m.colorClass] || '#22c55e',
                            img: m.person?.photo || null
                        });
                    }

                    const parentId = m.skipManagerNode ? rootId : mid;

                    (m.supervisors || []).forEach((s, j) => {
                        nodes.push({
                            id: `m-${i}-s-${j}`,
                            pid: parentId,
                            department: clamp(s.title, 64),
                            name: clamp(s.person?.name, 48),
                            grade: s.person?.grade ?? '-',
                            age: s.person?.age ?? '-',
                            los: s.person?.los ?? '-',
                            lcp: s.person?.lcp ?? '-',
                            cand_st: clamp(
                                `${s.shortTerm?.name ?? '-'} (${s.shortTerm?.grade ?? '-'}, ${s.shortTerm?.age ?? '-'})`,
                                48),
                            cand_mt: clamp(
                                `${s.midTerm?.name ?? '-'} (${s.midTerm?.grade ?? '-'}, ${s.midTerm?.age ?? '-'})`,
                                48),
                            cand_lt: clamp(
                                `${s.longTerm?.name ?? '-'} (${s.longTerm?.grade ?? '-'}, ${s.longTerm?.age ?? '-'})`,
                                48),
                            color: colorMap[s.colorClass] || '#ef4444',
                            img: s.person?.photo || null
                        });
                    });
                });

                return nodes;
            }

            /* ================== LOADING ================== */
            const loadingOverlay = $('#loading-overlay');
            const loadingProgress = $('#loading-progress');

            async function countTotalImages(payload) {
                let c = 0;
                if (payload.main.person?.photo) c++;
                payload.managers.forEach(m => {
                    if (m.person?.photo) c++;
                    (m.supervisors || []).forEach(s => {
                        if (s.person?.photo) c++;
                    });
                });
                return c;
            }

            function updateProgress(loaded, total) {
                loadingProgress.text(`Mengunduh gambar ${loaded}/${total}`);
            }

            async function safeConvertToBase64(url) {
                if (!url) return null;
                try {
                    const u = url.startsWith('//') ? (location.protocol + url) :
                        (url.startsWith('/') ? (location.origin + url) : url);
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

            async function prepareChartData(main, managers) {
                const processedMain = {
                    ...main
                };
                const processedManagers = JSON.parse(JSON.stringify(managers));

                let total = await countTotalImages({
                    main,
                    managers
                });
                let loaded = 0;
                updateProgress(0, total);

                if (processedMain.person?.photo) {
                    processedMain.person.photo = await safeConvertToBase64(processedMain.person.photo);
                    updateProgress(++loaded, total);
                }
                await Promise.all(processedManagers.map(async m => {
                    if (m.person?.photo) {
                        m.person.photo = await safeConvertToBase64(m.person.photo);
                        updateProgress(++loaded, total);
                    }
                    if (m.supervisors) {
                        for (const s of m.supervisors) {
                            if (s.person?.photo) {
                                s.person.photo = await safeConvertToBase64(s.person.photo);
                                updateProgress(++loaded, total);
                            }
                        }
                    }
                }));
                return {
                    main: processedMain,
                    managers: processedManagers
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

                const nodes = buildChartData(main, managers);
                new OrgChart(document.getElementById('orgchart-container'), {
                    template: "factory",
                    mode: "dark",
                    enableSearch: false,
                    nodeMouseClick: OrgChart.action.none,
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
            });
        });
    </script>
@endpush
