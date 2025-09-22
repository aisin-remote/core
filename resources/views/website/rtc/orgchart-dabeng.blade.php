@extends('layouts.root.view-rtc')

@section('title', $title ?? 'Org Chart')
@section('breadcrumbs', $title ?? 'Org Chart')

@push('custom-css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@3.8.0/dist/css/jquery.orgchart.min.css">
    <style>
        html,
        body {
            height: 100%;
            background: #101114;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Arial, sans-serif;
        }

        #chart {
            height: calc(100vh - 16px);
            overflow: auto;
            background: #101114;
        }

        .orgchart {
            background: #101114;
        }

        .orgchart .node {
            border-radius: 12px;
        }

        .orgchart .title {
            background: #0ea5e9;
            font-weight: 700;
            font-size: 14px;
        }

        .orgchart .content {
            white-space: pre-line;
            font-size: 12px;
            line-height: 1.25rem;
        }

        .oc-card {
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }

        .oc-card img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 2px;
        }

        .oc-main {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .oc-main .name {
            font-weight: 700;
            color: #111827;
        }

        .oc-main .dept {
            color: #6b7280;
            font-size: 12px;
        }

        /* warna top node ketika groupTop=true */
        .orgchart .node .title.top {
            background: #334155;
            color: #e5e7eb;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-2">
        <div id="chart"></div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/orgchart@3.8.0/dist/js/jquery.orgchart.min.js"></script>
    <script>
        $(async function() {
            // ambil parameter filter & id dari query (re-use milik summary)
            const params = new URLSearchParams(location.search);
            params.set('as_json', '1'); // minta JSON
            const url = `{{ route('rtc.structure.json') }}?${params.toString()}`;

            const nodes = await $.getJSON(url);

            // custom template per node (gambar + name + content multiline)
            function createNode($node, data) {
                const $cont = $node.find('.content');
                const img = data.image ? `<img src="${data.image}" alt="">` : '';
                // pindah "name" ke bar di atas, dan "title" jadi label kecil abu-abu
                const titleText = data.title || '';
                const nameText = data.name || '';
                const desc = data.content || '';

                // bar title default plugin pakai name → kita tukar:
                $node.find('.title').text(titleText);

                $cont.html(`
      <div class="oc-card">
        ${img}
        <div class="oc-main">
          <div class="name">${nameText}</div>
          <div class="dept">${titleText}</div>
          <div class="desc">${desc.replace(/\n/g,'<br>')}</div>
        </div>
      </div>
    `);
            }

            // render
            $('#chart').orgchart({
                data: nodes, // flat [{id,pid,name,title,content,image}]
                nodeTitle: 'name', // akan kita override di createNode
                nodeContent: 'content', // tetap perlu supaya .content tersedia
                pan: true,
                zoom: true,
                verticalLevel: 99,
                // kalau graph sangat besar, bisa set 'depth' untuk batasi
                createNode: createNode
            });

            // tweak style root top (jika node 1 adalah top management)
            if (nodes.length && nodes[0].name === 'President & VPD') {
                $('.orgchart .node:first .title').addClass('top');
            }
        });
    </script>
@endpush
