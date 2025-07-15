@extends('layouts.root.blank')

@section('title', $title ?? 'RTC')
@section('breadcrumbs', $title ?? 'RTC')

@section('main')
    <div id = "orgchart-container" style = "width:100%; height: 700px;"></div>
    {{-- Styles tetap di sini seperti sebelumnya --}}
    @include('website.rtc.style.index')
@endsection

@push('scripts')
    <script src="https://balkan.app/js/OrgChart.js"></script>

    <script>
        const main = @json($main);
        const managers = @json($managers);
    </script>

    <script>
        function buildChartData(main, managers) {
            let nodes = [];
            console.log(main);


            // Main person (GM)
            const rootId = 'root';
            nodes.push({
                id: rootId,
                name: main.person.name,
                title: main.person.grade,
                grade: `Age: ${main.person.age ?? '-'} | LOS: ${main.person.los ?? '-'}`,
            });

            // Managers
            managers.forEach((manager, i) => {
                const managerId = `m-${i}`;
                nodes.push({
                    id: managerId,
                    pid: rootId,
                    name: manager.person.name,
                    title: manager.person.grade,
                    grade: `Age: ${manager.person.age ?? '-'} | LOS: ${manager.person.los ?? '-'}`,
                });

                // Supervisors
                (manager.supervisors ?? []).forEach((spv, j) => {
                    const spvId = `m-${i}-s-${j}`;
                    nodes.push({
                        id: spvId,
                        pid: managerId,
                        name: spv.person.name,
                        title: spv.person.grade,
                        grade: `Age: ${spv.person.age ?? '-'} | LOS: ${spv.person.los ?? '-'}`,
                    });
                });
            });

            return nodes;
        }

        const chart = new OrgChart(document.getElementById("orgchart-container"), {
            template: "ana",
            nodeBinding: {
                field_0: "name",
                field_1: "title",
                field_2: "grade",
            },
            nodes: buildChartData(main, managers)
        });
    </script>
@endpush
