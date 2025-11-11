<!-- ============ HEADER ============ -->
<style>
    :root {
        --hd-bg: #fbfbfb;
        --hd-fg: #0f172a;
        --hd-muted: #cbd5e1;
        --hd-sep: rgba(0, 0, 0, .08);
        --hd-accent: #0E54DE;
        --hd-hover: rgba(14, 84, 222, .08);
    }

    #kt_app_header.app-header {
        background: var(--hd-bg);
        color: var(--hd-fg);
        border-bottom: 1px solid var(--hd-sep);
        backdrop-filter: saturate(140%) blur(6px);
        transition: box-shadow .2s ease, background-color .2s ease, border-color .2s ease;
    }

    #kt_app_header.is-scrolled {
        box-shadow: 0 8px 24px rgba(0, 0, 0, .22);
        border-bottom-color: transparent;
    }

    #kt_app_header .menu .menu-title {
        font-size: 1.125rem;
        line-height: 1.35;
        font-weight: 700;
        color: var(--hd-fg);
    }

    .app-navbar .btn.btn-icon {
        border: 1px solid var(--hd-sep);
        background: transparent;
        color: var(--hd-muted);
        transition: background-color .15s ease, color .15s ease, border-color .15s ease, box-shadow .15s ease;
        border-radius: .75rem;
    }

    .app-navbar .btn.btn-icon:hover,
    .app-navbar .btn.btn-icon:focus {
        background: var(--hd-hover);
        color: #0b1224;
        border-color: var(--hd-sep);
        box-shadow: 0 4px 14px rgba(14, 84, 222, .18);
    }

    #kt_header_search [data-kt-search-element="content"].menu-sub {
        background: #0b1224;
        border: 1px solid var(--hd-sep);
        border-radius: 1rem;
    }

    #kt_header_search .search-input {
        background: rgba(255, 255, 255, .06) !important;
        color: var(--hd-fg) !important;
        border-radius: 999px;
        border: 1px solid var(--hd-sep) !important;
        padding-block: .65rem !important;
    }

    #kt_header_search .search-input::placeholder {
        color: #9aa3b2;
    }

    #kt_header_search .search-reset,
    #kt_header_search .search-spinner {
        color: var(--hd-muted) !important;
    }

    #kt_menu_notifications.menu-sub,
    #kt_header_user_menu_toggle+.menu {
        background: #0b1224;
        border: 1px solid var(--hd-sep);
        border-radius: 1rem;
        color: var(--hd-fg);
    }

    .app-navbar .btn[data-has-unread="1"] {
        position: relative;
    }

    .app-navbar .btn[data-has-unread="1"]::after {
        content: "";
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #ef4444;
        box-shadow: 0 0 0 2px var(--hd-bg);
    }

    #kt_app_sidebar_mobile_toggle .btn,
    #kt_app_header_menu_toggle {
        border-radius: .75rem;
        border: 1px solid var(--hd-sep);
        color: var(--hd-muted);
    }

    .app-navbar .btn:focus-visible,
    #kt_app_header .menu .menu-title:focus-visible {
        outline: 2px solid #93c5fd;
        outline-offset: 2px;
    }

    .header-clock {
        font-variant-numeric: tabular-nums;
        color: var(--hd-fg);
        border: 1px solid var(--hd-sep);
        background: transparent;
        padding: .45rem .75rem;
        border-radius: .75rem;
        line-height: 1;
        user-select: none;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    .header-clock:hover {
        background: var(--hd-hover);
    }
</style>

<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}"
    data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}"
    data-kt-sticky-animation="false">

    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
        id="kt_app_header_container">

        <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1"><span class="path1"></span><span
                        class="path2"></span></i>
            </div>
        </div>

        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="index.html" class="d-lg-none"></a>
        </div>

        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
            <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true"
                data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}"
                data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end"
                data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true"
                data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">

                <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0"
                    id="kt_app_header_menu" data-kt-menu="true">

                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
                        class="menu-item here show menu-here-bg menu-lg-down-accordion me-0 me-lg-2">
                        <span class="menu-link">
                            @php
                                $title = match (ucfirst(request()->path())) {
                                    'Hav' => 'Human Assets Value',
                                    'Idp' => 'Individual Development Plan',
                                    default => ucfirst(request()->path()),
                                };
                            @endphp
                            <span class="menu-title">{{ $title }}</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                    </div>

                </div>
            </div>

            <div class="app-navbar flex-shrink-0">
                <!-- Live Clock (WIB) -->
                <div class="app-navbar-item d-none d-md-flex me-2" title="Waktu Jakarta (WIB)" data-bs-toggle="tooltip">
                    <span id="header-clock" class="header-clock">--:--:-- WIB</span>
                </div>
                @php
                    $photo = auth()->user()->employee?->photo;
                @endphp
                <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                    <div class="cursor-pointer symbol symbol-35px"
                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <img src="{{ $photo ? Storage::url($photo) : asset('assets/media/avatars/300-1.jpg') }}"
                            class="rounded-3" alt="user">
                    </div>

                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-300px"
                        data-kt-menu="true">
                        <div class="separator my-2"></div>
                        @php
                            $tok = App\Support\OpaqueId::encode((int) auth()->user()->id);
                        @endphp
                        @if (auth()->user())
                            <div class="menu-item px-5">
                                <a href="/employee/detail/{{ $tok }}" class="menu-link px-5">My
                                    Profile</a>
                            </div>
                            <div class="menu-item px-5">
                                <a href="/change-password" class="menu-link px-5">Change Password</a>
                            </div>
                        @endif

                        <div class="separator my-2"></div>
                        <div class="menu-item px-5">
                            <form action="{{ route('logout.auth') }}" method="post">
                                @csrf @method('POST')
                                <div class="d-grid py-4 px-7 pt-8">
                                    <button type="submit" class="btn btn-light-primary" id="logout">Sign
                                        Out</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="app-navbar-item d-lg-none ms-2 me-n2" title="Show header menu">
                    <div class="btn btn-flex btn-icon btn-active-color-primary w-30px h-30px"
                        id="kt_app_header_menu_toggle">
                        <i class="ki-duotone ki-element-4 fs-1"><span class="path1"></span><span
                                class="path2"></span></i>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('kt_app_header');
        const onScroll = () => {
            if (!header) return;
            if (window.scrollY > 4) header.classList.add('is-scrolled');
            else header.classList.remove('is-scrolled');
        };
        onScroll();
        window.addEventListener('scroll', onScroll, {
            passive: true
        });

        if (window.bootstrap?.Tooltip) {
            document.querySelectorAll('.app-navbar .btn[title], .app-navbar [data-bs-toggle="tooltip"]')
                .forEach(el => {
                    window.bootstrap.Tooltip.getOrCreateInstance(el, {
                        trigger: 'hover'
                    });
                });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('kt_app_header');
        const onScroll = () => {
            if (!header) return;
            if (window.scrollY > 4) header.classList.add('is-scrolled');
            else header.classList.remove('is-scrolled');
        };
        onScroll();
        window.addEventListener('scroll', onScroll, {
            passive: true
        });

        // Init tooltips yg sudah ada
        if (window.bootstrap?.Tooltip) {
            document.querySelectorAll('.app-navbar .btn[title], .app-navbar [data-bs-toggle="tooltip"]')
                .forEach(el => {
                    window.bootstrap.Tooltip.getOrCreateInstance(el, {
                        trigger: 'hover'
                    });
                });
        }

        // === CLOCK (WIB) ===
        const clockEl = document.getElementById('header-clock');
        const tz = 'Asia/Jakarta';

        function updateClock() {
            if (!clockEl) return;
            const now = new Date();

            // Waktu HH:mm:ss (24 jam)
            const time = new Intl.DateTimeFormat('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: tz
            }).format(now);

            // Tooltip: hari, tanggal lengkap
            const dateLabel = new Intl.DateTimeFormat('id-ID', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                timeZone: tz
            }).format(now);

            clockEl.textContent = `${time} WIB`;
            clockEl.setAttribute('title', `${dateLabel} — WIB`);

            // Refresh tooltip title bila bootstrap aktif
            if (window.bootstrap?.Tooltip) {
                const tip = window.bootstrap.Tooltip.getInstance(clockEl) || window.bootstrap.Tooltip
                    .getOrCreateInstance(clockEl);
                tip.setContent({
                    '.tooltip-inner': clockEl.getAttribute('title')
                });
            }
        }

        updateClock();
        setInterval(updateClock, 1000);
    });
</script>

<!-- end HEADER -->
