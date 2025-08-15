{{-- resources/views/layouts/partials/sidebar.blade.php --}}
<style>
    :root {
        --sb-bg: #0f172a;
        --sb-fg: #e5e7eb;
        --sb-muted: #9ca3af;
        --sb-accent: #0E54DE;
        --sb-hover: rgba(255, 255, 255, .06);
        --sb-sep: rgba(255, 255, 255, .10);

        /* jarak antar dropdown */
        --flyout-gap-root: 8px;
        /* sidebar -> dropdown utama */
        --flyout-gap-nested: 2px;
        /* dropdown -> dropdown anak */
    }

    /* === SIDEBAR === */
    #kt_app_sidebar.app-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        background: var(--sb-bg);
        color: var(--sb-fg);
        border-right: 1px solid var(--sb-sep);
        transition: width .3s ease;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        overflow: visible;
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar.app-sidebar {
        width: 80px
    }

    /* === LOGO === */
    #kt_app_sidebar .app-sidebar-logo {
        border-bottom: 1px solid var(--sb-sep);
        padding: 1rem;
        position: relative
    }

    #kt_app_sidebar .logo-img {
        width: 190px;
        height: auto;
        display: block;
        transition: width .2s
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .logo-img {
        display: none !important
    }

    #kt_app_sidebar .app-sidebar-logo-minimize {
        display: none;
        /* width: 40px; */
        height: auto
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .app-sidebar-logo-minimize {
        display: block !important
    }

    /* === MENU (selalu vertikal) === */
    #kt_app_sidebar .menu {
        display: flex !important;
        flex-direction: column !important;
        gap: .25rem;
        padding: .5rem
    }

    #kt_app_sidebar .menu>.menu-item {
        display: block !important;
        width: 100%
    }

    #kt_app_sidebar .menu .menu-link {
        color: var(--sb-fg);
        border-radius: .625rem;
        padding: .6rem .75rem;
        gap: .5rem;
        display: flex !important;
        align-items: center;
        text-decoration: none;
        position: relative;
        width: 100%;
        transition: background .15s, color .15s, box-shadow .15s
    }

    #kt_app_sidebar .menu .menu-icon {
        color: var(--sb-muted);
        width: 1.25rem;
        min-width: 1.25rem;
        display: inline-flex;
        justify-content: center
    }

    #kt_app_sidebar .menu .menu-title {
        padding-left: .5rem;
        white-space: nowrap;
        overflow: hidden
    }

    #kt_app_sidebar .menu .menu-arrow {
        margin-left: auto;
        transition: transform .2s
    }

    #kt_app_sidebar .menu .menu-link:hover {
        background: var(--sb-hover);
        color: #fff
    }

    #kt_app_sidebar .menu .menu-link:hover .menu-icon {
        color: #fff
    }

    #kt_app_sidebar .menu .menu-link.active {
        background: var(--sb-accent) !important;
        color: #fff !important;
        box-shadow: 0 6px 16px rgba(14, 84, 222, .28)
    }

    #kt_app_sidebar .menu .menu-link.active .menu-icon {
        color: #fff !important
    }

    /* === SUBMENU biasa === */
    #kt_app_sidebar .menu-sub.menu-sub-accordion {
        display: none;
        margin-left: 1rem;
        border-left: 1px dashed var(--sb-sep);
        padding-left: .75rem;
        overflow: hidden
    }

    #kt_app_sidebar .menu-sub.menu-sub-accordion .menu-link {
        padding: .4rem .5rem;
        font-size: .9rem
    }

    #kt_app_sidebar .menu-item.menu-accordion:not(.show)>.menu-sub.menu-sub-accordion {
        display: none
    }

    #kt_app_sidebar .menu-item.menu-accordion.show>.menu-sub.menu-sub-accordion {
        display: block
    }

    #kt_app_sidebar .menu-item.menu-accordion.show>.menu-link .menu-arrow {
        transform: rotate(90deg)
    }

    /* === MINIMIZED === */
    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu {
        padding: .25rem .35rem
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-item {
        margin: 2px 0;
        position: relative
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-title,
    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-arrow {
        display: none !important
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-link {
        justify-content: center;
        padding: 0 !important;
        height: 44px;
        border-radius: 12px;
        margin: 0;
        width: 44px
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-icon {
        width: auto;
        font-size: 1.15rem;
        margin: 0
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu-sub.menu-sub-accordion {
        display: none !important
    }

    /* === FLYOUT utk minimized === */
    #kt_app_sidebar .menu-sub.menu-sub-dropdown {
        position: fixed !important;
        background: var(--sb-bg);
        border: 1px solid var(--sb-sep);
        border-radius: 12px;
        box-shadow: 0 12px 28px rgba(0, 0, 0, .45);
        padding: .5rem;
        z-index: 1060;
        min-width: 220px;
        display: none;
        opacity: 0;
        transform: translateX(-10px);
        /* hanya saat animasi masuk */
        transition: opacity .2s, transform .2s;
        max-height: calc(100vh - 16px);
        overflow-y: auto;
        overflow-x: hidden;
        /* <-- hilangkan scroll horizontal */
        padding-right: 8px;
        /* ruang untuk scrollbar vertikal */
    }

    #kt_app_sidebar .menu-sub.menu-sub-dropdown.show {
        display: block !important;
        opacity: 1;
        transform: none !important;
        /* <-- kunci: jangan jadi containing block */
    }

    #kt_app_sidebar .menu-sub.menu-sub-dropdown .menu-item+.menu-item {
        margin-top: 2px
    }

    #kt_app_sidebar .menu-sub.menu-sub-dropdown .menu-link {
        padding: .5rem .75rem;
        border-radius: .5rem;
        font-size: .9rem;
        width: 100%;
        justify-content: flex-start;
        height: auto
    }

    #kt_app_sidebar .menu-sub.menu-sub-dropdown .menu-link:hover {
        background: var(--sb-hover)
    }

    #kt_app_sidebar .menu-sub.menu-sub-dropdown .menu-title {
        display: block !important;
        padding-left: 0
    }

    #kt_app_sidebar .menu-sub.menu-sub-dropdown .menu-icon {
        margin-right: .5rem
    }

    #kt_app_sidebar .menu-sub.menu-sub-dropdown .menu-sub.menu-sub-dropdown {
        z-index: 2001
    }

    /* === Toggle FAB & user === */
    #kt_app_sidebar .sidebar-toggle-fab {
        position: absolute;
        top: 50%;
        right: -14px;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, .14);
        background: linear-gradient(180deg, rgba(255, 255, 255, .12), rgba(255, 255, 255, .05));
        box-shadow: 0 12px 24px rgba(0, 0, 0, .35);
        color: #0E54DE;
        cursor: pointer;
        transition: transform .18s, box-shadow .22s, background .22s, color .22s;
        z-index: 2000;
        pointer-events: auto;
    }

    #kt_app_sidebar .sidebar-toggle-fab:hover {
        transform: translateY(-50%) scale(1.06);
        box-shadow: 0 18px 34px rgba(0, 0, 0, .5)
    }

    [data-kt-app-sidebar-minimize="on"] #sidebarToggleIcon {
        transform: rotate(180deg)
    }

    [data-kt-app-sidebar-minimize="on"] .main-content {
        margin-left: 80px
    }

    /* USER */
    #kt_app_sidebar .app-sidebar-user {
        margin-top: auto;
        padding: 1rem;
        border-top: 1px solid var(--sb-sep);
        display: flex;
        align-items: center
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .app-sidebar-user {
        justify-content: center
    }

    [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .app-sidebar-user .user-info {
        display: none
    }

    #kt_app_sidebar .symbol {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700
    }

    #kt_app_sidebar .user-info {
        margin-left: .75rem
    }

    .user-name {
        font-weight: 600;
        color: #fff
    }

    .user-position {
        font-size: .8rem;
        color: var(--sb-muted)
    }

    .blinking-dot {
        animation: blink 1s infinite
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1
        }

        50% {
            opacity: 0
        }
    }
</style>

<div id="kt_app_sidebar" class="app-sidebar">
    <div class="app-sidebar-logo" id="kt_app_sidebar_logo">
        <div class="d-flex align-items-center">
            <img src="{{ asset('assets/media/logos/logo-putih.png') }}" alt="Logo" class="logo-img"
                data-kt-app-sidebar-minimize-small="on">
            <img src="{{ asset('assets/media/logos/logo-putih-kecil.png') }}" class="h-20px app-sidebar-logo-minimize"
                alt="Mini Logo">
        </div>
        <div id="kt_app_sidebar_toggle" class="sidebar-toggle-fab">
            <i id="sidebarToggleIcon" class="fas fa-arrow-left"></i>
        </div>
    </div>

    <div class="flex-grow-1" style="overflow-y:auto;overflow-x:visible;">
        @php
            $currentPath = request()->path();
            $isEmployeeCompetencies = str_starts_with($currentPath, 'employeeCompetencies');
            $isAssessment = str_starts_with($currentPath, 'assessment');
            $isHav = str_starts_with($currentPath, 'hav');
            $isIdp = str_starts_with($currentPath, 'idp');
            $isIcp = str_starts_with($currentPath, 'icp');
            $isRtc = str_starts_with($currentPath, 'rtc');
            $role = strtoupper(auth()->user()->role ?? '');
            $isUser = $role === 'USER';
            $isHRD = $role === 'HRD';
            $user = auth()->user();
            $isPrsdn = $user->employee->position === 'President';
        @endphp

        @include('layouts.partials.menu-content')
    </div>

    @php
        $name = trim($user->name ?? '');
        $initials = collect(preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1, 'UTF-8'), 'UTF-8'))
            ->implode('');
    @endphp

    <div class="app-sidebar-user">
        <div class="symbol">{{ $initials }}</div>
        <div class="user-info">
            <div class="user-name">{{ $user->name }}</div>
            <div class="user-position">{{ $user->employee->position }}</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.body;
        const sidebar = document.getElementById('kt_app_sidebar');
        const toggleBtn = document.getElementById('kt_app_sidebar_toggle');
        const toggleIcon = document.getElementById('sidebarToggleIcon');

        const isMinimized = () => body.getAttribute('data-kt-app-sidebar-minimize') === 'on';
        const setMinimized = (on) => {
            if (on) body.setAttribute('data-kt-app-sidebar-minimize', 'on');
            else body.removeAttribute('data-kt-app-sidebar-minimize');
            updateToggleIcon();
        };

        function updateToggleIcon() {
            if (!toggleIcon) return;
            toggleIcon.style.transform = isMinimized() ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        /* Toggle */
        toggleBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const next = !isMinimized();
            setMinimized(next);
            if (!isMinimized()) {
                document.querySelectorAll('#kt_app_sidebar .menu-sub.menu-sub-dropdown.show')
                    .forEach(el => el.classList.remove('show'));
            }
        });

        /* Accordions (expanded mode only) */
        function initAccordions() {
            const items = document.querySelectorAll('#kt_app_sidebar .menu-item.menu-accordion');
            items.forEach(acc => {
                const link = acc.querySelector(':scope > .menu-link');
                const sub = acc.querySelector(':scope > .menu-sub.menu-sub-accordion');
                if (!link || !sub) return;

                link.addEventListener('click', (ev) => {
                    if (isMinimized()) return;
                    ev.preventDefault();

                    const parentSub = acc.parentElement.closest('.menu-sub.menu-sub-accordion');
                    if (parentSub) {
                        parentSub.querySelectorAll(':scope > .menu-item.menu-accordion.show')
                            .forEach(sib => {
                                if (sib !== acc) sib.classList.remove('show');
                            });
                    }
                    acc.classList.toggle('show');
                });
            });
        }

        /* Flyouts (minimized) */
        function initFlyouts() {
            const accs = document.querySelectorAll('#kt_app_sidebar .menu-item.menu-accordion');
            accs.forEach(acc => {
                const dd = acc.querySelector(':scope > .menu-sub.menu-sub-dropdown');
                if (!dd) return;

                let hideTimer, hovering = false;

                acc.addEventListener('mouseenter', () => {
                    if (!isMinimized()) return;
                    clearTimeout(hideTimer);
                    showFlyout(acc, dd);
                });
                acc.addEventListener('mouseleave', (e) => {
                    if (!isMinimized()) return;
                    const r = dd.getBoundingClientRect();
                    if (e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e
                        .clientY <= r.bottom) return;
                    hideTimer = setTimeout(() => {
                        if (!hovering) hideFlyout(dd);
                    }, 120);
                });

                dd.addEventListener('mouseenter', () => {
                    if (isMinimized()) {
                        hovering = true;
                        clearTimeout(hideTimer);
                    }
                });
                dd.addEventListener('mouseleave', () => {
                    if (isMinimized()) {
                        hovering = false;
                        hideTimer = setTimeout(() => hideFlyout(dd), 120);
                    }
                });

                // nested
                dd.querySelectorAll('.menu-item.menu-accordion').forEach(nested => {
                    const child = nested.querySelector(':scope > .menu-sub.menu-sub-dropdown');
                    if (!child) return;
                    nested.addEventListener('mouseenter', () => {
                        if (isMinimized()) showFlyout(nested, child);
                    });
                    nested.addEventListener('mouseleave', () => {
                        if (isMinimized()) setTimeout(() => hideFlyout(child), 120);
                    });
                });
            });
        }

        function clamp(v, min, max) {
            return Math.min(Math.max(v, min), max);
        }

        function showFlyout(parent, dd) {
            const linkEl = parent.querySelector(':scope > .menu-link') || parent;
            const linkRect = linkEl.getBoundingClientRect();

            const ancestorDD = parent.closest('.menu-sub.menu-sub-dropdown.show');
            const isNested = !!ancestorDD;

            const sidebarRect = sidebar.getBoundingClientRect();

            /* tampilkan sementara agar bisa dihitung */
            dd.classList.add('show');
            dd.style.visibility = 'hidden';

            const ddRect = dd.getBoundingClientRect();
            const rootStyles = getComputedStyle(document.documentElement);
            const GAP_ROOT = parseInt(rootStyles.getPropertyValue('--flyout-gap-root')) || 8;
            const GAP_NESTED = parseInt(rootStyles.getPropertyValue('--flyout-gap-nested')) || 2;

            const ancRect = (ancestorDD ? ancestorDD.getBoundingClientRect() : sidebarRect);
            const baseRight = ancRect.right;
            const baseLeft = ancRect.left;

            /* X: anchor ke tepi kanan dropdown induk */
            let left = baseRight + (isNested ? GAP_NESTED : GAP_ROOT);
            if (left + ddRect.width + 4 > window.innerWidth) {
                left = baseLeft - ddRect.width - (isNested ? GAP_NESTED : GAP_ROOT); // flip ke kiri
            }

            /* Y: sejajarkan dengan item; cegah overflow bawah */
            let top = clamp(linkRect.top, 8, window.innerHeight - ddRect.height - 8);

            dd.style.left = left + 'px';
            dd.style.top = top + 'px';
            dd.style.visibility = '';

            requestAnimationFrame(() => {
                const r = dd.getBoundingClientRect();
                if (r.bottom > window.innerHeight - 8) {
                    dd.style.top = Math.max(8, top - (r.bottom - (window.innerHeight - 8))) + 'px';
                }
            });
        }

        function hideFlyout(dd) {
            dd.classList.remove('show');
            dd.querySelectorAll('.menu-sub.menu-sub-dropdown.show').forEach(x => x.classList.remove('show'));
        }

        // klik di luar => tutup semua flyout saat minimized
        document.addEventListener('click', (e) => {
            if (isMinimized() && !sidebar.contains(e.target)) {
                document.querySelectorAll('#kt_app_sidebar .menu-sub.menu-sub-dropdown.show').forEach(
                    hideFlyout);
            }
        });
        window.addEventListener('resize', () => {
            if (isMinimized()) {
                document.querySelectorAll('#kt_app_sidebar .menu-sub.menu-sub-dropdown.show').forEach(
                    hideFlyout);
            }
        });

        // init
        updateToggleIcon();
        initAccordions();
        initFlyouts();
    });
</script>
