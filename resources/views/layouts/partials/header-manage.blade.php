{{-- resources/views/layouts/partials/header.blade.php --}}

<style>
    :root {
        --hd-bg: #fbfbfb;
        --hd-fg: #0f172a;
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
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        border-bottom-color: transparent;
    }

    .app-navbar .btn.btn-icon {
        border: 1px solid var(--hd-sep);
        background: transparent;
        color: #64748b;
        border-radius: .75rem;
        transition: background-color .15s, color .15s, box-shadow .15s;
    }

    .app-navbar .btn.btn-icon:hover {
        background: var(--hd-hover);
        color: #0b1224;
        box-shadow: 0 4px 14px rgba(14, 84, 222, .18);
    }

    .app-header-title {
        font-weight: 700;
        font-size: 1.125rem;
        line-height: 1.35;
    }

    .dropdown-menu {
        border-radius: 1rem;
        border: 1px solid var(--hd-sep);
        box-shadow: 0 20px 50px rgba(0, 0, 0, .12);
    }

    /* ukuran nyaman dibaca */
    #kt_app_header,
    #kt_app_header .dropdown-item,
    #kt_app_header .form-control {
        font-size: 1rem;
    }

</style>

<div id="kt_app_header" class="app-header">
    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">

        {{-- Left: brand + page title --}}
        <div class="d-flex align-items-center gap-3">
            {{-- Mobile sidebar toggle (opsional, kalau pakai sidebar) --}}
            <button class="btn btn-icon d-lg-none" id="kt_app_sidebar_mobile_toggle" type="button" aria-label="Toggle sidebar">
                <i class="ki-duotone ki-abstract-14 fs-3"><span class="path1"></span><span class="path2"></span></i>
            </button>

            {{-- Logo (opsional) --}}
            <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
                <img src="{{ asset('assets/media/logos/satu-aisin-final1.png') }}" alt="Logo" class="h-30px me-2" />
                <span class="visually-hidden">Home</span>
            </a>

            {{-- Judul halaman --}}
            @php
            $headerTitle = trim($__env->yieldContent('title')) ?: 'HR - People Development';
            @endphp
            <div class="app-header-title d-none d-md-block">{{ $headerTitle }}</div>
        </div>

        {{-- Right: actions --}}
        <div class="app-navbar d-flex align-items-center gap-2">

            {{-- Search (dropdown Bootstrap) --}}
            <div class="dropdown">
                <button class="btn btn-icon" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Search">
                    <i class="ki-duotone ki-magnifier fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
                    <form action="" method="GET" class="d-flex" role="search">
                        <input name="q" type="text" class="form-control me-2" placeholder="Cari..." aria-label="Search" autofocus>
                        <button class="btn btn-primary" type="submit">Cari</button>
                    </form>
                </div>
            </div>

            {{-- User menu --}}
            <div class="dropdown">
                <button class="btn p-0 border-0" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
                    <span class="d-inline-flex align-items-center">
                        <img src="{{ asset('assets/media/avatars/user.jpg') }}" class="rounded-3" width="35" height="35" alt="User">
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2">
                        <div class="fw-semibold">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-muted small">{{ auth()->user()->email ?? '' }}</div>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    @if (auth()->user()?->role === 'User')
                    <li><a class="dropdown-item" href="/employee/detail/{{ auth()->user()->employee->npk }}">My Profile</a></li>
                    <li><a class="dropdown-item" href="/change-password">Change Password</a></li>
                    @endif

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout.auth') }}" method="post" class="px-3 py-2">
                            @csrf
                            <button type="submit" class="btn btn-light w-100">Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>

            {{-- Mobile header menu toggle (opsional kalau punya header drawer) --}}
            {{-- <button class="btn btn-icon d-lg-none" id="kt_app_header_menu_toggle" aria-label="Toggle header menu">
        <i class="ki-duotone ki-element-4 fs-2"><span class="path1"></span><span class="path2"></span></i>
      </button> --}}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('kt_app_header');

        // efek shadow saat scroll
        const onScroll = () => {
            if (!header) return;
            if (window.scrollY > 4) header.classList.add('is-scrolled');
            else header.classList.remove('is-scrolled');
        };
        onScroll();
        window.addEventListener('scroll', onScroll, {
            passive: true
        });

        // autofocus input saat dropdown search dibuka
        const searchDropdown = document.querySelector('[aria-label="Search"]');
        if (searchDropdown) {
            searchDropdown.addEventListener('shown.bs.dropdown', () => {
                const input = document.querySelector('.dropdown-menu [name="q"]');
                input && input.focus();
            });
        }
    });

</script>
