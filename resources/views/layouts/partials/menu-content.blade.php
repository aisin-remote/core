<nav class="menu" id="app_menu">
    @php
        $user = auth()->user();
        $employee = $user->employee;
        $position = $employee->position ?? null;

        $role = strtoupper($user->role ?? '');
        $isHRD = $role === 'HRD';
        $isUserRole = $role === 'USER' && !in_array($position, ['President', 'VPD']);
        $isHRDorTop = $isHRD || in_array($position, ['President', 'VPD']);
    @endphp

    <!-- ===== PEOPLE DEVELOPMENT ===== -->
    <div class="menu-item menu-accordion" id="menu-people-development" data-kt-menu-trigger="click"
        data-kt-menu-expand="true">
        <span class="menu-link">
            <span class="menu-icon"><i class="fas fa-users-cog"></i></span>
            <span class="menu-title">People Development</span>
            <span class="menu-arrow"></span>
        </span>

        {{-- NORMAL (ACCORDION) --}}
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            @can('view-dashboard')
                {{-- Dashboard (Dashboard) --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                        <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>
            @endcan
            {{-- To Do List --}}
            <div class="menu-item">
                <a class="menu-link {{ $currentPath === 'todolist' ? 'active' : '' }}" href="/todolist">
                    <span class="menu-icon"><i class="fas fa-clipboard-check"></i></span>
                    <span class="menu-title position-relative d-inline-block" style="padding-right:16px;">
                        To Do List
                        @if ($allIdpTasks->count() > 0 || $allHavTasks->count() > 0 || $allRtcTasks->count() > 0)
                            <span
                                class="blinking-dot position-absolute top-50 end-0 translate-middle-y bg-danger rounded-circle"
                                style="width:8px; height:8px;"></span>
                        @endif
                    </span>
                </a>
            </div>

            {{-- Employee Profile --}}
            @if ($isUserRole)
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('employee') ? 'active' : '' }}" href="/employee">
                        <span class="menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="menu-title">Employee Profile</span>
                    </a>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion {{ request()->is('employee*') ? 'show' : '' }}"
                    id="menu-employee-profile" data-kt-menu-trigger="click" data-kt-menu-expand="true">
                    <span class="menu-link {{ request()->is('employee*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="menu-title">Employee Profile</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('employee/aii') ? 'active' : '' }}"
                                href="/employee/aii">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">AII</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('employee/aiia') ? 'active' : '' }}"
                                href="/employee/aiia">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">AIIA</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Assessment --}}
            @if ($isUserRole)
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('assessment') ? 'active' : '' }}" href="/assessment">
                        <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="menu-title">Assessment</span>
                    </a>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion {{ request()->is('assessment*') ? 'show' : '' }}"
                    id="menu-assessment" data-kt-menu-trigger="click" data-kt-menu-expand="true">
                    <span class="menu-link {{ request()->is('assessment*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="menu-title">Assessment</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('assessment/aii') ? 'active' : '' }}"
                                href="/assessment/aii">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">AII</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('assessment/aiia') ? 'active' : '' }}"
                                href="/assessment/aiia">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">AIIA</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- HAV --}}
            @if ($isUserRole)
                <div class="menu-item menu-accordion {{ $isHav ? 'show' : '' }}" id="menu-hav"
                    data-kt-menu-trigger="click" data-kt-menu-expand="true">
                    <span class="menu-link {{ $isHav ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                        <span class="menu-title">HAV</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item">
                            <a class="menu-link {{ $currentPath === 'hav' ? 'active' : '' }}" href="/hav">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">HAV Quadran</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ $currentPath === 'hav/assign' ? 'active' : '' }}"
                                href="/hav/assign">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">HAV Assign</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ $currentPath === 'hav/list' ? 'active' : '' }}" href="/hav/list">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">HAV List</span>
                            </a>
                        </div>
                    </div>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion {{ request()->is('hav*') ? 'show' : '' }}" id="menu-hav-admin"
                    data-kt-menu-trigger="click" data-kt-menu-expand="true">
                    <span class="menu-link {{ request()->is('hav*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                        <span class="menu-title">HAV</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
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
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('hav/aiia') ? 'active' : '' }}"
                                        href="/hav/aiia">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                        </div>

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
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('hav/list/aiia') ? 'active' : '' }}"
                                        href="/hav/list/aiia">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ICP --}}
            @if ($isUserRole)
                <div class="menu-item menu-accordion {{ $isIcp ? 'show' : '' }}" id="menu-icp"
                    data-kt-menu-trigger="click" data-kt-menu-expand="true">
                    <span class="menu-link {{ $isIcp ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-project-diagram"></i></span>
                        <span class="menu-title">ICP</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item">
                            <a class="menu-link {{ $currentPath === 'icp' ? 'active' : '' }}"
                                href="{{ route('icp.assign') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">ICP Assign</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ $currentPath === 'icp/list' ? 'active' : '' }}"
                                href="{{ route('icp.list', ['company' => null]) }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">ICP List</span>
                            </a>
                        </div>
                    </div>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion {{ $isIcp ? 'show' : '' }}" id="menu-icp-admin"
                    data-kt-menu-trigger="click" data-kt-menu-expand="true">
                    <span class="menu-link {{ $isIcp ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-project-diagram"></i></span>
                        <span class="menu-title">ICP</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item menu-accordion {{ request()->is('icp/list/*') ? 'show' : '' }}"
                            data-kt-menu-trigger="click">
                            <span class="menu-link">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">ICP List</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('icp/list/aii') ? 'active' : '' }}"
                                        href="/icp/list/aii">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('icp/list/aiia') ? 'active' : '' }}"
                                        href="/icp/list/aiia">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- IDP --}}
            @if ($isUserRole)
                <div class="menu-item menu-accordion {{ $isIdp ? 'show' : '' }}" id="menu-idp"
                    data-kt-menu-trigger="click" data-kt-menu-expand="true">
                    <span class="menu-link {{ $isIdp ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-bullseye"></i></span>
                        <span class="menu-title">IDP</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item">
                            <a class="menu-link {{ $currentPath === 'idp' ? 'active' : '' }}" href="/idp">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">IDP Assign</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ $currentPath === 'idp/list' ? 'active' : '' }}"
                                href="{{ route('idp.list', ['company' => null]) }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">IDP List</span>
                            </a>
                        </div>
                    </div>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion {{ $isIdp ? 'show' : '' }}" id="menu-idp-admin"
                    data-kt-menu-trigger="click" data-kt-menu-expand="true">
                    <span class="menu-link {{ $isIdp ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-bullseye"></i></span>
                        <span class="menu-title">IDP</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item menu-accordion {{ request()->is('idp/list/*') ? 'show' : '' }}"
                            data-kt-menu-trigger="click">
                            <span class="menu-link">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">IDP List</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('idp/list/aii') ? 'active' : '' }}"
                                        href="/idp/list/aii">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AII</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('idp/list/aiia') ? 'active' : '' }}"
                                        href="/idp/list/aiia">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">AIIA</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($isPrsdn)
                        @php $isManageActive = request()->routeIs('idp.manage.*'); @endphp
                        <div class="menu-sub menu-sub-accordion menu-active-bg">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('idp.manage.all') ? 'active' : '' }}"
                                    href="{{ route('idp.manage.all') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Manage IDP</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- RTC --}}
            @php
                $user = auth()->user();
                $employee = $user->employee;
                $position = $employee->position ?? '';

                // Normalisasi role/posisi
                $role = strtoupper($user->role ?? '');
                $posRaw =
                    $employee && method_exists($employee, 'getNormalizedPosition')
                        ? (string) $employee->getNormalizedPosition()
                        : (string) $position;
                $posNorm = strtolower(trim($posRaw));

                $isHRD = $role === 'HRD';
                $isTop2 = in_array($posNorm, ['president', 'vpd'], true);
                $isDir = in_array($posNorm, ['direktur', 'director'], true);
                $isGM = in_array($posNorm, ['gm', 'act gm'], true);

                // Siapa saja boleh lihat RTC
                $canSeeRtc = $isHRD || $isTop2 || $isDir || $isGM;

                // Tentukan level + id yang dikirim ke route
                $level = 'division';
                $id = null;

                if ($isHRD || $isTop2) {
                    $level = 'company';
                    // HRD/Top2 bisa pegang dua company; kalau mau satu default, pakai company login
                    $id = strtoupper((string) $employee->company_name); // fallback AII
                } elseif ($isDir) {
                    $level = 'plant';
                    $id = optional($employee->plant)->id; // aman kalau null
                } elseif ($isGM) {
                    $level = 'division';
                    $id = optional($employee->division)->id;
                }

                // Jika id belum ada, biarkan null—controller akan fallback aman

            @endphp

            @if ($canSeeRtc)
                <div class="menu-item">
                    <a class="menu-link {{ $currentPath === 'rtc' ? 'active' : '' }}"
                        href="{{ route('rtc.list', ['level' => $level, 'id' => $id]) }}">
                        <span class="menu-icon"><i class="fas fa-tasks"></i></span>
                        <span class="menu-title">RTC</span>
                    </a>
                </div>
            @endif

            {{-- IPP & IPA --}}
            @php
                $userCompany = strtolower(optional(optional(auth()->user())->employee)->company_name ?? 'aii');
            @endphp
            @if ($isUserRole)
                <div class="disabled menu-item menu-accordion {{ request()->routeIs('ipp.*') || request()->is('ipa*') ? 'show' : '' }}"
                    id="menu-ipp" data-kt-menu-trigger="click" data-kt-menu-expand="true">

                    <span
                        class="menu-link {{ request()->routeIs('ipp.*') || request()->is('ipa*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="menu-title">IPP & IPA</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item menu-accordion {{ request()->routeIs('ipp.*') ? 'show' : '' }}">
                            <span class="menu-link {{ request()->routeIs('ipp.*') ? 'active' : '' }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">IPP</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a href="{{ route('ipp.index') }}"
                                        class="menu-link {{ request()->routeIs('ipp.index') ? 'active' : '' }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">IPP Assign</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a href="{{ route('ipp.list') }}"
                                        class="menu-link {{ request()->routeIs('ipp.list') ? 'active' : '' }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">IPP List</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div
                            class="menu-item menu-accordion {{ request()->routeIs('ipa.*') || request()->is('ipa*') ? 'show' : '' }}">
                            <span
                                class="menu-link {{ request()->routeIs('ipa.*') || request()->is('ipa*') ? 'active' : '' }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">IPA</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a href="{{ route('ipa.index') }}"
                                        class="menu-link {{ request()->routeIs('ipa.index') ? 'active' : '' }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">IPA Assign</span>
                                    </a>
                                </div>
                                <div class="disabled menu-item">
                                    @if (Route::has('ipa.list'))
                                        <a href="{{ route('ipa.list', ['company' => $userCompany]) }}"
                                            class="menu-link {{ request()->routeIs('ipa.list') ? 'active' : '' }}">
                                        @else
                                            <a href="{{ url('/ipa/list/' . $userCompany) }}"
                                                class="menu-link {{ request()->is('ipa/list/*') ? 'active' : '' }}">
                                    @endif
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">IPA List</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @elseif ($isHRDorTop)
                <div class="disabled menu-item menu-accordion
        {{ request()->routeIs('ipp.list') || request()->routeIs('ipa.list') ? 'show' : '' }}"
                    id="menu-ipp" data-kt-menu-trigger="click" data-kt-menu-expand="true">

                    <span
                        class="menu-link
            {{ request()->routeIs('ipp.list') || request()->routeIs('ipa.list') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="menu-title">IPP & IPA</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion menu-active-bg">

                        <div class="menu-item menu-accordion {{ request()->routeIs('ipp.list') ? 'show' : '' }}">
                            <span class="menu-link {{ request()->routeIs('ipp.list') ? 'active' : '' }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">IPP</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a href="{{ route('ipp.list', ['company' => 'aii']) }}"
                                        class="menu-link {{ request()->routeIs('ipp.list') && request()->route('company') === 'aii' ? 'active' : '' }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">List AII</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a href="{{ route('ipp.list', ['company' => 'aiia']) }}"
                                        class="menu-link {{ request()->routeIs('ipp.list') && request()->route('company') === 'aiia' ? 'active' : '' }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">List AIIA</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div
                            class="disabled menu-item menu-accordion {{ request()->routeIs('ipa.list') || request()->is('ipa/list/*') ? 'show' : '' }}">
                            <span
                                class="menu-link {{ request()->routeIs('ipa.list') || request()->is('ipa/list/*') ? 'active' : '' }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">IPA</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    @if (Route::has('ipa.list'))
                                        <a href="{{ route('ipa.list', ['company' => 'aii']) }}"
                                            class="menu-link {{ request()->routeIs('ipa.list') && request()->route('company') === 'aii' ? 'active' : '' }}">
                                        @else
                                            <a href="{{ url('/ipa/list/aii') }}"
                                                class="menu-link {{ request()->is('ipa/list/aii') ? 'active' : '' }}">
                                    @endif
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">List AII</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    @if (Route::has('ipa.list'))
                                        <a href="{{ route('ipa.list', ['company' => 'aiia']) }}"
                                            class="menu-link {{ request()->routeIs('ipa.list') && request()->route('company') === 'aiia' ? 'active' : '' }}">
                                        @else
                                            <a href="{{ url('/ipa/list/aiia') }}"
                                                class="menu-link {{ request()->is('ipa/list/aiia') ? 'active' : '' }}">
                                    @endif
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">List AIIA</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endif

        </div>

        {{-- FLYOUT (DROPDOWN) untuk mode minimized --}}
        <div class="menu-sub menu-sub-dropdown">
            {{-- Item-item penting People Development ditampilkan ringkas; nested tetap didukung --}}
            @can('view-dashboard')
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                        <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>
            @endcan
            <div class="menu-item">
                <a class="menu-link {{ $currentPath === 'todolist' ? 'active' : '' }}" href="/todolist">
                    <span class="menu-icon"><i class="fas fa-clipboard-check"></i></span>
                    <span class="menu-title">To Do List</span>
                </a>
            </div>

            {{-- Employee Profile (flyout) --}}
            @if ($isUserRole)
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('employee') ? 'active' : '' }}" href="/employee">
                        <span class="menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="menu-title">Employee Profile</span>
                    </a>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="menu-title">Employee Profile</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('employee/aii') ? 'active' : '' }}"
                                href="/employee/aii"><span class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('employee/aiia') ? 'active' : '' }}"
                                href="/employee/aiia"><span class="menu-title">AIIA</span></a></div>
                    </div>
                </div>
            @endif

            {{-- Assessment (flyout) --}}
            @if ($isUserRole)
                <div class="menu-item">
                    <a class="menu-link {{ request()->is('assessment') ? 'active' : '' }}" href="/assessment">
                        <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="menu-title">Assessment</span>
                    </a>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="menu-title">Assessment</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('assessment/aii') ? 'active' : '' }}"
                                href="/assessment/aii"><span class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('assessment/aiia') ? 'active' : '' }}"
                                href="/assessment/aiia"><span class="menu-title">AIIA</span></a></div>
                    </div>
                </div>
            @endif

            {{-- HAV (flyout) --}}
            @if ($isUserRole)
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                        <span class="menu-title">HAV</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a class="menu-link {{ $currentPath === 'hav' ? 'active' : '' }}"
                                href="/hav"><span class="menu-title">HAV Quadran</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ $currentPath === 'hav/assign' ? 'active' : '' }}"
                                href="/hav/assign"><span class="menu-title">HAV Assign</span></a></div>
                        <div class="menu-item"><a class="menu-link {{ $currentPath === 'hav/list' ? 'active' : '' }}"
                                href="/hav/list"><span class="menu-title">HAV List</span></a></div>
                    </div>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                        <span class="menu-title">HAV</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                            <span class="menu-link"><span class="menu-title">HAV Quadran</span><span
                                    class="menu-arrow"></span></span>
                            <div class="menu-sub menu-sub-dropdown">
                                <div class="menu-item"><a
                                        class="menu-link {{ request()->is('hav/aii') ? 'active' : '' }}"
                                        href="/hav/aii"><span class="menu-title">AII</span></a></div>
                                <div class="menu-item"><a
                                        class="menu-link {{ request()->is('hav/aiia') ? 'active' : '' }}"
                                        href="/hav/aiia"><span class="menu-title">AIIA</span></a></div>
                            </div>
                        </div>
                        <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                            <span class="menu-link"><span class="menu-title">HAV List</span><span
                                    class="menu-arrow"></span></span>
                            <div class="menu-sub menu-sub-dropdown">
                                <div class="menu-item"><a
                                        class="menu-link {{ request()->is('hav/list/aii') ? 'active' : '' }}"
                                        href="/hav/list/aii"><span class="menu-title">AII</span></a></div>
                                <div class="menu-item"><a
                                        class="menu-link {{ request()->is('hav/list/aiia') ? 'active' : '' }}"
                                        href="/hav/list/aiia"><span class="menu-title">AIIA</span></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ICP (flyout) --}}
            @if ($isUserRole)
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-icon"><i
                                class="fas fa-project-diagram"></i></span><span class="menu-title">ICP</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a class="menu-link {{ $currentPath === 'icp' ? 'active' : '' }}"
                                href="{{ route('icp.assign') }}"><span class="menu-title">ICP Assign</span></a>
                        </div>
                        <div class="menu-item"><a
                                class="menu-link {{ $currentPath === 'icp/list' ? 'active' : '' }}"
                                href="{{ route('icp.list', ['company' => null]) }}"><span class="menu-title">ICP
                                    List</span></a></div>
                    </div>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-icon"><i
                                class="fas fa-project-diagram"></i></span><span class="menu-title">ICP</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                            <span class="menu-link"><span class="menu-title">ICP List</span><span
                                    class="menu-arrow"></span></span>
                            <div class="menu-sub menu-sub-dropdown">
                                <div class="menu-item"><a
                                        class="menu-link {{ request()->is('icp/list/aii') ? 'active' : '' }}"
                                        href="/icp/list/aii"><span class="menu-title">AII</span></a></div>
                                <div class="menu-item"><a
                                        class="menu-link {{ request()->is('icp/list/aiia') ? 'active' : '' }}"
                                        href="/icp/list/aiia"><span class="menu-title">AIIA</span></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- IDP (flyout) --}}
            @if ($isUserRole)
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-icon"><i class="fas fa-bullseye"></i></span><span
                            class="menu-title">IDP</span><span class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a class="menu-link {{ $currentPath === 'idp' ? 'active' : '' }}"
                                href="/idp"><span class="menu-title">IDP Assign</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ $currentPath === 'idp/list' ? 'active' : '' }}"
                                href="{{ route('idp.list', ['company' => null]) }}"><span class="menu-title">IDP
                                    List</span></a></div>
                    </div>
                </div>
            @elseif ($isHRDorTop)
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-icon"><i class="fas fa-bullseye"></i></span><span
                            class="menu-title">IDP</span><span class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                            <span class="menu-link"><span class="menu-title">IDP List</span><span
                                    class="menu-arrow"></span></span>
                            <div class="menu-sub menu-sub-dropdown">
                                <div class="menu-item"><a
                                        class="menu-link {{ request()->is('idp/list/aii') ? 'active' : '' }}"
                                        href="/idp/list/aii"><span class="menu-title">AII</span></a></div>
                                <div class="menu-item"><a
                                        class="menu-link {{ request()->is('idp/list/aiia') ? 'active' : '' }}"
                                        href="/idp/list/aiia"><span class="menu-title">AIIA</span></a></div>
                            </div>
                        </div>
                        @if ($isPrsdn)
                            <div class="menu-item"><a
                                    class="menu-link {{ request()->routeIs('idp.manage.all') ? 'active' : '' }}"
                                    href="{{ route('idp.manage.all') }}"><span class="menu-title">Manage
                                        IDP</span></a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- RTC (flyout) --}}
            @if ($canSeeRtc)
                <div class="disabled menu-item">
                    <a class="menu-link {{ $currentPath === 'rtc' ? 'active' : '' }}" href="/rtc">
                        <span class="menu-icon"><i class="fas fa-tasks"></i></span>
                        <span class="menu-title">RTC</span>
                    </a>
                </div>
            @elseif ($isHRDorTop)
                <div class="disabled menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-icon"><i class="fas fa-tasks"></i></span><span
                            class="menu-title">RTC</span><span class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a class="menu-link {{ $currentPath === 'rtc/aii' ? 'active' : '' }}"
                                href="/rtc/aii"><span class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ $currentPath === 'rtc/aiia' ? 'active' : '' }}"
                                href="/rtc/aiia"><span class="menu-title">AIIA</span></a></div>
                    </div>
                </div>
            @endif

            {{-- ==== IPP & IPA (flyout) ==== --}}
            @if ($isUserRole)
                <div class="disabled menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="menu-title">IPP & IPA</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('ipp.*') ? 'active' : '' }}"
                                href="{{ route('ipp.index') }}"><span class="menu-title">IPP</span></a>
                        </div>
                        <div class="menu-item">
                            @if (Route::has('ipa.index'))
                                <a class="menu-link {{ request()->routeIs('ipa.*') ? 'active' : '' }}"
                                    href="{{ route('ipa.index') }}"><span class="menu-title">IPA</span></a>
                            @else
                                <a class="menu-link {{ request()->is('ipa*') ? 'active' : '' }}"
                                    href="{{ url('/ipa') }}"><span class="menu-title">IPA</span></a>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif ($isHRDorTop)
                <div class="disabled menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="menu-title">IPP & IPA</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-dropdown">

                        {{-- IPP --}}
                        <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                            <span class="menu-link">
                                <span class="menu-title">IPP</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-dropdown">
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('ipp.list') && request()->route('company') === 'aii' ? 'active' : '' }}"
                                        href="{{ route('ipp.list', ['company' => 'aii']) }}"><span
                                            class="menu-title">List AII</span></a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('ipp.list') && request()->route('company') === 'aiia' ? 'active' : '' }}"
                                        href="{{ route('ipp.list', ['company' => 'aiia']) }}"><span
                                            class="menu-title">List AIIA</span></a>
                                </div>
                            </div>
                        </div>

                        {{-- IPA --}}
                        <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                            <span class="menu-link">
                                <span class="menu-title">IPA</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-dropdown">
                                <div class="menu-item">
                                    @if (Route::has('ipa.list'))
                                        <a class="menu-link {{ request()->routeIs('ipa.list') && request()->route('company') === 'aii' ? 'active' : '' }}"
                                            href="{{ route('ipa.list', ['company' => 'aii']) }}"><span
                                                class="menu-title">List AII</span></a>
                                    @else
                                        <a class="menu-link {{ request()->is('ipa/list/aii') ? 'active' : '' }}"
                                            href="{{ url('/ipa/list/aii') }}"><span class="menu-title">List
                                                AII</span></a>
                                    @endif
                                </div>
                                <div class="menu-item">
                                    @if (Route::has('ipa.list'))
                                        <a class="menu-link {{ request()->routeIs('ipa.list') && request()->route('company') === 'aiia' ? 'active' : '' }}"
                                            href="{{ route('ipa.list', ['company' => 'aiia']) }}"><span
                                                class="menu-title">List AIIA</span></a>
                                    @else
                                        <a class="menu-link {{ request()->is('ipa/list/aiia') ? 'active' : '' }}"
                                            href="{{ url('/ipa/list/aiia') }}"><span class="menu-title">List
                                                AIIA</span></a>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endif

        </div>
    </div>
    <!-- ===== END PEOPLE DEVELOPMENT ===== -->

    <!-- ===== TRAINING (HRD only) ===== -->
    @if ($isHRD)
        <div class="menu-item menu-accordion" id="menu-training" data-kt-menu-trigger="click"
            data-kt-menu-expand="true">
            <span class="menu-link">
                <span class="menu-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                <span class="menu-title">Training</span>
                <span class="menu-arrow"></span>
            </span>

            {{-- NORMAL --}}
            <div class="menu-sub menu-sub-accordion menu-active-bg">
                @if (auth()->user()->role == 'User')
                    <div class="menu-item">
                        <a class="menu-link {{ $currentPath === 'employeeCompetencies' ? 'active' : '' }}"
                            href="/employeeCompetencies">
                            <span class="menu-title">Employee Competency</span>
                        </a>
                    </div>
                @else
                    <div class="menu-item menu-accordion {{ $isEmployeeCompetencies ? 'show' : '' }}"
                        id="menu-emp-competency" data-kt-menu-trigger="click" data-kt-menu-expand="true">
                        <span class="menu-link {{ $isEmployeeCompetencies ? 'active' : '' }}">
                            <span class="menu-title">Employee Competency</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion menu-active-bg">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->is('employeeCompetencies/aii') ? 'active' : '' }}"
                                    href="{{ route('employeeCompetencies.index', ['company' => 'aii']) }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AII</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->is('employeeCompetencies/aiia') ? 'active' : '' }}"
                                    href="{{ route('employeeCompetencies.index', ['company' => 'aiia']) }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">AIIA</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- FLYOUT --}}
            <div class="menu-sub menu-sub-dropdown">
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-title">Employee Competency</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('employeeCompetencies/aii') ? 'active' : '' }}"
                                href="{{ route('employeeCompetencies.index', ['company' => 'aii']) }}"><span
                                    class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('employeeCompetencies/aiia') ? 'active' : '' }}"
                                href="{{ route('employeeCompetencies.index', ['company' => 'aiia']) }}"><span
                                    class="menu-title">AIIA</span></a></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- ===== END TRAINING ===== -->


    <!-- ===== MASTER (HRD only) ===== -->
    @if ($isHRD)
        <div class="menu-item menu-accordion" id="menu-master" data-kt-menu-trigger="click"
            data-kt-menu-expand="true">
            <span class="menu-link">
                <span class="menu-icon"><i class="fas fa-cog"></i></span>
                <span class="menu-title">Master</span>
                <span class="menu-arrow"></span>
            </span>

            {{-- NORMAL --}}
            <div class="menu-sub menu-sub-accordion menu-active-bg">
                <div class="menu-item menu-accordion {{ request()->is('master/employee/*') ? 'show' : '' }}"
                    id="menu-employee-master" data-kt-menu-trigger="click">
                    <span class="menu-link {{ request()->is('master/employee/*') ? 'active' : '' }}">
                        <span class="menu-title">Employee</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/employee/aii') ? 'active' : '' }}"
                                href="{{ url('master/employee/aii') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/employee/aiia') ? 'active' : '' }}"
                                href="{{ url('master/employee/aiia') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AIIA</span></a></div>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->is('master/plant/*') ? 'show' : '' }}"
                    id="menu-plant-master" data-kt-menu-trigger="click">
                    <span class="menu-link {{ request()->is('master/plant/*') ? 'active' : '' }}">
                        <span class="menu-title">Plant</span><span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/plant/aii') ? 'active' : '' }}"
                                href="{{ url('master/plant/aii') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/plant/aiia') ? 'active' : '' }}"
                                href="{{ url('master/plant/aiia') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AIIA</span></a></div>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->is('master/division/*') ? 'show' : '' }}"
                    id="menu-division-master" data-kt-menu-trigger="click">
                    <span class="menu-link {{ request()->is('master/division/*') ? 'active' : '' }}"><span
                            class="menu-title">Division</span><span class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/division/aii') ? 'active' : '' }}"
                                href="{{ url('master/division/aii') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/division/aiia') ? 'active' : '' }}"
                                href="{{ url('master/division/aiia') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AIIA</span></a></div>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->is('master/department/*') ? 'show' : '' }}"
                    id="menu-dept-master" data-kt-menu-trigger="click">
                    <span class="menu-link {{ request()->is('master/department/*') ? 'active' : '' }}"><span
                            class="menu-title">Department</span><span class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/department/aii') ? 'active' : '' }}"
                                href="{{ url('master/department/aii') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/department/aiia') ? 'active' : '' }}"
                                href="{{ url('master/department/aiia') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AIIA</span></a></div>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->is('master/section/*') ? 'show' : '' }}"
                    id="menu-section-master" data-kt-menu-trigger="click">
                    <span class="menu-link {{ request()->is('master/section/*') ? 'active' : '' }}"><span
                            class="menu-title">Section</span><span class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/section/aii') ? 'active' : '' }}"
                                href="{{ url('master/section/aii') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/section/aiia') ? 'active' : '' }}"
                                href="{{ url('master/section/aiia') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AIIA</span></a></div>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->is('master/subSection/*') ? 'show' : '' }}"
                    id="menu-subsection-master" data-kt-menu-trigger="click">
                    <span class="menu-link {{ request()->is('master/subSection/*') ? 'active' : '' }}"><span
                            class="menu-title">Sub Section</span><span class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/subSection/aii') ? 'active' : '' }}"
                                href="{{ url('master/subSection/aii') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/subSection/aiia') ? 'active' : '' }}"
                                href="{{ url('master/subSection/aiia') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AIIA</span></a></div>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->is('master/users/*') ? 'show' : '' }}"
                    id="menu-users-master" data-kt-menu-trigger="click">
                    <span class="menu-link {{ request()->is('master/users/*') ? 'active' : '' }}"><span
                            class="menu-title">Users</span><span class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/users/aii') ? 'active' : '' }}"
                                href="{{ url('master/users/aii') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AII</span></a></div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/users/aiia') ? 'active' : '' }}"
                                href="{{ url('master/users/aiia') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span
                                    class="menu-title">AIIA</span></a></div>
                    </div>
                </div>
            </div>

            {{-- FLYOUT --}}
            <div class="menu-sub menu-sub-dropdown">
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-title">Employee</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/employee/aii') ? 'active' : '' }}"
                                href="{{ url('master/employee/aii') }}"><span class="menu-title">AII</span></a>
                        </div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/employee/aiia') ? 'active' : '' }}"
                                href="{{ url('master/employee/aiia') }}"><span class="menu-title">AIIA</span></a>
                        </div>
                    </div>
                </div>
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-title">Plant</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/plant/aii') ? 'active' : '' }}"
                                href="{{ url('master/plant/aii') }}"><span class="menu-title">AII</span></a>
                        </div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/plant/aiia') ? 'active' : '' }}"
                                href="{{ url('master/plant/aiia') }}"><span class="menu-title">AIIA</span></a>
                        </div>
                    </div>
                </div>
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-title">Division</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/division/aii') ? 'active' : '' }}"
                                href="{{ url('master/division/aii') }}"><span class="menu-title">AII</span></a>
                        </div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/division/aiia') ? 'active' : '' }}"
                                href="{{ url('master/division/aiia') }}"><span class="menu-title">AIIA</span></a>
                        </div>
                    </div>
                </div>
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-title">Department</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/department/aii') ? 'active' : '' }}"
                                href="{{ url('master/department/aii') }}"><span class="menu-title">AII</span></a>
                        </div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/department/aiia') ? 'active' : '' }}"
                                href="{{ url('master/department/aiia') }}"><span class="menu-title">AIIA</span></a>
                        </div>
                    </div>
                </div>
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-title">Section</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/section/aii') ? 'active' : '' }}"
                                href="{{ url('master/section/aii') }}"><span class="menu-title">AII</span></a>
                        </div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/section/aiia') ? 'active' : '' }}"
                                href="{{ url('master/section/aiia') }}"><span class="menu-title">AIIA</span></a>
                        </div>
                    </div>
                </div>
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-title">Sub Section</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/subSection/aii') ? 'active' : '' }}"
                                href="{{ url('master/subSection/aii') }}"><span class="menu-title">AII</span></a>
                        </div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/subSection/aiia') ? 'active' : '' }}"
                                href="{{ url('master/subSection/aiia') }}"><span class="menu-title">AIIA</span></a>
                        </div>
                    </div>
                </div>
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link"><span class="menu-title">Users</span><span
                            class="menu-arrow"></span></span>
                    <div class="menu-sub menu-sub-dropdown">
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/users/aii') ? 'active' : '' }}"
                                href="{{ url('master/users/aii') }}"><span class="menu-title">AII</span></a>
                        </div>
                        <div class="menu-item"><a
                                class="menu-link {{ request()->is('master/users/aiia') ? 'active' : '' }}"
                                href="{{ url('master/users/aiia') }}"><span class="menu-title">AIIA</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- ===== END MASTER ===== -->

    <!-- ===== APPROVAL (All eligible roles) ===== -->
    <div class="menu-item menu-accordion" id="menu-approval" data-kt-menu-trigger="click"
        data-kt-menu-expand="true">
        <span class="menu-link">
            <span class="menu-icon"><i class="fas fa-check"></i></span>
            <span class="menu-title">Approval</span>
            <span class="menu-arrow"></span>
        </span>

        {{-- NORMAL --}}
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            <div class="menu-item">
                <a class="menu-link {{ request()->is('approval/idp') ? 'active' : '' }}"
                    href="{{ route('idp.approval') }}">
                    <span class="menu-title">IDP</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ request()->is('approval/hav') ? 'active' : '' }}"
                    href="{{ route('hav.approval') }}">
                    <span class="menu-title">HAV</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ request()->is('approval/rtc') ? 'active' : '' }}"
                    href="{{ route('rtc.approval') }}">
                    <span class="menu-title">RTC</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ request()->is('approval/icp') ? 'active' : '' }}"
                    href="{{ route('icp.approval') }}">
                    <span class="menu-title">ICP</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ request()->is('approval/ipp') ? 'active' : '' }}"
                    href="{{ route('ipp.approval') }}">
                    <span class="menu-title">IPP</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ request()->is('approval/ipa') ? 'active' : '' }}"
                    href="{{ route('ipa.approval') }}">
                    <span class="menu-title">IPA</span>
                </a>
            </div>
        </div>

        {{-- FLYOUT --}}
        <div class="menu-sub menu-sub-dropdown">
            <div class="menu-item"><a class="menu-link {{ request()->is('approval/idp') ? 'active' : '' }}"
                    href="{{ route('idp.approval') }}"><span class="menu-title">IDP</span></a></div>
            <div class="menu-item"><a class="menu-link {{ request()->is('approval/hav') ? 'active' : '' }}"
                    href="{{ route('hav.approval') }}"><span class="menu-title">HAV</span></a></div>
            <div class="menu-item"><a class="menu-link {{ request()->is('approval/rtc') ? 'active' : '' }}"
                    href="{{ route('rtc.approval') }}"><span class="menu-title">RTC</span></a></div>
            <div class="menu-item"><a class="menu-link {{ request()->is('approval/icp') ? 'active' : '' }}"
                    href="{{ route('icp.approval') }}"><span class="menu-title">ICP</span></a></div>
            <div class="menu-item"><a class="menu-link {{ request()->is('approval/ipp') ? 'active' : '' }}"
                    href="{{ route('ipp.approval') }}"><span class="menu-title">ICP</span></a></div>
            <div class="menu-item"><a class="menu-link {{ request()->is('approval/ipa') ? 'active' : '' }}"
                    href="{{ route('ipa.approval') }}"><span class="menu-title">ICP</span></a></div>
        </div>
    </div>
    <!-- ===== END APPROVAL ===== -->
</nav>
