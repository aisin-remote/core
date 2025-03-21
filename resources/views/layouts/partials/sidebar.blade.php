<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <style>
        .logo-img {
            width: 190px;
            /* Sesuaikan ukuran */
            height: auto;
            /* Menjaga proporsi */
            display: block;
        }

        .app-sidebar-minimize .app-sidebar-logo img {
            display: none;
        }
    </style>


    @php
        $isUser = auth()->user()->role == 'User';
    @endphp

    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <div class="d-flex align-items-center">
            <img src="{{ asset('assets/media/logos/logo-putih.png') }}" alt="Logo" class="logo-img">
            <img alt="Logo" src="{{ asset('assets/media/logos/logo-putih-kecil.png') }}"
                class="h-20px app-sidebar-logo-minimize">
        </div>

        <div id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <i id="sidebarToggleIcon" class="fas fa-arrow-left fs-4"></i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->

    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                    data-kt-menu="true" data-kt-menu-expand="false">
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content"><span
                                class="menu-heading fw-bold text-uppercase fs-7">Dashboard</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                            <span class="menu-icon">
                                <i class="fas fa-dashboard"></i>
                            </span>
                            <span class="menu-title ps-1">Dashboard</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    @if (auth()->user()->role == 'User')
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link {{ request()->is('employee') ? 'active' : '' }}" href="/employee">
                                <span class="menu-icon">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <span class="menu-title ps-1">Employee Profile</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                    @else
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <!-- Begin: Menu Link -->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <span class="menu-title ps-1">Employee Profile</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!-- End: Menu Link -->


                            <!-- Begin: Menu Sub -->
                            <div class="menu-sub menu-sub-accordion menu-active-bg" kt-hidden-height="84"
                                style="display: none; overflow: hidden;">
                                <!-- Menu Item: FAQ Classic -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/employee/aii">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>

                                <!-- Menu Item: FAQ Extended -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/employee/aiia">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                            <!-- End: Menu Sub -->
                        </div>
                    @endif

                    @if (auth()->user()->role == 'User')
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link {{ request()->is('assessment') ? 'active' : '' }}" href="/assessment">
                                <span class="menu-icon">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                                <span class="menu-title ps-1">Assesment</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                    @else
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <!-- Begin: Menu Link -->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                                <span class="menu-title ps-1">Assessment</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!-- End: Menu Link -->


                            <!-- Begin: Menu Sub -->
                            <div class="menu-sub menu-sub-accordion menu-active-bg" kt-hidden-height="84"
                                style="display: none; overflow: hidden;">
                                <!-- Menu Item: FAQ Classic -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/assessment/aii">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>

                                <!-- Menu Item: FAQ Extended -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/assessment/aiia">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                            <!-- End: Menu Sub -->
                        </div>
                    @endif

                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <!-- Begin: Menu Link -->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fas fa-th-large fs-2"></i>
                            </span>
                            <span class="menu-title ps-1">HAV</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!-- End: Menu Link -->


                        <!-- Begin: Menu Sub -->
                        <div class="menu-sub menu-sub-accordion menu-active-bg" kt-hidden-height="84"
                            style="display: none; overflow: hidden;">
                            <!-- Menu Item: FAQ Classic -->
                            <div class="menu-item">
                                <a class="menu-link" href="/hav/list-create">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">HAV Entry</span>
                                </a>
                            </div>

                            <!-- Menu Item: FAQ Extended -->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->is('hav') ? 'active' : '' }}" href="/hav">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">HAV Quadran</span>
                                </a>
                            </div>
                        </div>
                        <!-- End: Menu Sub -->
                    </div>


                    @if (auth()->user()->role == 'User')
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link {{ request()->is('idp') ? 'active' : '' }}" href="/idp">
                                <span class="menu-icon">
                                    <i class="fas fa-code-branch"></i>
                                </span>
                                <span class="menu-title ps-1">IDP</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                    @else
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <!-- Begin: Menu Link -->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="fas fa-code-branch"></i>
                                </span>
                                <span class="menu-title ps-1">IDP</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!-- End: Menu Link -->


                            <!-- Begin: Menu Sub -->
                            <div class="menu-sub menu-sub-accordion menu-active-bg" kt-hidden-height="84"
                                style="display: none; overflow: hidden;">
                                <!-- Menu Item: FAQ Classic -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/idp/aii">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>

                                <!-- Menu Item: FAQ Extended -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/idp/aiia">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                            <!-- End: Menu Sub -->
                        </div>
                    @endif

                    @if (auth()->user()->role == 'User')
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link {{ request()->is('rtc') ? 'active' : '' }}" href="/rtc">
                                <span class="menu-icon">
                                    <i class="fas fa-server"></i>
                                </span>
                                <span class="menu-title ps-1">RTC</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                    @else
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <!-- Begin: Menu Link -->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="fas fa-server"></i>
                                </span>
                                <span class="menu-title ps-1">RTC</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!-- End: Menu Link -->


                            <!-- Begin: Menu Sub -->
                            <div class="menu-sub menu-sub-accordion menu-active-bg" kt-hidden-height="84"
                                style="display: none; overflow: hidden;">
                                <!-- Menu Item: FAQ Classic -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/rtc/aii">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>

                                <!-- Menu Item: FAQ Extended -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/rtc/aiia">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                            <!-- End: Menu Sub -->
                        </div>
                    @endif

                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Master</span>
                        </div>
                        <!--end:Menu content-->
                    </div>

                    @if (auth()->user()->role == 'User')
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link {{ request()->is('employee') ? 'active' : '' }}" href="/employee">
                                <span class="menu-icon">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <span class="menu-title ps-1">Employee Profile</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                    @else
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <!-- Begin: Menu Link -->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <span class="menu-title ps-1">Employee</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!-- End: Menu Link -->


                            <!-- Begin: Menu Sub -->
                            <div class="menu-sub menu-sub-accordion menu-active-bg" kt-hidden-height="84"
                                style="display: none; overflow: hidden;">
                                <!-- Menu Item: FAQ Classic -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/master/employee/aii">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>

                                <!-- Menu Item: FAQ Extended -->
                                <div class="menu-item">
                                    <a class="menu-link" href="/master/employee/aiia">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                            <!-- End: Menu Sub -->
                        </div>
                    @endif

                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->is('master/department') ? 'active' : '' }}"
                            href="/master/department">
                            <span class="menu-icon">
                                <i class="fas fa-server"></i>
                            </span>
                            <span class="menu-title ps-1">Department</span>
                        </a>
                        <!--end:Menu link-->
                    </div>

                    <!--end:Menu item-->
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
</div>
<!--end::Sidebar-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleButton = document.getElementById("kt_app_sidebar_toggle");
        const icon = document.getElementById("sidebarToggleIcon");

        toggleButton.addEventListener("click", function() {
            // Toggle class on body
            document.body.classList.toggle("app-sidebar-minimize");

            // Update icon based on sidebar state
            if (document.body.classList.contains("app-sidebar-minimize")) {
                icon.classList.replace("fa-arrow-left", "fa-arrow-right");
            } else {
                icon.classList.replace("fa-arrow-right", "fa-arrow-left");
            }
        });

        // Ensure icon updates correctly on page load
        if (document.body.classList.contains("app-sidebar-minimize")) {
            icon.classList.replace("fa-arrow-left", "fa-arrow-right");
        }
    });
</script>
