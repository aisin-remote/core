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

        span.menu-link.active {
            background-color: #0E54DE !important;
            color: white !important;
        }


        .menu-link.active {
            background-color: #0E54DE !important;
            color: white !important;
        }
    </style>

    @php
        $isUser = auth()->user()->role == 'User';
    @endphp

    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo" style="margin-top: 20px;">
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

    <!--begin::Sidebar Scrollable Menu Area-->
    <div class="app-sidebar-menu flex-grow-1 overflow-auto" style="max-height: calc(100vh - 120px);">

        <!--begin::sidebar menu-->
        <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
            data-kt-menu="true" style="margin-top: -15px;" data-kt-menu-expand="true">

            <!--begin:People Development Menu Accordion-->
            @php
                $currentPath = request()->path();
                $currentMenu = explode('/', $currentPath)[0];

                $isEmployee = str_starts_with($currentPath, 'dashboard');
                $isEmployee = str_starts_with($currentPath, 'employee');
                $isAssessment = str_starts_with($currentPath, 'assessment');
                $isHav = str_starts_with($currentPath, 'hav');
                $isIdp = str_starts_with($currentPath, 'idp');
                $isRtc = str_starts_with($currentPath, 'rtc');
                $isgrade = str_starts_with($currentPath, 'grade');
                $isdivision = str_starts_with($currentPath, 'division');
                $isdepartment = str_starts_with($currentPath, 'department');
                $issection = str_starts_with($currentPath, 'section');
            @endphp
            @php
                $jobPositions = [
                    'Show All',
                    'Direktur',
                    'GM',
                    'Manager',
                    'Coordinator',
                    'Section Head',
                    'Supervisor',
                    'Leader',
                    'JP',
                    'Operator',
                ];
            @endphp

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                data-kt-menu="true" style="margin-top: 40px;" data-kt-menu-expand="true">

                <div class="menu-item menu-accordion" data-kt-menu-expand="true" data-kt-menu-trigger="click"
                    id="menu-people-development">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-users-cog"></i></span>
                        <span class="menu-title ps-1">People Development</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion menu-active-bg">

                        {{-- Dashboard --}}
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                                {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                <span class="menu-title ps-1">Development Plan</span>
                            </a>
                        </div>

                        {{-- EMPLOYEE --}}
                        @if (auth()->user()->role == 'User')
                            <div class="menu-item">
                                <a class="menu-link {{ $currentPath === 'employee' ? 'active' : '' }}" href="/employee">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">Employee Profile</span>
                                </a>
                            </div>
                        @else
                            <div class="menu-item menu-accordion {{ $isEmployee ? 'show' : '' }}"
                                data-kt-menu-expand="true" data-kt-menu-trigger="click" id="menu-employee-profile">
                                <span class="menu-link {{ $isEmployee ? 'active' : '' }}">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">Employee Profile</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'employee/aii' ? 'active' : '' }}"
                                            href="/employee/aii">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">AII</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'employee/aiia' ? 'active' : '' }}"
                                            href="/employee/aiia">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">AIIA</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ASSESSMENT --}}
                        @if (auth()->user()->role == 'User')
                            <div class="menu-item">
                                <a class="menu-link {{ $currentPath === 'assessment' ? 'active' : '' }}"
                                    href="/assessment">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">Assessment</span>
                                </a>
                            </div>
                        @else
                            <div class="menu-item menu-accordion {{ $isAssessment ? 'show' : '' }}"
                                data-kt-menu-expand="true" data-kt-menu-trigger="click" id="menu-assessment">
                                <span class="menu-link {{ $isAssessment ? 'active' : '' }}">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">Assessment</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'assessment/aii' ? 'active' : '' }}"
                                            href="/assessment/aii">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">AII</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'assessment/aiia' ? 'active' : '' }}"
                                            href="/assessment/aiia">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">AIIA</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (auth()->user()->role == 'User')
                            <div class="menu-item menu-accordion {{ $isHav ? 'show' : '' }}"
                                data-kt-menu-expand="true" data-kt-menu-trigger="click" id="menu-hav">
                                <span class="menu-link {{ $isHav ? 'active' : '' }}">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">HAV</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'hav' ? 'active' : '' }}"
                                            href="/hav">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">HAV Quadran</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'hav/list' ? 'active' : '' }}"
                                            href="/hav/list">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">HAV List</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="menu-item menu-accordion {{ request()->is('hav*') ? 'show' : '' }}"
                                data-kt-menu-trigger="click" data-kt-menu-expand="true">
                                <span class="menu-link">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">HAV</span>
                                    <span class="menu-arrow"></span>
                                </span>

                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    {{-- HAV Quadran --}}
                                    <div class="menu-item menu-accordion {{ request()->is('hav/quadran*') ? 'show' : '' }}"
                                        data-kt-menu-trigger="click">
                                        <span class="menu-link">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">HAV Quadran</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link {{ request()->is('hav/aii') ? 'active' : '' }}"
                                                    href="/hav/aii">
                                                    <span class="menu-bullet"><span
                                                            class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">AII</span>
                                                </a>
                                            </div>
                                            <div class="menu-item">
                                                <a class="menu-link {{ request()->is('hav/aiia') ? 'active' : '' }}"
                                                    href="/hav/aiia">
                                                    <span class="menu-bullet"><span
                                                            class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">AIIA</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- HAV List --}}
                                    <div class="menu-item menu-accordion {{ request()->is('hav/list*') ? 'show' : '' }}"
                                        data-kt-menu-trigger="click">
                                        <span class="menu-link">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">HAV List</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link {{ request()->is('hav/list/aii') ? 'active' : '' }}"
                                                    href="/hav/list/aii">
                                                    <span class="menu-bullet"><span
                                                            class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">AII</span>
                                                </a>
                                            </div>
                                            <div class="menu-item">
                                                <a class="menu-link {{ request()->is('hav/list/aiia') ? 'active' : '' }}"
                                                    href="/hav/list/aiia">
                                                    <span class="menu-bullet"><span
                                                            class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">AIIA</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- IDP --}}
                        @if (auth()->user()->role == 'User')
                            <div class="menu-item">
                                <a class="menu-link {{ $currentPath === 'idp' ? 'active' : '' }}" href="/idp">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">IDP</span>
                                </a>
                            </div>
                        @else
                            <div class="menu-item menu-accordion {{ $isIdp ? 'show' : '' }}"
                                data-kt-menu-expand="true" data-kt-menu-trigger="click" id="menu-idp">
                                <span class="menu-link {{ $isIdp ? 'active' : '' }}">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">IDP</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'idp/aii' ? 'active' : '' }}"
                                            href="/idp/aii">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">AII</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'idp/aiia' ? 'active' : '' }}"
                                            href="/idp/aiia">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">AIIA</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- RTC --}}
                        @php
                            $normalized = strtolower(auth()->user()->employee->getNormalizedPosition());
                            $allowedPositions = ['gm', 'direktur'];
                        @endphp
                        @if (auth()->user()->role == 'User')
                            @if (in_array($normalized, $allowedPositions))
                                <div class="menu-item">
                                    <a class="menu-link {{ $currentPath === 'rtc' ? 'active' : '' }}" href="/rtc">
                                        {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                        <span class="menu-title ps-1">RTC</span>
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="menu-item menu-accordion {{ $isRtc ? 'show' : '' }}"
                                data-kt-menu-expand="true" data-kt-menu-trigger="click" id="menu-rtc">
                                <span class="menu-link {{ $isRtc ? 'active' : '' }}">
                                    {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                    <span class="menu-title ps-1">RTC</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'rtc/aii' ? 'active' : '' }}"
                                            href="/rtc/aii">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">AII</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link {{ $currentPath === 'rtc/aiia' ? 'active' : '' }}"
                                            href="/rtc/aiia">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">AIIA</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <style>
                            .blinking-dot {
                                animation: blink 1s infinite;
                            }

                            @keyframes blink {

                                0%,
                                100% {
                                    opacity: 1;
                                }

                                50% {
                                    opacity: 0;
                                }
                            }
                        </style>

                        <div class="menu-item">
                            <a class="menu-link {{ $currentPath === 'todolist' ? 'active' : '' }}" href="/todolist">
                                <span class="menu-title ps-1 position-relative d-inline-block"
                                    style="padding-right: 16px;">
                                    To Do List
                                    @if ($allIdpTasks->count() > 0 || $allHavTasks->count() > 0)
                                        <span
                                            class="blinking-dot position-absolute top-50 end-0 translate-middle-y bg-danger rounded-circle"
                                            style="width: 8px; height: 8px;"></span>
                                    @endif
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>


            <!--end:People Development Menu Accordion-->

            <!--end:People Development Menu Accordion-->

            <!--begin:Master Section-->
            <!--begin:Master Menu Accordion-->

            @if (auth()->user()->role == 'HRD')
                <div class="menu-item menu-accordion" data-kt-menu-expand="true" data-kt-menu-trigger="click"
                    id="menu-master">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="fas fa-cog"></i>
                        </span>
                        <span class="menu-title ps-1">Master</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg" style="overflow: hidden;">
                        <div class="menu-item menu-accordion" data-kt-menu-expand="true" data-kt-menu-trigger="click"
                            id="menu-employee-profile">
                            <span
                                class="menu-link {{ request()->is('master/employee/aii') || request()->is('master/employee/aiia') ? 'active' : '' }}">
                                {{-- <span class="menu-bullet"><span class="bullet bullet-dot"></span></span> --}}
                                <span class="menu-title ps-1">Employee</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion menu-active-bg">
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('master/employee/aii') ? 'active' : '' }}"
                                        href="{{ url('master/employee/aii') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('master/employee/aiia') ? 'active' : '' }}"
                                        href="{{ url('master/employee/aiia') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Submenu Accordion -->
                        {{-- <div class="menu-item menu-accordion" data-kt-menu-expand="true" data-kt-menu-trigger="click">
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
                    </div> --}}

                        <!-- Grade -->

                        <!-- Grade -->
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/grade') ? 'active' : '' }}"
                                href="/master/grade">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">Grade</span>
                            </a>
                        </div>


                        <!-- plant -->
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/plant') ? 'active' : '' }}"
                                href="/master/plant">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">Plant</span>
                            </a>
                        </div>


                        <!-- Division -->
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/division') ? 'active' : '' }}"
                                href="/master/division">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">Division</span>
                            </a>
                        </div>

                        <!-- Department -->
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/department') ? 'active' : '' }}"
                                href="/master/department">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">Department</span>
                            </a>
                        </div>

                        <!-- Section -->
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/section') ? 'active' : '' }}"
                                href="/master/section">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">Section</span>
                            </a>
                        </div>

                        {{-- sub section --}}
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/subSection') ? 'active' : '' }}"
                                href="/master/subSection">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">Sub Section</span>
                            </a>
                        </div>

                    </div>
                </div>
            @endif

            {{-- approve --}}
            @php
                $normalized = strtolower(auth()->user()->employee->getNormalizedPosition());
                $allowedPositions = ['manager', 'gm', 'direktur'];
            @endphp
            @if (in_array($normalized, $allowedPositions))
                <div class="menu-item menu-accordion" data-kt-menu-expand="true" data-kt-menu-trigger="click"
                    id="menu-approval">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="fas fa-check  "></i>
                        </span>
                        <span class="menu-title ps-1">Approval</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg" style="overflow: hidden;">

                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('approval/idp') ? 'active' : '' }}"
                                href="{{ route('idp.approval') }}">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">IDP</span>
                            </a>
                        </div>


                        <!-- plant -->
                        <a class="menu-link {{ request()->is('approval/hav') ? 'active' : '' }}"
                            href="{{ route('hav.approval') }}">
                            {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                            <span class="menu-title ps-1">HAV</span>
                        </a>


                        <!-- Division -->
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('approval/rtc') ? 'active' : '' }}" href="#">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">RTC</span>
                            </a>
                        </div>

                        <!-- Department -->


                    </div>
                </div>
            @elseif (auth()->user()->role == 'HRD')
                <div class="menu-item menu-accordion" data-kt-menu-expand="true" data-kt-menu-trigger="click"
                    id="menu-approval">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="fas fa-check"></i>
                        </span>
                        <span class="menu-title ps-1">Approval</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg" style="overflow: hidden;">

                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('master/grade') ? 'active' : '' }}" href="#">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">IDP</span>
                            </a>
                        </div>


                        <!-- plant -->
                        <a class="menu-link {{ request()->is('approval/list-approval-HAV') ? 'active' : '' }}"
                            href="{{ route('hav.approval') }}">
                            {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                            <span class="menu-title ps-1">HAV</span>
                        </a>



                        <!-- Division -->
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('approval/rtc') ? 'active' : '' }}" href="#">
                                {{-- <span class="menu-bullet"><i class="bullet bullet-dot"></i></span> --}}
                                <span class="menu-title ps-1">RTC</span>
                            </a>
                        </div>

                        <!-- Department -->


                    </div>
                </div>
            @endif

            {{-- <div class="menu-item menu-accordion" data-kt-menu-expand="true" data-kt-menu-trigger="click"
                id="menu-master">
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </span>
                    <span class="menu-title ps-1">Training</span>
                    <span class="menu-arrow"></span>
                </span>
                <!-- End: Menu Link -->

                <!-- Begin: Menu Sub -->
                <div class="menu-sub menu-sub-accordion menu-active-bg" style="overflow: hidden;">
                    <!-- Menu Item: FAQ Classic -->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('/emp_competency') ? 'active' : '' }}"
                            href="/emp_competency">
                            <span class="menu-bullet"><i class="bullet bullet-dot"></i></span>
                            <span class="menu-title ps-1">Section</span>
                        </a>
                    </div>

                    <!-- Menu Item: FAQ Extended -->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('/competencies') ? 'active' : '' }}"
                            href="/competencies">
                            <span class="menu-bullet"><i class="bullet bullet-dot"></i></span>
                            <span class="menu-title ps-1">Competency</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('/group_competency') ? 'active' : '' }}"
                            href="/group_competency">
                            <span class="menu-bullet"><i class="bullet bullet-dot"></i></span>
                            <span class="menu-title ps-1">Group Competency</span>
                        </a>
                    </div>
                </div>

            </div> --}}
            <!--end:Master Menu Accordion-->

            <!--end:Master Section-->
        </div>
        <!-- Sidebar User Panel -->
        <!--begin::User info sidebar bottom-->

        <!--end::User info sidebar bottom-->
        <!--end::sidebar menu-->
    </div>
    <div class="app-sidebar-user mt-auto px-3 pt-5 pb-5 border-top border-white border-opacity-25"
        style="position: sticky; bottom: 0; background-color: #1e1e2d;">
        <div class="d-flex align-items-center">
            <div class="symbol symbol-40px">
                <img src="{{ auth()->user()->employee && auth()->user()->employee->photo
                    ? asset('storage/' . auth()->user()->employee->photo)
                    : asset('assets/media/avatars/user.jpg') }}"
                    class="rounded-3" alt="user" style="width: 40px; height: 40px; object-fit: cover;" />
            </div>
            <div class="ms-3">
                <div class="fw-bold text-white">{{ auth()->user()->name }}</div>
                <div class="text-muted fs-8">{{ auth()->user()->employee->position ?? '-' }}</div>
            </div>
        </div>
    </div>

</div>
<!--end::Sidebar-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const menuAccordions = document.querySelectorAll(".menu-item.menu-accordion");

        // Restore dropdown states
        menuAccordions.forEach((menu) => {
            const id = menu.id;
            const isOpen = localStorage.getItem(id);

            if (isOpen === "true") {
                menu.classList.add("hover", "show");
            }
        });

        // Save states on toggle
        menuAccordions.forEach((menu) => {
            const trigger = menu.querySelector(".menu-link");
            const id = menu.id;

            if (!id) return;

            trigger.addEventListener("click", function() {
                const isExpanded = menu.classList.contains("show");
                localStorage.setItem(id, !isExpanded); // Save the toggled state to localStorage
            });
        });
        const currentPath = window.location.pathname;

        // Temukan semua link di sidebar
        const links = document.querySelectorAll("a.menu-link");

        links.forEach(link => {
            const linkPath = link.getAttribute("href");

            // Jika path sama persis, tandai link dan parent accordion-nya
            if (currentPath === linkPath) {
                link.classList.add("active");

                // Tambahkan class 'show' ke accordion induknya (menu-item)
                let parent = link.closest(".menu-item.menu-accordion");
                if (parent) {
                    parent.classList.add("show");
                }
            }
        });
    });
</script>
