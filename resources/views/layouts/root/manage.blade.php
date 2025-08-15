<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HR - People Development')</title>

    <!-- Font (boleh dipertahankan) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <!-- Metronic Global CSS -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <!-- DataTables 2 (Bootstrap 5) -->
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    @stack('custom-css')

    <script>
        // Theme mode (tetap dipertahankan)
        (function() {
            var defaultThemeMode = "light";
            var themeMode = localStorage.getItem("data-bs-theme") ?? defaultThemeMode;
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        })();
    </script>

    <style>
        /* Tweak aksesibilitas untuk kalangan senior */
        body {
            font-size: 1.02rem;
        }

        .table {
            font-size: 1.05rem;
        }

        .form-control,
        .form-select,
        .btn {
            font-size: 1rem;
        }

        .btn {
            border-radius: .6rem;
        }
    </style>
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="false" data-kt-app-sidebar-fixed="false" data-kt-app-sidebar-hoverable="false"
    data-kt-app-sidebar-push-header="false" data-kt-app-sidebar-push-toolbar="false"
    data-kt-app-sidebar-push-footer="false" data-kt-app-toolbar-enabled="false" data-kt-app-sidebar-minimize="off"
    class="app-default">

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            @include('layouts.partials.header')

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">

                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-4">
                            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                                @yield('toolbar')
                            </div>
                        </div>

                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            @yield('main')
                        </div>
                    </div>

                    @include('layouts.partials.footer')
                </div>
            </div>

        </div>
    </div>

    <script>
        var hostUrl = "{{ asset('assets/index.html') }}";
    </script>

    <!-- Metronic Global JS -->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <!-- DataTables 2 -->
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>

    @stack('scripts')
    {{-- Flash: success --}}
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    title: "Sukses!",
                    text: @json(session('success')),
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    {{-- Flash: warning (rapikan dengan @json) --}}
    @if (session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const msg = @json(session('warning'));
                if (window.Swal) {
                    Swal.fire({
                        title: 'Attention',
                        text: msg,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(msg);
                }
            });
        </script>
    @endif


    @if (session('show_first_login_alert'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Welcome!',
                    text: 'This is your first login. Please change your password to continue.',
                    icon: 'warning',
                    confirmButtonText: 'I Understand',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonColor: '#3085d6',
                    backdrop: `rgba(0,0,0,0.7)`
                });
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Perhatian',
                    text: '{{ session('warning') }}',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif
</body>

</html>
