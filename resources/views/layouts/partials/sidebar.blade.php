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
    <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
        data-kt-menu="true" data-kt-menu-expand="false">



        <!--begin:People Development Menu Accordion-->
        <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="fas fa-users-cog"></i>
                </span>
                <span class="menu-title ps-1">People Development</span>
                <span class="menu-arrow"></span>
            </span>

            <div class="menu-sub menu-sub-accordion menu-active-bg">
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                        <span class="menu-icon"><i class="fas fa-dashboard"></i></span>
                        <span class="menu-title ps-1">Development Plan</span>
                    </a>
                </div>

                @if (auth()->user()->role == 'User')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('employee') ? 'active' : '' }}" href="/employee">
                            <span class="menu-icon"><i class="fas fa-user-tie"></i></span>
                            <span class="menu-title ps-1">Employee Profile</span>
                        </a>
                    </div>
                @else
                    <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fas fa-user-tie"></i></span>
                            <span class="menu-title ps-1">Employee Profile</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion menu-active-bg">
                            <div class="menu-item">
                                <a class="menu-link" href="/employee/aii">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AII</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="/employee/aiia">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AIIA</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->role == 'User')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('assessment') ? 'active' : '' }}" href="/assessment">
                            <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                            <span class="menu-title ps-1">Assessment</span>
                        </a>
                    </div>
                @else
                    <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                            <span class="menu-title ps-1">Assessment</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion menu-active-bg">
                            <div class="menu-item">
                                <a class="menu-link" href="/assessment/aii">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AII</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="/assessment/aiia">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AIIA</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-th-large fs-2"></i></span>
                        <span class="menu-title ps-1">HAV</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('hav/list') ? 'active' : '' }}" href="/hav/list">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">HAV List</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('hav') ? 'active' : '' }}" href="/hav">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">HAV Quadran</span>
                            </a>
                        </div>
                    </div>
                </div>

                @if (auth()->user()->role == 'User')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('idp') ? 'active' : '' }}" href="/idp">
                            <span class="menu-icon"><i class="fas fa-code-branch"></i></span>
                            <span class="menu-title ps-1">IDP</span>
                        </a>
                    </div>
                @else
                    <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fas fa-code-branch"></i></span>
                            <span class="menu-title ps-1">IDP</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion menu-active-bg">
                            <div class="menu-item">
                                <a class="menu-link" href="/idp/aii">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AII</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="/idp/aiia">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AIIA</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->role == 'User')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('rtc') ? 'active' : '' }}" href="/rtc">
                            <span class="menu-icon"><i class="fas fa-sitemap"></i></span>
                            <span class="menu-title ps-1">RTC</span>
                        </a>
                    </div>
                @else
                    <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fas fa-sitemap"></i></span>
                            <span class="menu-title ps-1">RTC</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion menu-active-bg">
                            <div class="menu-item">
                                <a class="menu-link" href="/rtc/aii">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AII</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="/rtc/aiia">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AIIA</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <!--end:People Development Menu Accordion-->

        <!--end:People Development Menu Accordion-->

        <!--begin:Master Section-->
        <!--begin:Master Menu Accordion-->
        <div data-kt-menu-trigger="click" class="menu-item menu-accordion pt-5">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="fas fa-cogs"></i>
                </span>
                <span class="menu-title ps-1">Master</span>
                <span class="menu-arrow"></span>
            </span>

            <div class="menu-sub menu-sub-accordion menu-active-bg" style="overflow: hidden;">

                <!-- Employee Submenu Accordion -->
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-user-tie"></i></span>
                        <span class="menu-title ps-1">Employee</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/employee/aii') ? 'active' : '' }}"
                                href="/master/employee/aii">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">AII</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/employee/aiia') ? 'active' : '' }}"
                                href="/master/employee/aiia">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">AIIA</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Grade -->
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('master/grade') ? 'active' : '' }}" href="/master/grade">
                        <span class="menu-icon"><i class="fas fa-layer-group"></i></span>
                        <span class="menu-title ps-1">Grade</span>
                    </a>
                </div>

                <!-- Department -->
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('master/department') ? 'active' : '' }}"
                        href="/master/department">
                        <span class="menu-icon"><i class="fas fa-building"></i></span>
                        <span class="menu-title ps-1">Department</span>
                    </a>
                </div>

                <!-- Division -->
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('master/division') ? 'active' : '' }}"
                        href="/master/division">
                        <span class="menu-icon"><i class="fas fa-network-wired"></i></span>
                        <span class="menu-title ps-1">Division</span>
                    </a>
                </div>

                <!-- Section -->
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('master/section') ? 'active' : '' }}"
                        href="/master/section">
                        <span class="menu-icon"><i class="fas fa-users"></i></span>
                        <span class="menu-title ps-1">Section</span>
                    </a>
                </div>

            </div>
        </div>

        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
            <!-- Begin: Menu Link -->
            <span class="menu-link {{ request()->is('Competency') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </span>
                <span class="menu-title ps-1">Training</span>
                <span class="menu-arrow"></span>
            </span>
            <!-- End: Menu Link -->

            <!-- Begin: Menu Sub -->
            <div class="menu-sub menu-sub-accordion menu-active-bg" kt-hidden-height="84"
                style="display: none; overflow: hidden;">
                <!-- Menu Item: FAQ Classic -->
                <div class="menu-item">
                    <a class="menu-link" href="/emp_competency">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Employee Competency</span>
                    </a>
                </div>

                <!-- Menu Item: FAQ Extended -->
                <div class="menu-item">
                    <a class="menu-link" href="/competencies">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Competency</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link" href="/group_competency">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Group Competency</span>
                    </a>
                </div>
            </div>
            <!-- End: Menu Sub -->
        </div>
        <!--end:Master Menu Accordion-->

        <!--end:Master Section-->
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
