<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <div>
            <!-- Gantilah dengan asset() untuk mendapatkan path yang benar -->
            <img alt="Logo" src="{{ asset('assets/media/logos/default.svg') }}" class="h-25px app-sidebar-logo-default" />
            <img alt="Logo" src="{{ asset('assets/media/logos/default-small.svg') }}" class="h-20px app-sidebar-logo-minimize" />
        </a>
        <!--end::Logo image-->

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
                        <a class="menu-link {{ request()->is('employee') ? 'active' : '' }}" href="/employee">
                            <span class="menu-icon">
                                <i class="fas fa-user-tie"></i>
                            </span>
                            <span class="menu-title ps-1">Employee</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->is('assessment') ? 'active' : '' }}" href="/assessment">
                            <span class="menu-icon">
                                <i class="fas fa-chart-line"></i>
                            </span>
                            <span class="menu-title ps-1">Assessment</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->is('hav') ? 'active' : '' }}" href="/hav">
                            <span class="menu-icon">
                                <i class="fas fa-th-large fs-2"></i>
                            </span>
                            <span class="menu-title ps-1">HAV Quadran</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
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

                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Master</span>
                        </div>
                        <!--end:Menu content-->
                    </div>

                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion"><!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fas fa-database"></i> <!-- Change as needed -->
                            </span>
                            <span class="menu-title ps-1">Database</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion" kt-hidden-height="84"
                            style="display: none; overflow: hidden;"><!--begin:Menu item-->
                            <div class="menu-item"><!--begin:Menu link-->
                                <a class="menu-link {{ request()->is('master/employee') ? 'active' : '' }}"
                                    href="/master/employee"><span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">
                                        Employee
                                    </span>
                                </a><!--end:Menu link-->
                            </div>
                            <!--end:Menu item--><!--begin:Menu item-->
                            <div class="menu-item"><!--begin:Menu link-->
                                <a class="menu-link {{ request()->is('master/assesment') ? 'active' : '' }}"
                                    href="/master/assesment"><span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span></span><span class="menu-title">
                                        Assesment
                                    </span>
                                </a><!--end:Menu link-->
                            </div><!--end:Menu item-->
                        </div><!--end:Menu sub-->
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

    <!--begin::Footer-->
    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
        <a href="https://preview.keenthemes.com/html/metronic/docs"
            class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100"
            data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click"
            title="200+ in-house components and 3rd-party plugins">

            <span class="btn-label">
                Docs & Components
            </span>

            <i class="ki-duotone ki-document btn-icon fs-2 m-0"><span class="path1"></span><span
                    class="path2"></span></i> </a>
    </div>
    <!--end::Footer-->
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
