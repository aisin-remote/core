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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            background-color: #111;
            /* Sesuai mode dark chart */
        }

        #orgchart-container {
            width: 100%;
            height: 100vh;
            overflow: auto;
        }
    </style>

@endsection

@push('scripts')
    <script src="https://balkan.app/js/OrgChart.js"></script>

    <script>
        const main = @json($main);
        const managers = @json($managers);
    </script>

    <script>
        $(function() {
            OrgChart.templates.myTemplate = Object.assign({}, OrgChart.templates.ana);
            OrgChart.templates.myTemplate.size = [300, 380];

            OrgChart.templates.myTemplate.node = `
<rect x="0" y="0" width="300" height="380" fill="#ffffff" rx="10" ry="10" stroke="#e0e0e0" stroke-width="1"></rect>
<rect x="0" y="0" width="300" height="50" fill="{val}" rx="10" ry="10"></rect>
`;

            OrgChart.templates.myTemplate.img_0 =
                `<clipPath id="ulaImg"><circle cx="150" cy="45" r="35"></circle></clipPath>
    <image preserveAspectRatio="xMidYMid slice" clip-path="url(#ulaImg)" xlink:href="{val}" x="115" y="12" width="70" height="70"></image>`;

            OrgChart.templates.myTemplate.field_0 =
                `
<text style="font-size: 15px; font-weight: bold;" fill="#000" x="150" y="95" text-anchor="middle">{val}</text>`; // department

            OrgChart.templates.myTemplate.field_1 = `
<text style="font-size: 13px;" fill="#777" x="150" y="115" text-anchor="middle">{val}</text>`; // name

            OrgChart.templates.myTemplate.field_2 = `
<text style="font-size: 13px;" fill="#000" x="50" y="140" text-anchor="start">Grade</text>
<text style="font-size: 13px; font-weight: bold;" fill="#000" x="250" y="140" text-anchor="end">{val}</text>`;

            OrgChart.templates.myTemplate.field_3 = `
<text style="font-size: 13px;" fill="#000" x="50" y="160" text-anchor="start">Age</text>
<text style="font-size: 13px; font-weight: bold;" fill="#000" x="250" y="160" text-anchor="end">{val}</text>`;

            OrgChart.templates.myTemplate.field_4 = `
<text style="font-size: 13px;" fill="#000" x="50" y="180" text-anchor="start">LOS</text>
<text style="font-size: 13px; font-weight: bold;" fill="#000" x="250" y="180" text-anchor="end">{val}</text>`;

            OrgChart.templates.myTemplate.field_5 = `
<text style="font-size: 13px;" fill="#000" x="50" y="200" text-anchor="start">LCP</text>
<text style="font-size: 13px; font-weight: bold;" fill="#000" x="250" y="200" text-anchor="end">{val}</text>`;

            OrgChart.templates.myTemplate.field_6 = `
<text style="font-size: 13px;" fill="#000" x="30" y="230" text-anchor="start">S/T</text>
<text style="font-size: 13px;" fill="#000" x="270" y="230" text-anchor="end">{val}</text>`;

            OrgChart.templates.myTemplate.field_7 = `
<text style="font-size: 13px;" fill="#000" x="30" y="250" text-anchor="start">M/T</text>
<text style="font-size: 13px;" fill="#000" x="270" y="250" text-anchor="end">{val}</text>`;

            OrgChart.templates.myTemplate.field_8 = `
<text style="font-size: 13px;" fill="#000" x="30" y="270" text-anchor="start">L/T</text>
<text style="font-size: 13px;" fill="#000" x="270" y="270" text-anchor="end">{val}</text>`;

            OrgChart.templates.myTemplate.field_9 = `
<text style="font-size: 11px;" fill="#888" x="150" y="350" text-anchor="middle">(gol, usia, HAV)</text>`;

            const colorMap = {
                'color-1': '#007bff',
                'color-2': '#28a745',
                'color-3': '#dc3545',
                'color-4': '#ffc107',
                'color-5': '#6f42c1',
                'color-6': '#20c997',
                'color-7': '#fd7e14',
            }

            function buildChartData(main, managers) {
                let nodes = [];

                const rootId = 'root';
                nodes.push({
                    id: rootId,
                    department: main.title ?? '-',
                    name: main.person.name ?? '-',
                    grade: main.person.grade ?? '-',
                    age: main.person.age ?? '-',
                    los: main.person.los ?? '-',
                    lcp: main.person.lcp ?? '-',
                    cand_st: `${main.shortTerm?.name ?? '-'} (${main.shortTerm?.grade ?? '-'}, ${main.shortTerm?.age ?? '-'})`,
                    cand_mt: `${main.midTerm?.name ?? '-'} (${main.midTerm?.grade ?? '-'}, ${main.midTerm?.age ?? '-'})`,
                    cand_lt: `${main.longTerm?.name ?? '-'} (${main.longTerm?.grade ?? '-'}, ${main.longTerm?.age ?? '-'})`,
                    color: colorMap[main.colorClass] || '#007bff',
                    img: main.person?.photo ? main.person.photo : null
                });

                managers.forEach((manager, i) => {
                    const managerId = `m-${i}`;

                    nodes.push({
                        id: managerId,
                        pid: rootId,
                        department: manager.title ?? '-',
                        name: manager.person?.name ?? '-',
                        grade: manager.person?.grade ?? '-',
                        age: manager.person?.age ?? '-',
                        los: manager.person?.los ?? '-',
                        lcp: manager.person?.lcp ?? '-',
                        cand_st: `${manager.shortTerm?.name ?? '-'} (${manager.shortTerm?.grade ?? '-'}, ${manager.shortTerm?.age ?? '-'})`,
                        cand_mt: `${manager.midTerm?.name ?? '-'} (${manager.midTerm?.grade ?? '-'}, ${manager.midTerm?.age ?? '-'})`,
                        cand_lt: `${manager.longTerm?.name ?? '-'} (${manager.longTerm?.grade ?? '-'}, ${manager.longTerm?.age ?? '-'})`,
                        color: colorMap[manager.colorClass] || '#28a745',
                        img: manager.person?.photo ? manager.person.photo : null
                    });

                    (manager.supervisors ?? []).forEach((spv, j) => {
                        const spvId = `m-${i}-s-${j}`;
                        nodes.push({
                            id: spvId,
                            pid: managerId,
                            department: spv.title ?? '-',
                            name: spv.person?.name ?? '-',
                            grade: spv.person?.grade ?? '-',
                            age: spv.person?.age ?? '-',
                            los: spv.person?.los ?? '-',
                            lcp: spv.person?.lcp ?? '-',
                            cand_st: `${spv.shortTerm?.name ?? '-'} (${spv.shortTerm?.grade ?? '-'}, ${spv.shortTerm?.age ?? '-'})`,
                            cand_mt: `${spv.midTerm?.name ?? '-'} (${spv.midTerm?.grade ?? '-'}, ${spv.midTerm?.age ?? '-'})`,
                            cand_lt: `${spv.longTerm?.name ?? '-'} (${spv.longTerm?.grade ?? '-'}, ${spv.longTerm?.age ?? '-'})`,
                            color: colorMap[spv.colorClass] || '#dc3545',
                            img: spv.person?.photo ? spv.person.photo : null
                        });
                    });
                });

                return nodes;
            }

            console.log('Main data:', main);
            console.log('Managers data:', managers);
            console.log('Chart nodes:', buildChartData(main, managers));

            async function countTotalImages(data) {
                let count = 0;
                if (data.main.person?.photo) count++;
                data.managers.forEach(manager => {
                    if (manager.person.photo) count++;
                    (manager.supervisors || []).forEach(spv => {
                        if (spv.person.photo) count++;
                    })
                })

                return count;
            }

            // inisiasi loading overflow
            const loadingOverlay = $('#loading-overlay');
            const loadingProgress = $('#loading-progress');
            let totalImages = 0;
            countTotalImages({
                main,
                managers
            }).then(total => {
                totalImages = total;
                loadingProgress.text(`Mengunduh gambar 0/${totalImages}`);
            });
            let loadedImages = 0;

            // Update progress text
            function updateProgress() {
                loadedImages++;
                loadingProgress.text(`Mengunduh gambar ${loadedImages}/${totalImages}`);
            }

            // Tampilkan loading overlay
            loadingOverlay.removeClass('d-none').addClass('d-flex');
            loadingProgress.text(`Mengunduh gambar 0/${totalImages}`);

            async function safeConvertToBase64(url) {
                try {
                    if (!url) {
                        updateProgress();
                        return null;
                    }

                    // Fix protocol-relative URLs
                    if (url.startsWith('//')) url = window.location.protocol + url;
                    if (url.startsWith('/')) url = window.location.origin + url;

                    // Tambahkan cache buster untuk menghindari cached response
                    const cacheBusterUrl = url + (url.includes('?') ? '&' : '?') + 't=' + Date.now();

                    const response = await fetch(cacheBusterUrl, {
                        mode: 'cors',
                        credentials: 'same-origin'
                    });

                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                    const blob = await response.blob();
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onloadend = () => {
                            updateProgress(); // Update progress ketika gambar selesai
                            resolve(reader.result)
                        };
                        reader.onerror = () => {
                            updateProgress(); // Update progress ketika gambar selesai
                            resolve(null);
                        };
                        reader.readAsDataURL(blob);
                    });
                } catch (error) {
                    console.warn('Failed to convert image to base64:', url, error);
                    return null;
                }
            }

            async function prepareChartData(main, managers) {
                // Clone objects to avoid mutation
                const processedMain = {
                    ...main
                };
                const processedManagers = JSON.parse(JSON.stringify(managers));

                // Convert main image
                if (processedMain.person?.photo) {
                    processedMain.person.photo = await safeConvertToBase64(processedMain.person.photo);
                }

                // Process managers
                await Promise.all(processedManagers.map(async (manager) => {
                    if (manager.person?.photo) {
                        manager.person.photo = await safeConvertToBase64(manager.person.photo);
                    }

                    // Process supervisors
                    if (manager.supervisors) {
                        await Promise.all(manager.supervisors.map(async (spv) => {
                            if (spv.person?.photo) {
                                spv.person.photo = await safeConvertToBase64(spv
                                    .person.photo);
                            }
                        }));
                    }
                }));

                return {
                    main: processedMain,
                    managers: processedManagers
                };
            }

            // ✅ 3. Buat chart
            prepareChartData(main, managers).then(({
                main,
                managers
            }) => {
                const nodes = buildChartData(main, managers);

                // Sembunyikan loading dengan animasi
                loadingOverlay.fadeOut(300, function() {
                    $(this).addClass('d-none').removeClass('d-flex');
                });

                const chart = new OrgChart(document.getElementById("orgchart-container"), {
                    template: "myTemplate",
                    mode: "dark",
                    enableSearch: false,
                    enableDragDrop: false,
                    enableZoom: true,
                    enablePan: true,
                    scaleInitial: OrgChart.match.boundary,
                    scaleMin: 0.3,
                    scaleMax: 2,
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
                        field_9: "field_9",
                    },
                    nodes: nodes, // Gunakan nodes yang sudah diproses
                    onInit: function() {
                        // Pastikan tombol tetap ada setelah chart diinisialisasi
                        document.querySelector('.export-buttons').style.display = 'flex';
                    }
                });

                // Export handlers
                function setupExportButtons() {
                    document.getElementById("btn-pdf")?.addEventListener("click", () => {
                        chart.fit();
                        setTimeout(() => {
                            chart.exportPDF({
                                filename: "chart.pdf"
                            });
                        }, 1500);
                    });

                    document.getElementById("btn-png")?.addEventListener("click", () => {
                        chart.fit();
                        setTimeout(() => {
                            chart.exportPNG({
                                filename: "chart.png"
                            });
                        }, 1500); // Tambah delay lebih besar
                    });

                    document.getElementById("btn-svg")?.addEventListener("click", () => {
                        chart.fit();
                        setTimeout(() => {
                            chart.exportSVG({
                                filename: "chart.svg"
                            });
                        }, 1500);
                    });

                    document.getElementById("btn-csv")?.addEventListener("click", () => {
                        chart.fit();
                        setTimeout(() => {
                            chart.exportCSV({
                                filename: "chart.csv"
                            });
                        }, 1500);
                    });
                }

                setupExportButtons();
                chart.fit();
            }).catch(error => {
                console.error('Error initializing chart:', error);
                loadingOverlay.fadeOut(300, function() {
                    $(this).addClass('d-none').removeClass('d-flex');
                });
                // Fallback: Render chart without images
                const nodes = buildChartData(main, managers);
                new OrgChart(document.getElementById("orgchart-container"), {
                    template: "myTemplate",
                    mode: "dark",
                    enableSearch: false,
                    enableDragDrop: false,
                    enableZoom: true,
                    enablePan: true,
                    scaleInitial: OrgChart.match.boundary,
                    scaleMin: 0.3,
                    scaleMax: 2,
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
                        field_9: "field_9",
                    },
                    nodes: nodes
                });
            });
        });
    </script>
@endpush
