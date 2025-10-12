@extends('layouts.root.view-rtc')

@section('title', $title ?? 'RTC')
@section('breadcrumbs', $title ?? 'RTC')

@push('custom-css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --panel: #fff;
            --card: #fff;
            --line: #cbd5e1;
            --text: #1e293b;
            --text-light: #0f172a;
            --muted: #64748b;
            --chip: #f1f5f9;
            --shadow: 0 4px 16px rgba(0, 0, 0, .08);
            --shadow-heavy: 0 8px 32px rgba(0, 0, 0, .12);
            --border: #e2e8f0;
            --border-light: #cbd5e1;
            --line-thickness: 2;
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
            margin: 0
        }

        .rtc-toolbar {
            display: flex;
            gap: .5rem;
            align-items: center;
            flex-wrap: wrap;
            margin: 0 0 .75rem
        }

        .rtc-btn {
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: .5rem;
            padding: .45rem .7rem;
            cursor: pointer;
            box-shadow: var(--shadow);
            font-size: 12px;
            font-weight: 500;
            transition: .2s
        }

        .rtc-btn:hover {
            background: #f1f5f9;
            transform: translateY(-1px)
        }

        .rtc-input {
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            border-radius: .5rem;
            padding: .45rem .6rem;
            min-width: 260px;
            box-shadow: var(--shadow);
            font-size: 13px
        }

        .rtc-wrap {
            height: calc(100vh - 160px);
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--panel);
            overflow: auto;
            position: relative
        }

        .rtc-canvas {
            position: relative;
            transform-origin: 0 0;
            padding: 40px;
            width: fit-content;
            min-width: 100%;
            transition: transform .3s ease;
            backface-visibility: hidden;
            will-change: transform;
        }

        /* ===== STRUKTUR POHON (hanya untuk layouting; konektor digambar oleh SVG) ===== */
        .tree {
            padding-left: 0;
            margin: 0;
            list-style: none;
            position: relative
        }

        .tree ul {
            padding-left: 0;
            margin: 0;
            position: relative;
            padding-top: 50px;
            /* jarak vertikal parent->bar */
            margin-top: 30px;
            /* jarak bar->anak */
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 60px;
            list-style: none
        }

        .tree li {
            list-style: none;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center
        }

        /* ===== KARTU ===== */
        .node {
            width: 320px;
            text-align: left;
            background: var(--card);
            border: 2px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 20px;
            position: relative;
            z-index: 10;
            transition: .3s;
            overflow: hidden;
            /* supaya strip warna ikut rounded */
        }

        /* strip warna di atas kartu */
        .node::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            height: 6px;
            z-index: 1;
            background: linear-gradient(135deg, var(--node-color, #0ea5e9), var(--node-color-dark, #0284c7));
        }

        .node[data-level="executive"] {
            border: 3px solid;
            border-image: linear-gradient(135deg, var(--node-color, #0ea5e9), var(--node-color-dark, #0284c7)) 1;
            box-shadow: var(--shadow-heavy), 0 0 0 1px var(--node-color, #0ea5e9);
            width: 360px
        }

        .node[data-level="manager"] {
            border: 3px solid;
            border-image: linear-gradient(135deg, var(--node-color, #22c55e), var(--node-color-dark, #16a34a)) 1;
            box-shadow: var(--shadow), 0 0 0 1px var(--node-color, #22c55e);
            width: 340px
        }

        .node[data-level="supervisor"] {
            border: 3px solid;
            border-image: linear-gradient(135deg, var(--node-color, #ef4444), var(--node-color-dark, #dc2626)) 1;
            box-shadow: var(--shadow), 0 0 0 1px var(--node-color, #ef4444);
            width: 320px
        }

        .node[data-level="staff"] {
            border: 3px solid;
            border-image: linear-gradient(135deg, var(--node-color, #f59e0b), var(--node-color-dark, #d97706)) 1;
            box-shadow: var(--shadow), 0 0 0 1px var(--node-color, #f59e0b);
            width: 300px
        }

        .node:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, .15)
        }

        .node-header {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 16px
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border);
            flex: 0 0 auto;
            background: #f1f5f9;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1)
        }

        .node h4 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: var(--text-light);
            line-height: 1.2
        }

        .role {
            font-size: 14px;
            color: var(--muted);
            white-space: pre-line;
            margin-top: 6px;
            font-weight: 500
        }

        .badge {
            display: inline-block;
            background: var(--chip);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 11px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 999px;
            margin-right: 8px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .5px
        }

        .grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px 16px;
            margin: 16px 0;
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border)
        }

        .label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px
        }

        .value {
            font-size: 13px;
            color: var(--text-light);
            font-weight: 500;
            text-align: right
        }

        .multiline {
            white-space: pre-line
        }

        .toggle {
            margin-top: 16px;
            text-align: center
        }

        .toggle button {
            background: var(--chip);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 12px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: .2s;
            text-transform: uppercase;
            letter-spacing: .5px
        }

        .toggle button:hover {
            background: #e2e8f0;
            transform: translateY(-1px)
        }

        li.collapsed>ul {
            display: none
        }

        li.collapsed .toggle button {
            background: rgba(245, 158, 11, .1);
            border-color: #f59e0b;
            color: #d97706
        }

        .top-pair {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
            justify-content: center;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto
        }

        .top-title {
            font-size: 16px;
            color: var(--text);
            margin: 0 0 20px;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 24px;
            background: var(--chip);
            border-radius: 12px;
            border: 1px solid var(--border);
            backdrop-filter: blur(10px)
        }

        .chip {
            display: inline-block;
            height: 10px;
            width: 10px;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 10px;
            box-shadow: 0 0 8px rgba(0, 0, 0, .3)
        }

        .c-1 {
            background: linear-gradient(135deg, #0ea5e9, #0284c7)
        }

        .c-2 {
            background: linear-gradient(135deg, #22c55e, #16a34a)
        }

        .c-3 {
            background: linear-gradient(135deg, #ef4444, #dc2626)
        }

        .c-4 {
            background: linear-gradient(135deg, #f59e0b, #d97706)
        }

        .c-5 {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed)
        }

        .c-6 {
            background: linear-gradient(135deg, #14b8a6, #0d9488)
        }

        .c-7 {
            background: linear-gradient(135deg, #fb923c, #ea580c)
        }

        .c-8 {
            background: linear-gradient(135deg, #06b6d4, #0891b2)
        }

        .c-9 {
            background: linear-gradient(135deg, #a855f7, #9333ea)
        }

        .c-10 {
            background: linear-gradient(135deg, #10b981, #059669)
        }

        .c-11 {
            background: linear-gradient(135deg, #eab308, #ca8a04)
        }

        .c-12 {
            background: linear-gradient(135deg, #ec4899, #db2777)
        }

        .c-13 {
            background: linear-gradient(135deg, #34d399, #10b981)
        }

        .c-14 {
            background: linear-gradient(135deg, #3b82f6, #2563eb)
        }

        .node.highlight {
            outline: 4px solid #f59e0b;
            outline-offset: 4px;
            box-shadow: 0 0 0 8px rgba(245, 158, 11, .2), var(--shadow-heavy);
            animation: pulse-highlight 2s infinite
        }

        @keyframes pulse-highlight {

            0%,
            100% {
                outline-color: #f59e0b
            }

            50% {
                outline-color: #fbbf24
            }
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            background: var(--panel);
            border-radius: 12px;
            border: 1px solid var(--border)
        }

        .card-header {
            background: var(--panel);
            border-bottom: 1px solid var(--border);
            border-radius: 12px 12px 0 0;
            padding: 20px
        }

        .card-body {
            background: var(--panel);
            border-radius: 0 0 12px 12px;
            padding: 20px
        }

        #info {
            color: #9ca3af;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 12px;
            background: rgba(156, 163, 175, .1);
            border-radius: 6px;
            border: 1px solid rgba(156, 163, 175, .2)
        }

        @media (max-width:1200px) {
            .tree ul {
                gap: 40px
            }

            .node {
                width: 300px
            }

            .node[data-level="executive"] {
                width: 320px
            }

            .node[data-level="manager"] {
                width: 310px
            }
        }

        @media (max-width:768px) {

            .node,.node[data-level="executive"],
            .node[data-level="manager"],
            .node[data-level="supervisor"],
            .node[data-level="staff"] {
                width: 280px
            }

            .tree ul {
                gap: 30px
            }

            .rtc-canvas {
                padding: 20px
            }

            .top-pair {
                grid-template-columns: 1fr;
                gap: 20px
            }
        }

        @media (max-width:480px) {

            .node,.node[data-level="executive"],
            .node[data-level="manager"],
            .node[data-level="supervisor"],
            .node[data-level="staff"] {
                width: 260px
            }

            .rtc-toolbar {
                flex-direction: column;
                align-items: stretch
            }

            .rtc-input {
                min-width: auto
            }
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
            font-size: 14px;
            color: var(--muted)
        }

        .loading::after {
            content: '';
            width: 20px;
            height: 20px;
            border: 2px solid var(--border);
            border-top: 2px solid var(--text);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px
        }

        @keyframes spin {
            0% {
                transform: rotate(0)
            }

            100% {
                transform: rotate(360deg)
            }
        }

        /* Export */
        .export-mode .rtc-canvas {
            transform: scale(1) !important
        }

        .export-mode .node.highlight {
            outline: none !important;
            animation: none !important;
            box-shadow: var(--shadow) !important
        }

        /* ===== SVG konektor ===== */
        .rtc-links {
            position: absolute;
            left: 0;
            top: 0;
            pointer-events: none;
            z-index: 0;
        }

        .rtc-links path {
            stroke: var(--line);
            stroke-width: var(--line-thickness);
            fill: none;
            shape-rendering: crispEdges;
            vector-effect: non-scaling-stroke;
            /* stroke tidak ikut scaling saat zoom */
            stroke-linecap: square;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-3">
        <div class="card shadow-sm" style="background:var(--panel);border:1px solid #1f2433">
            <div class="card-header" style="background:var(--panel);border-bottom:1px solid #1f2433">
                <div class="rtc-toolbar">
                    <button id="zoomOut" class="rtc-btn" title="Zoom Out">-</button>
                    <button id="zoomReset" class="rtc-btn" title="Reset Zoom">100%</button>
                    <button id="zoomIn" class="rtc-btn" title="Zoom In">+</button>
                    <button id="fitToScreen" class="rtc-btn" title="Fit to Screen">Fit</button>

                    <div style="border-left:1px solid var(--border);height:24px;margin:0 8px;"></div>

                    <input id="orgSearch" class="rtc-input" type="search"
                        placeholder="Cari nama, jabatan, atau departemen…">
                    <button id="clearSearch" class="rtc-btn" title="Clear Search" style="display:none;">✕</button>
                    <span id="info" style="color:#9aa8c0;font-size:12px"></span>

                    <span style="flex:1 1 auto"></span>

                    <button id="expandAll" class="rtc-btn" title="Expand All">Expand All</button>
                    <button id="collapseAll" class="rtc-btn" title="Collapse All">Collapse All</button>

                    <div style="border-left:1px solid var(--border);height:24px;margin:0 8px;"></div>

                    <button id="btn-png" class="rtc-btn" title="Export as PNG">PNG</button>
                    <button id="btn-pdf" class="rtc-btn" title="Export as PDF">PDF</button>
                </div>
            </div>
            <div class="card-body" style="background:var(--panel)">
                <div class="rtc-wrap">
                    <div id="canvas" class="rtc-canvas">
                        <!-- SVG overlay untuk konektor -->
                        <svg id="links" class="rtc-links" width="0" height="0"></svg>
                        <div class="loading">Loading organization chart...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /* ====== DATA DARI CONTROLLER ====== */
        const MAIN = @json($main);
        const MANAGERS = @json($managers);
        const HIDE_MAIN = @json($hideMainPlans ?? false);
        const NO_ROOT = @json($noRoot ?? false);
        const GROUP_TOP = @json($groupTop ?? false);

        /* ---------- HELPERS ---------- */
        const colorMap = {
            "color-1": "c-1",
            "color-2": "c-2",
            "color-3": "c-3",
            "color-4": "c-4",
            "color-5": "c-5",
            "color-6": "c-6",
            "color-7": "c-7",
            "color-8": "c-8",
            "color-9": "c-9",
            "color-10": "c-10",
            "color-11": "c-11",
            "color-12": "c-12",
            "color-13": "c-13",
            "color-14": "c-14"
        };
        const hierarchyLevels = {
            "president": "executive",
            "vpd": "executive",
            "vice president": "executive",
            "director": "executive",
            "gm": "executive",
            "general manager": "executive",
            "manager": "manager",
            "head": "manager",
            "supervisor": "supervisor",
            "lead": "supervisor",
            "senior": "supervisor",
            "section head": "supervisor",
            "staff": "staff",
            "officer": "staff",
            "analyst": "staff"
        };
        const clamp = (s, n = 64) => (s ? (String(s).length > n ? String(s).slice(0, n - 1) + '…' : String(s)) : '-');
        const qs = (s) => document.querySelector(s);
        const qsAll = (s) => document.querySelectorAll(s);
        const el = (t, c) => {
            const e = document.createElement(t);
            if (c) e.className = c;
            return e;
        };

        function getDeptName(person) {
            if (!person || !person.department) return '';
            const d = person.department;
            if (typeof d === 'string') return d;
            if (typeof d === 'object') return d.name || '';
            return '';
        }

        function getHierarchyLevelByNode(data) {
            const pos = (data?.person?.position || '').toLowerCase();
            if (pos) {
                for (const [kw, lv] of Object.entries(hierarchyLevels)) {
                    if (pos.includes(kw)) return lv;
                }
            }
            const t = (data?.title || '').toLowerCase();
            for (const [kw, lv] of Object.entries(hierarchyLevels)) {
                if (t.includes(kw)) return lv;
            }
            return 'staff';
        }

        function candidateText(c) {
            if (!c || c.name === '-' || !c.name) return '';
            const g = c.grade ?? '-',
                a = c.age ?? '-';
            return `${c.name} (${g}, ${a})`;
        }

        /* === AVATAR + HEADER NODE === */
        function personRow(p, unitTitle) {
            const head = el('div', 'node-header');
            const img = el('img', 'avatar');
            const fallback =
                `https://ui-avatars.com/api/?name=${encodeURIComponent(p?.name||'N/A')}&background=374151&color=e2e8f0`;
            img.src = (p?.photo || '').trim() || fallback;
            img.alt = (p?.name || 'photo');
            img.crossOrigin = 'anonymous';
            img.referrerPolicy = 'no-referrer';
            img.onerror = () => {
                img.src = fallback;
            };
            head.appendChild(img);
            const tx = el('div');
            const h4 = el('h4');
            h4.textContent = clamp(p?.name, 48);
            const role = el('div', 'role multiline');
            role.textContent = unitTitle || '';
            tx.appendChild(h4);
            tx.appendChild(role);
            head.appendChild(tx);
            return head;
        }

        function chips(title, colorClass, departmentName) {
            const wrap = el('div');
            if (departmentName) {
                const b = el('span', 'badge');
                b.textContent = departmentName;
                wrap.appendChild(b);
            }
            const chip = el('span', 'chip ' + (colorMap[colorClass] || 'c-1'));
            const t = el('span');
            t.textContent = clamp(title, 64);
            t.style.cssText = 'font-size:13px;color:#0f172a;font-weight:600';
            wrap.appendChild(chip);
            wrap.appendChild(t);
            return wrap;
        }

        function detailGrid(person, plans, hidePlans) {
            const box = el('div');
            const g = el('div', 'grid');
            const add = (k, v, hi = false) => {
                const a = el('div', 'label');
                a.textContent = k;
                const b = el('div', 'value');
                b.textContent = (v ?? '-');
                if (hi && v !== undefined && v !== null && v !== '') {
                    b.style.color = '#22c55e';
                    b.style.fontWeight = '600';
                }
                g.appendChild(a);
                g.appendChild(b);
            };
            add('Grade', person?.grade ?? '-', true);
            add('Age', person?.age ?? '-');
            add('LOS', person?.los ?? '-');
            add('LCP', person?.lcp ?? '-');
            box.appendChild(g);
            if (!hidePlans) {
                const ttl = el('div', 'label');
                ttl.textContent = 'SUCCESSION PLANS';
                ttl.style.cssText = 'font-size:11px;margin:16px 0 12px;text-align:center;color:#64748b';
                box.appendChild(ttl);
                const g2 = el('div', 'grid');
                const add2 = (k, v) => {
                    const a = el('div', 'label');
                    a.textContent = k;
                    const b = el('div', 'value multiline');
                    b.textContent = v || '';
                    if (!v) {
                        b.style.color = '#ef4444';
                        b.style.fontStyle = 'italic';
                    }
                    g2.appendChild(a);
                    g2.appendChild(b);
                };
                add2('S/T', plans?.st || '');
                add2('M/T', plans?.mt || '');
                add2('L/T', plans?.lt || '');
                box.appendChild(g2);
            }
            return box;
        }

        function getNodeColor(cc) {
            const m = {
                "color-1": {
                    primary: "#0ea5e9",
                    dark: "#0284c7"
                },
                "color-2": {
                    primary: "#22c55e",
                    dark: "#16a34a"
                },
                "color-3": {
                    primary: "#ef4444",
                    dark: "#dc2626"
                },
                "color-4": {
                    primary: "#f59e0b",
                    dark: "#d97706"
                },
                "color-5": {
                    primary: "#8b5cf6",
                    dark: "#7c3aed"
                },
                "color-6": {
                    primary: "#14b8a6",
                    dark: "#0d9488"
                },
                "color-7": {
                    primary: "#fb923c",
                    dark: "#ea580c"
                },
                "color-8": {
                    primary: "#06b6d4",
                    dark: "#0891b2"
                },
                "color-9": {
                    primary: "#a855f7",
                    dark: "#9333ea"
                },
                "color-10": {
                    primary: "#10b981",
                    dark: "#059669"
                },
                "color-11": {
                    primary: "#eab308",
                    dark: "#ca8a04"
                },
                "color-12": {
                    primary: "#ec4899",
                    dark: "#db2777"
                },
                "color-13": {
                    primary: "#34d399",
                    dark: "#10b981"
                },
                "color-14": {
                    primary: "#3b82f6",
                    dark: "#2563eb"
                }
            };
            return m[cc] || m['color-1'];
        }

        function makeCard(data, {
            hidePlans = false
        } = {}) {
            const card = el('div', 'node');
            const level = getHierarchyLevelByNode(data);
            card.setAttribute('data-level', level);

            const nc = getNodeColor(data.colorClass);
            card.style.setProperty('--node-color', nc.primary);
            card.style.setProperty('--node-color-dark', nc.dark);

            const deptName = getDeptName(data.person);
            card.appendChild(chips(data.title, data.colorClass, deptName));
            card.appendChild(personRow(data.person, data.title));

            const plans = {
                st: candidateText(data.shortTerm),
                mt: candidateText(data.midTerm),
                lt: candidateText(data.longTerm)
            };
            card.appendChild(detailGrid(data.person, plans, hidePlans || data.no_plans));

            if (data.supervisors && data.supervisors.length) {
                const t = el('div', 'toggle');
                const b = el('button');
                b.textContent = `Collapse (${data.supervisors.length})`;
                b.onclick = (ev) => {
                    const li = ev.currentTarget.closest('li');
                    li.classList.toggle('collapsed');
                    const c = li.classList.contains('collapsed');
                    b.textContent = c ? `Expand (${data.supervisors.length})` : `Collapse (${data.supervisors.length})`;
                    queueDraw();
                };
                t.appendChild(b);
                card.appendChild(t);
            }

            const searchText = [data.person?.name, data.person?.position, data.title, deptName, data.person?.grade, level]
                .filter(Boolean).join(' ').toLowerCase();
            registry.push({
                card,
                data,
                level,
                searchText
            });
            return card;
        }

        /* ===== Render ===== */
        function renderNode(node, depth = 0) {
            const li = el('li');
            li.setAttribute('data-depth', depth);
            li.appendChild(makeCard(node, {
                hidePlans: false
            }));

            if (node.supervisors && node.supervisors.length) {
                const ul = el('ul');
                const groups = {};
                node.supervisors.forEach(ch => {
                    const g = getDeptName(ch.person) || 'General';
                    (groups[g] ||= []).push(ch);
                });

                if (Object.keys(groups).length > 1) {
                    Object.entries(groups).forEach(([dept, children]) => {
                        const container = el('div', 'department-group');
                        container.setAttribute('data-department', dept);
                        const inner = el('ul');
                        children.forEach(c => inner.appendChild(renderNode(c, depth + 1)));
                        container.appendChild(inner);
                        ul.appendChild(container);
                    });
                } else {
                    node.supervisors.forEach(c => ul.appendChild(renderNode(c, depth + 1)));
                }
                li.appendChild(ul);
            }
            return li;
        }

        function renderRoots(roots) {
            const tree = el('ul', 'tree');
            roots.forEach(r => tree.appendChild(renderNode(r, 0)));
            return tree;
        }

        function buildRoots() {
            const container = el('div');
            if (GROUP_TOP) {
                const wrap = el('div');
                const title = el('div', 'top-title');
                title.innerHTML = '🏢 Top Management';
                wrap.appendChild(title);
                const pair = el('div', 'top-pair');
                (MANAGERS.slice(0, 2)).forEach(m => {
                    const card = makeCard({
                        ...m,
                        no_plans: true
                    }, {
                        hidePlans: true
                    });
                    pair.appendChild(card);
                });
                wrap.appendChild(pair);
                const roots = (MANAGERS[0]?.supervisors || []);
                if (roots.length) {
                    const ttl = el('div', 'top-title');
                    ttl.innerHTML = '👥 Management Team';
                    ttl.style.marginTop = '40px';
                    wrap.appendChild(ttl);
                    wrap.appendChild(renderRoots(roots));
                }
                return wrap;
            }
            if (!NO_ROOT) {
                const ul = el('ul', 'tree');
                const li = el('li');
                li.appendChild(makeCard({
                    ...MAIN,
                    no_plans: HIDE_MAIN
                }, {
                    hidePlans: HIDE_MAIN
                }));
                if (MANAGERS && MANAGERS.length) {
                    const kids = el('ul');
                    MANAGERS.forEach(m => kids.appendChild(renderNode(m, 1)));
                    li.appendChild(kids);
                }
                ul.appendChild(li);
                container.appendChild(ul);
            } else {
                if (MANAGERS.length > 1) {
                    const ttl = el('div', 'top-title');
                    ttl.innerHTML = '🌟 Leadership Team';
                    container.appendChild(ttl);
                }
                container.appendChild(renderRoots(MANAGERS));
            }
            return container;
        }

        /* ====== MATIKAN KONEKTOR CSS LAMA ====== */
        function disableCssConnectors() {
            if (document.getElementById('kill-css-lines')) return;
            const style = document.createElement('style');
            style.id = 'kill-css-lines';
            style.textContent = `
    .tree ul::before, .tree ul::after, .tree li::before, .department-group::before { content:none !important; border:0 !important; }
  `;
            document.head.appendChild(style);
        }

        /* ===== SVG CONNECTORS ===== */
        let drawTimer = null;

        function queueDraw() {
            clearTimeout(drawTimer);
            drawTimer = setTimeout(drawLinks, 32);
        }

        function drawLinks() {
            const canvas = document.getElementById('canvas');
            if (!canvas) return;

            // pastikan relative positioning supaya SVG absolute nempel ke canvas
            if (getComputedStyle(canvas).position === 'static') {
                canvas.style.position = 'relative';
            }

            let svg = document.getElementById('links');
            if (!svg) {
                svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.id = 'links';
                svg.style.position = 'absolute';
                svg.style.left = 0;
                svg.style.top = 0;
                svg.style.pointerEvents = 'none';
                svg.style.zIndex = 0;
                canvas.prepend(svg);
            }

            // gunakan ukuran KONTEN sebenarnya agar tidak offset (padding/zoom/scroll aman)
            const W = canvas.scrollWidth;
            const H = canvas.scrollHeight;
            svg.setAttribute('width', W);
            svg.setAttribute('height', H);
            svg.setAttribute('viewBox', `0 0 ${W} ${H}`);

            while (svg.firstChild) svg.removeChild(svg.firstChild);

            const dpr = window.devicePixelRatio || 1;
            const snap = v => Math.round(v * dpr) / dpr;

            const rootStyle = getComputedStyle(document.documentElement);
            const LINE_COLOR = rootStyle.getPropertyValue('--line')?.trim() || '#cbd5e1';
            const THICK = parseFloat(rootStyle.getPropertyValue('--line-thickness')) || 2;

            // offset canvas (padding box) utk konversi absolut -> lokal
            const cRect = canvas.getBoundingClientRect();

            canvas.querySelectorAll('.tree li').forEach(li => {
                const parentNode = li.querySelector(':scope > .node');
                const ul = li.querySelector(':scope > ul, :scope > .department-group > ul');
                if (!parentNode || !ul || ul.offsetParent === null) return;

                const parentRect = parentNode.getBoundingClientRect();
                const childNodes = [
                    ...ul.querySelectorAll(':scope > li > .node'),
                    ...ul.querySelectorAll(':scope > .department-group > ul > li > .node')
                ].filter(n => n.offsetParent !== null);
                if (childNodes.length === 0) return;

                const px = snap(parentRect.left + parentRect.width / 2 - cRect.left + canvas.scrollLeft);
                const py = snap(parentRect.bottom - cRect.top + canvas.scrollTop);

                const childRects = childNodes.map(n => n.getBoundingClientRect());
                const childCenters = childRects.map(r => snap(r.left + r.width / 2 - cRect.left + canvas
                    .scrollLeft));
                const firstX = Math.min(...childCenters);
                const lastX = Math.max(...childCenters);

                const minChildTop = Math.min(...childRects.map(r => r.top - cRect.top + canvas.scrollTop));
                // bar di antara parent & anak: minimal 14px di bawah parent, 24px di atas anak
                let barY = snap(Math.min(minChildTop - 24, Math.max(py + 14, py + (minChildTop - py) * 0.35)));

                addPath(svg, `M ${px} ${py} L ${px} ${barY}`, LINE_COLOR, THICK); // parent vertical
                addPath(svg, `M ${firstX} ${barY} L ${lastX} ${barY}`, LINE_COLOR, THICK); // horizontal bar

                childRects.forEach((r, i) => {
                    const cx = childCenters[i];
                    const cy = snap(r.top - cRect.top + canvas.scrollTop);
                    addPath(svg, `M ${cx} ${barY} L ${cx} ${cy}`, LINE_COLOR, THICK); // child verticals
                });
            });
        }

        function addPath(svg, d, color, thick) {
            const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            p.setAttribute('d', d);
            p.setAttribute('stroke', color);
            p.setAttribute('stroke-width', thick);
            p.setAttribute('fill', 'none');
            p.setAttribute('shape-rendering', 'crispEdges');
            p.setAttribute('vector-effect', 'non-scaling-stroke');
            p.setAttribute('stroke-linecap', 'square');
            svg.appendChild(p);
        }

        /* ===== Zoom, Fit, Search ===== */
        const registry = [];
        let zoom = 1,
            canvasEl, searchInput, clearBtn, info, searchTimer = null;

        function applyZoom() {
            if (canvasEl) {
                canvasEl.style.transform = `scale(${zoom})`;
                const r = qs('#zoomReset');
                if (r) r.textContent = `${Math.round(zoom*100)}%`;
                queueDraw();
            }
        }

        function fitToScreen() {
            const wrapper = qs('.rtc-wrap'),
                content = qs('#canvas');
            if (!wrapper || !content) return;
            const prev = content.style.transform;
            content.style.transform = 'scale(1)';
            content.offsetHeight;
            queueDraw();
            setTimeout(() => {
                const wr = wrapper.getBoundingClientRect(),
                    cr = content.getBoundingClientRect();
                const scaleX = (wr.width - 40) / cr.width,
                    scaleY = (wr.height - 40) / cr.height;
                zoom = Math.min(scaleX, scaleY, 1);
                zoom = Math.max(0.1, zoom);
                applyZoom();
            }, 50);
        }

        function performSearch(q) {
            const query = (q || '').trim().toLowerCase();
            let hit = 0;
            registry.forEach(({
                card,
                searchText
            }) => {
                card.classList.remove('highlight');
                if (query && searchText.includes(query)) {
                    card.classList.add('highlight');
                    let li = card.closest('li');
                    while (li) {
                        li.classList.remove('collapsed');
                        const btn = li.querySelector('.toggle button');
                        if (btn && !li.classList.contains('collapsed')) btn.textContent = btn.textContent.replace(
                            'Expand', 'Collapse');
                        li = li.parentElement?.closest('li');
                    }
                    hit++;
                }
            });
            if (info) {
                if (query) {
                    if (clearBtn) clearBtn.style.display = 'block';
                    info.textContent = hit ? `${hit} hasil ditemukan` : 'Tidak ada hasil';
                    info.style.color = hit ? '#22c55e' : '#ef4444';
                } else {
                    if (clearBtn) clearBtn.style.display = 'none';
                    info.textContent = '';
                }
            }
            queueDraw();
        }

        function expandAll() {
            qsAll('li.collapsed').forEach(li => {
                li.classList.remove('collapsed');
                const b = li.querySelector('.toggle button');
                if (b) b.textContent = b.textContent.replace('Expand', 'Collapse');
            });
            queueDraw();
        }

        function collapseAll() {
            qsAll('li').forEach(li => {
                if (li.querySelector('ul')) {
                    li.classList.add('collapsed');
                    const b = li.querySelector('.toggle button');
                    if (b) b.textContent = b.textContent.replace('Collapse', 'Expand');
                }
            });
            queueDraw();
        }

        /* ===== Init ===== */
        function initializeChart() {
            disableCssConnectors();

            canvasEl = qs('#canvas');
            if (!canvasEl) return;
            if (getComputedStyle(canvasEl).position === 'static') {
                canvasEl.style.position = 'relative';
            }

            // siapkan SVG overlay
            let svg = qs('#links');
            if (!svg) {
                svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.id = 'links';
                svg.style.position = 'absolute';
                svg.style.left = 0;
                svg.style.top = 0;
                svg.style.pointerEvents = 'none';
                svg.style.zIndex = 0;
                canvasEl.prepend(svg);
            } else {
                canvasEl.insertBefore(svg, canvasEl.firstChild);
            }

            // build struktur
            canvasEl.querySelectorAll(':scope > :not(#links)').forEach(n => n.remove());
            const structure = buildRoots();
            canvasEl.appendChild(structure);

            // redraw saat avatar load
            qsAll('.avatar').forEach(img => {
                if (!img.complete) {
                    img.addEventListener('load', queueDraw, {
                        once: true
                    });
                }
            });

            // observer: setiap perubahan attr/class/layout
            const mo = new MutationObserver(() => queueDraw());
            mo.observe(canvasEl, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class']
            });

            requestAnimationFrame(() => {
                queueDraw();
                setTimeout(fitToScreen, 100);
            });

            const infoEl = qs('#info');
            if (infoEl) {
                infoEl.textContent = `${registry.length} posisi dimuat`;
                infoEl.style.color = '#22c55e';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializeChart();

            const zoomInBtn = qs('#zoomIn'),
                zoomOutBtn = qs('#zoomOut'),
                zoomResetBtn = qs('#zoomReset'),
                fitBtn = qs('#fitToScreen');
            if (zoomInBtn) zoomInBtn.onclick = () => {
                zoom = Math.min(2.0, +(zoom + 0.1).toFixed(2));
                applyZoom();
            };
            if (zoomOutBtn) zoomOutBtn.onclick = () => {
                zoom = Math.max(0.1, +(zoom - 0.1).toFixed(2));
                applyZoom();
            };
            if (zoomResetBtn) zoomResetBtn.onclick = () => {
                zoom = 1;
                applyZoom();
            };
            if (fitBtn) fitBtn.onclick = fitToScreen;

            const searchInput = qs('#orgSearch');
            clearBtn = qs('#clearSearch');
            info = qs('#info');
            if (searchInput) {
                searchInput.addEventListener('input', e => {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => performSearch(e.target.value), 300);
                });
            }
            if (clearBtn) {
                clearBtn.onclick = () => {
                    if (searchInput) {
                        searchInput.value = '';
                        performSearch('');
                        searchInput.focus();
                    }
                };
            }

            const expandBtn = qs('#expandAll'),
                collapseBtn = qs('#collapseAll');
            if (expandBtn) expandBtn.onclick = expandAll;
            if (collapseBtn) collapseBtn.onclick = collapseAll;

            let resizeTimer = null;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    queueDraw();
                    fitToScreen();
                }, 180);
            });

            // redraw ketika container di-scroll (mis. kamu punya sticky header/observer)
            const wrap = qs('.rtc-wrap');
            if (wrap) {
                wrap.addEventListener('scroll', () => queueDraw(), {
                    passive: true
                });
            }
        });
    </script>

    <!-- Export Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pngBtn = qs('#btn-png'),
                pdfBtn = qs('#btn-pdf'),
                canvas = qs('#canvas');

            if (pngBtn) {
                pngBtn.onclick = async () => {
                    const btn = pngBtn,
                        txt = btn.textContent;
                    btn.textContent = 'Exporting...';
                    btn.disabled = true;
                    try {
                        const body = document.body;
                        const hi = qsAll('.highlight');
                        const originalZoom = zoom;
                        body.classList.add('export-mode');
                        hi.forEach(n => n.classList.remove('highlight'));
                        zoom = 1;
                        canvas.style.transform = 'scale(1)';
                        canvas.offsetHeight;
                        queueDraw();
                        await new Promise(r => setTimeout(r, 200));

                        const node = qs('.rtc-canvas');
                        if (!node) throw new Error('Canvas not found');
                        const canvasEl = await html2canvas(node, {
                            backgroundColor: '#ffffff',
                            scale: 2,
                            useCORS: true,
                            allowTaint: true,
                            scrollX: 0,
                            scrollY: 0
                        });

                        body.classList.remove('export-mode');
                        hi.forEach(n => n.classList.add('highlight'));
                        zoom = originalZoom;
                        canvas.style.transform = `scale(${zoom})`;
                        queueDraw();

                        const a = document.createElement('a');
                        a.download = `orgchart-${new Date().toISOString().slice(0,10)}.png`;
                        a.href = canvasEl.toDataURL('image/png', 1.0);
                        a.click();
                    } catch (e) {
                        console.error(e);
                        alert('Export failed. Please try again.');
                    } finally {
                        btn.textContent = txt;
                        btn.disabled = false;
                    }
                };
            }

            if (pdfBtn) {
                pdfBtn.onclick = async () => {
                    const btn = pdfBtn,
                        txt = btn.textContent;
                    btn.textContent = 'Exporting...';
                    btn.disabled = true;
                    try {
                        const body = document.body;
                        const hi = qsAll('.highlight');
                        const originalZoom = zoom;
                        body.classList.add('export-mode');
                        hi.forEach(n => n.classList.remove('highlight'));
                        zoom = 1;
                        canvas.style.transform = 'scale(1)';
                        canvas.offsetHeight;
                        queueDraw();
                        await new Promise(r => setTimeout(r, 200));

                        const node = qs('.rtc-canvas');
                        if (!node) throw new Error('Canvas not found');
                        const canvasEl = await html2canvas(node, {
                            backgroundColor: '#ffffff',
                            scale: 2,
                            useCORS: true,
                            allowTaint: true,
                            scrollX: 0,
                            scrollY: 0
                        });

                        body.classList.remove('export-mode');
                        hi.forEach(n => n.classList.add('highlight'));
                        zoom = originalZoom;
                        canvas.style.transform = `scale(${zoom})`;
                        queueDraw();

                        const imgData = canvasEl.toDataURL('image/jpeg', 0.95);
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: canvasEl.width > canvasEl.height ? 'landscape' :
                                'portrait',
                            unit: 'pt',
                            format: 'a4'
                        });
                        const PW = pdf.internal.pageSize.getWidth(),
                            PH = pdf.internal.pageSize.getHeight();
                        const margin = 40,
                            AW = PW - margin * 2,
                            AH = PH - margin * 2;
                        const ratio = Math.min(AW / canvasEl.width, AH / canvasEl.height);
                        const w = canvasEl.width * ratio,
                            h = canvasEl.height * ratio,
                            x = (PW - w) / 2,
                            y = (PH - h) / 2;
                        pdf.addImage(imgData, 'JPEG', x, y, w, h);
                        pdf.save(`orgchart-${new Date().toISOString().slice(0,10)}.pdf`);
                    } catch (e) {
                        console.error(e);
                        alert('PDF export failed. Please try again.');
                    } finally {
                        btn.textContent = txt;
                        btn.disabled = false;
                    }
                };
            }
        });
    </script>
@endpush
