<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HR- People Development</title>

    <meta charset="utf-8" />
    <meta name="description" content="People Development Portal" />
    <meta name="keywords" content="tailwind, bootstrap, metronic, admin, dashboard" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="HR - People Development" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/satu-aisin-final1.png') }}" />

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <!-- Vendor CSS -->
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" />

    <!-- Select2 + DataTables CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    <!-- Global CSS -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" />

    @stack('custom-css')

    <style>
        /* jaga-jaga dropdown tidak ketutup backdrop */
        .modal .select2-container {
            z-index: 2055
        }

        .modal .select2-container .select2-dropdown {
            z-index: 2056
        }
    </style>
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" data-kt-app-sidebar-minimize="off"
    class="app-default">

    <script>
        (function() {
            var defaultThemeMode = "light",
                themeMode;
            if (document.documentElement) {
                if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                    themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
                } else if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode
                }
                if (themeMode === "system") {
                    themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"
                }
                document.documentElement.setAttribute("data-bs-theme", themeMode);
            }
        })();
    </script>

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            @include('layouts.partials.header')

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                @include('layouts.partials.sidebar')

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
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

    <!-- ===== JS ORDER: jQuery -> Select2 -> Metronic core -> lainnya -> @stack('scripts') ===== -->

    <!-- 1) jQuery (WAJIB untuk Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- 2) Select2 & DataTables (butuh jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <!-- 3) Metronic bundles (boleh setelah jQuery) -->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <!-- 4) Charts (opsional) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <!-- 5) Highcharts (opsional) -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <!-- 6) Metronic page vendors -->
    <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>

    <!-- 7) Widgets / Custom -->
    <script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/create-app.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/new-target.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/users-search.js') }}"></script>

    <!-- 8) Page-specific scripts -->
    @stack('scripts')

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
                    title: 'Warning',
                    text: @json(session('warning')),
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

</body>

</html>
