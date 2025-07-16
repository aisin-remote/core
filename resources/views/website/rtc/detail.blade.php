@extends('layouts.root.view-rtc')

@section('title', $title ?? 'RTC')
@section('breadcrumbs', $title ?? 'RTC')

@section('main')
    <div class="fixed top-4 right-4 z-50 flex gap-2">
        <button id="btn-pdf" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">PDF</button>
        <button id="btn-png" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">PNG</button>
        <button id="btn-svg" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">SVG</button>
        <button id="btn-csv" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">CSV</button>
    </div>
    <div id="orgchart-container" style="width: 100%; height: 100vh;"></div>
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
            overflow: hidden;
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
                    img: main.person?.photo ? `/storage/${main.person.photo}` : null
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
                        img: manager.person?.photo ? `/storage/${manager.person.photo}` : null
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
                            img: spv.person?.photo ? `/storage/${spv.person.photo}` : null
                        });
                    });
                });

                return nodes;
            }

            console.log('Main data:', main);
            console.log('Managers data:', managers);
            console.log('Chart nodes:', buildChartData(main, managers));
            console.log(buildChartData(main, managers)[0])

            // ✅ 3. Buat chart
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
                nodes: buildChartData(main, managers),
            });

            // Tombol export manual
            document.getElementById("btn-pdf").addEventListener("click", function() {
                chart.exportPDF({
                    filename: "chart.pdf"
                });
            });
            document.getElementById("btn-png").addEventListener("click", function() {
                chart.exportPNG({
                    filename: "chart.png"
                });
            });
            document.getElementById("btn-svg").addEventListener("click", function() {
                chart.exportSVG({
                    filename: "chart.svg"
                });
            });
            document.getElementById("btn-csv").addEventListener("click", function() {
                chart.exportCSV({
                    filename: "chart.csv"
                });
            });
            chart.fit();
        });
    </script>
@endpush
