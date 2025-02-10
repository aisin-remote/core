@extends('layouts.root.main')

@section('main')
    <div class="d-flex flex-column flex-column-fluid">

        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar  py-3 py-lg-6 ">

            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container  container-fluid d-flex flex-stack ">



                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3 ">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        HAV Quadran
                    </h1>
                    <!--end::Title-->


                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="index.html" class="text-muted text-hover-primary">
                                Home </a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->

                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            HAV Quadran </li>
                        <!--end::Item-->

                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">


                    <!--begin::Secondary button-->
                    <a href="#" class="btn btn-sm fw-bold btn-secondary" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_create_app">
                        Rollover </a>
                    <!--end::Secondary button-->

                    <!--begin::Primary button-->
                    <a href="#" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_new_target">
                        Add Target </a>
                    <!--end::Primary button-->
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content  flex-column-fluid ">


            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container  container-fluid ">
                <!--begin::Row-->
                <div class="row g-5 gx-xl-10 mb-2 mb-xl-10">
                    @php
                        $titles = [
                            'Maximal Contributor',
                            'Top Performer',
                            'Future Star',
                            'Star',
                            'Contributor',
                            'Strong Performer',
                            'Potential Candidate',
                            'Future Star',
                            'Minimal Contributor',
                            'Career Person',
                            'Candidate',
                            'Raw Diamond',
                            'Dead Wood',
                            'Problem Employee',
                            'Unit Employee',
                            'Most Unfit Employee',
                        ];

                        $borderColors = [
                            'bg-light-warning',
                            'bg-light-success',
                            'bg-light-primary',
                            'bg-light-info',
                            'bg-light-secondary',
                            'bg-light-warning',
                            'bg-light-success',
                            'bg-light-primary',
                            'bg-light-danger',
                            'bg-light-dark',
                            'bg-light-secondary',
                            'bg-light-warning',
                            'bg-light-danger',
                            'bg-light-dark',
                            'bg-light-secondary',
                            'bg-light-danger',
                        ];

                        $textColors = [
                            'text-warning',
                            'text-success',
                            'text-primary',
                            'text-info',
                            'text-dark',
                            'text-warning',
                            'text-success',
                            'text-primary',
                            'text-danger',
                            'text-dark',
                            'text-dark',
                            'text-warning',
                            'text-danger',
                            'text-dark',
                            'text-dark',
                            'text-danger',
                        ];

                        $progressColors = [
                            'bg-warning',
                            'bg-success',
                            'bg-primary',
                            'bg-info',
                            'bg-dark',
                            'bg-warning',
                            'bg-success',
                            'bg-primary',
                            'bg-danger',
                            'bg-dark',
                            'bg-dark',
                            'bg-warning',
                            'bg-danger',
                            'bg-dark',
                            'bg-dark',
                            'bg-danger',
                        ];
                    @endphp

                    <div class="row mt-5">
                        @for ($i = 0; $i < count($titles); $i++)
                            <div class="col-3">
                                <!--begin: Statistics Widget 6-->
                                <div class="card {{ $borderColors[$i] }} card-xl-stretch mb-xl-8">
                                    <!--begin::Body-->
                                    <div class="card-body my-3">
                                        <a href="#"
                                            class="card-title fw-bold {{ $textColors[$i] }} fs-5 mb-3 d-block">
                                            {{ $titles[$i] }} </a>

                                        <div class="py-1">
                                            <span class="text-gray-900 fs-1 fw-bold me-2">50%</span>

                                            <span class="fw-semibold text-muted fs-7">Avarage</span>
                                        </div>

                                        <div class="progress h-7px {{ $progressColors[$i] }} bg-opacity-50 mt-7">
                                            <div class="progress-bar {{ $progressColors[$i] }}" role="progressbar"
                                                style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                    <!--end:: Body-->
                                </div>
                                <!--end: Statistics Widget 6-->
                            </div>
                        @endfor
                    </div>

                    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                        <!--begin::Col-->
                        <div class="col-xl-12">

                            <!--begin::Chart widget 22-->
                            <div class="card h-xl-100">
                                <!--begin::Header-->
                                <div class="card-header position-relative py-0 border-bottom-2">
                                    <!--begin::Nav-->
                                    <ul class="nav nav-stretch nav-pills nav-pills-custom d-flex mt-3" role="tablist">
                                        <!--begin::Item-->
                                        <li class="nav-item p-0 ms-0 me-8" role="presentation">
                                            <!--begin::Link-->
                                            <a class="nav-link btn btn-color-muted px-0 active" data-bs-toggle="tab"
                                                id="kt_chart_widgets_22_tab_1" href="#kt_chart_widgets_22_tab_content_1"
                                                aria-selected="true" role="tab">
                                                <!--begin::Subtitle-->
                                                <span class="nav-text fw-semibold fs-4 mb-3">
                                                    Overview 2023
                                                </span>
                                                <!--end::Subtitle-->

                                                <!--begin::Bullet-->
                                                <span
                                                    class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
                                                <!--end::Bullet-->
                                            </a>
                                            <!--end::Link-->
                                        </li>
                                        <!--end::Item-->

                                        <!--begin::Item-->
                                        <li class="nav-item p-0 ms-0" role="presentation">
                                            <!--begin::Link-->
                                            <a class="nav-link btn btn-color-muted px-0" data-bs-toggle="tab"
                                                id="kt_chart_widgets_22_tab_2" href="#kt_chart_widgets_22_tab_content_2"
                                                aria-selected="false" role="tab" tabindex="-1">
                                                <!--begin::Subtitle-->
                                                <span class="nav-text fw-semibold fs-4 mb-3">
                                                    Overview 2022
                                                </span>
                                                <!--end::Subtitle-->

                                                <!--begin::Bullet-->
                                                <span
                                                    class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
                                                <!--end::Bullet-->
                                            </a>
                                            <!--end::Link-->
                                        </li>
                                        <!--end::Item-->
                                    </ul>
                                    <!--end::Nav-->

                                    <!--begin::Toolbar-->
                                    <div class="card-toolbar">
                                        <!--begin::Daterangepicker(defined in src/js/layout/app.js)-->
                                        <div data-kt-daterangepicker="true" data-kt-daterangepicker-opens="left"
                                            class="btn btn-sm btn-light d-flex align-items-center px-4"
                                            data-kt-initialized="1">
                                            <!--begin::Display range-->
                                            <span class="text-gray-600 fw-bold">
                                                Loading date range...
                                            </span>
                                            <!--end::Display range-->

                                            <i class="ki-duotone ki-calendar-8 text-gray-500 lh-0 fs-2 ms-2 me-0"><span
                                                    class="path1"></span><span class="path2"></span><span
                                                    class="path3"></span><span class="path4"></span><span
                                                    class="path5"></span><span class="path6"></span></i>
                                        </div>
                                        <!--end::Daterangepicker-->
                                    </div>
                                    <!--end::Toolbar-->
                                </div>
                                <!--end::Header-->

                                <!--begin::Body-->
                                <div class="card-body pb-3">
                                    <!--begin::Tab Content-->
                                    <div class="tab-content">
                                        <!--begin::Tap pane-->
                                        <div class="tab-pane fade active show" id="kt_chart_widgets_22_tab_content_1"
                                            role="tabpanel" aria-labelledby="kt_chart_widgets_22_tab_1">
                                            <!--begin::Wrapper-->
                                            <div class="d-flex flex-wrap flex-md-nowrap">
                                                <!--begin::Items-->
                                                <div class="me-md-5 w-100">

                                                    <!--begin::Item-->
                                                    <div
                                                        class="d-flex border border-gray-300 border-dashed rounded p-6 mb-6">
                                                        <!--begin::Block-->
                                                        <div class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                            <!--begin::Symbol-->
                                                            <div class="symbol symbol-50px me-4">
                                                                <span class="symbol-label">
                                                                    <i class="ki-duotone ki-timer fs-2qx text-primary"><span
                                                                            class="path1"></span><span
                                                                            class="path2"></span><span
                                                                            class="path3"></span></i>
                                                                </span>
                                                            </div>
                                                            <!--end::Symbol-->

                                                            <!--begin::Section-->
                                                            <div class="me-2">
                                                                <a href="#"
                                                                    class="text-gray-800 text-hover-primary fs-6 fw-bold">Attendance</a>

                                                                <span class="text-gray-500 fw-bold d-block fs-7">Great, you
                                                                    always attending class. keep it up</span>
                                                            </div>
                                                            <!--end::Section-->
                                                        </div>
                                                        <!--end::Block-->

                                                        <!--begin::Info-->
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-gray-900 fw-bolder fs-2x">73</span>

                                                            <span class="fw-semibold fs-2 text-gray-600 mx-1 pt-1">/</span>

                                                            <span
                                                                class="text-gray-600 fw-semibold fs-2 me-3 pt-2">76</span>

                                                            <span
                                                                class="badge badge-lg badge-light-success align-self-center px-2">95%</span>
                                                        </div>
                                                        <!--end::Info-->
                                                    </div>
                                                    <!--end::Item-->

                                                    <!--begin::Item-->
                                                    <div
                                                        class="d-flex border border-gray-300 border-dashed rounded p-6 mb-6">
                                                        <!--begin::Block-->
                                                        <div class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                            <!--begin::Symbol-->
                                                            <div class="symbol symbol-50px me-4">
                                                                <span class="symbol-label">
                                                                    <i
                                                                        class="ki-duotone ki-element-11 fs-2qx text-primary"><span
                                                                            class="path1"></span><span
                                                                            class="path2"></span><span
                                                                            class="path3"></span><span
                                                                            class="path4"></span></i>
                                                                </span>
                                                            </div>
                                                            <!--end::Symbol-->

                                                            <!--begin::Section-->
                                                            <div class="me-2">
                                                                <a href="#"
                                                                    class="text-gray-800 text-hover-primary fs-6 fw-bold">Homeworks</a>

                                                                <span class="text-gray-500 fw-bold d-block fs-7">Don’t
                                                                    forget to turn in your task</span>
                                                            </div>
                                                            <!--end::Section-->
                                                        </div>
                                                        <!--end::Block-->

                                                        <!--begin::Info-->
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-gray-900 fw-bolder fs-2x">207</span>

                                                            <span class="fw-semibold fs-2 text-gray-600 mx-1 pt-1">/</span>

                                                            <span
                                                                class="text-gray-600 fw-semibold fs-2 me-3 pt-2">214</span>

                                                            <span
                                                                class="badge badge-lg badge-light-success align-self-center px-2">92%</span>
                                                        </div>
                                                        <!--end::Info-->
                                                    </div>
                                                    <!--end::Item-->

                                                    <!--begin::Item-->
                                                    <div
                                                        class="d-flex border border-gray-300 border-dashed rounded p-6 mb-6">
                                                        <!--begin::Block-->
                                                        <div class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                            <!--begin::Symbol-->
                                                            <div class="symbol symbol-50px me-4">
                                                                <span class="symbol-label">
                                                                    <i
                                                                        class="ki-duotone ki-abstract-24 fs-2qx text-primary"><span
                                                                            class="path1"></span><span
                                                                            class="path2"></span></i>
                                                                </span>
                                                            </div>
                                                            <!--end::Symbol-->

                                                            <!--begin::Section-->
                                                            <div class="me-2">
                                                                <a href="#"
                                                                    class="text-gray-800 text-hover-primary fs-6 fw-bold">Tests</a>

                                                                <span class="text-gray-500 fw-bold d-block fs-7">You take
                                                                    12 subjects at this semester</span>
                                                            </div>
                                                            <!--end::Section-->
                                                        </div>
                                                        <!--end::Block-->

                                                        <!--begin::Info-->
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-gray-900 fw-bolder fs-2x">27</span>

                                                            <span class="fw-semibold fs-2 text-gray-600 mx-1 pt-1">/</span>

                                                            <span
                                                                class="text-gray-600 fw-semibold fs-2 me-3 pt-2">38</span>

                                                            <span
                                                                class="badge badge-lg badge-light-warning align-self-center px-2">80%</span>
                                                        </div>
                                                        <!--end::Info-->
                                                    </div>
                                                    <!--end::Item-->
                                                </div>
                                                <!--end::Items-->

                                                <!--begin::Container-->
                                                <div
                                                    class="d-flex justify-content-between flex-column w-225px w-md-600px mx-auto mx-md-0 pt-3 pb-10">
                                                    <!--begin::Title-->
                                                    <div class="fs-4 fw-bold text-gray-900 text-center mb-5">
                                                        Session Attendance <br>
                                                        for Current Academic Year
                                                    </div>
                                                    <!--end::Title-->

                                                    <!--begin::Chart-->
                                                    <div id="kt_chart_widgets_22_chart_1" class="mx-auto mb-4"
                                                        style="min-height: 216px;">
                                                        <div id="apexchartsif64io2jj"
                                                            class="apexcharts-canvas apexchartsif64io2jj apexcharts-theme-"
                                                            style="width: 250px; height: 216px;"><svg id="SvgjsSvg2630"
                                                                width="250" height="216"
                                                                xmlns="http://www.w3.org/2000/svg" version="1.1"
                                                                xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                xmlns:svgjs="http://svgjs.dev" class="apexcharts-svg"
                                                                xmlns:data="ApexChartsNS" transform="translate(0, 0)">
                                                                <foreignObject x="0" y="0" width="250" height="216">
                                                                    <div class="apexcharts-legend"
                                                                        xmlns="http://www.w3.org/1999/xhtml"></div>
                                                                    <style type="text/css">
                                                                        .apexcharts-flip-y {
                                                                            transform: scaleY(-1) translateY(-100%);
                                                                            transform-origin: top;
                                                                            transform-box: fill-box;
                                                                        }

                                                                        .apexcharts-flip-x {
                                                                            transform: scaleX(-1);
                                                                            transform-origin: center;
                                                                            transform-box: fill-box;
                                                                        }

                                                                        .apexcharts-legend {
                                                                            display: flex;
                                                                            overflow: auto;
                                                                            padding: 0 10px;
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-bottom,
                                                                        .apexcharts-legend.apx-legend-position-top {
                                                                            flex-wrap: wrap
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-right,
                                                                        .apexcharts-legend.apx-legend-position-left {
                                                                            flex-direction: column;
                                                                            bottom: 0;
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-left,
                                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-left,
                                                                        .apexcharts-legend.apx-legend-position-right,
                                                                        .apexcharts-legend.apx-legend-position-left {
                                                                            justify-content: flex-start;
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-center,
                                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-center {
                                                                            justify-content: center;
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-right,
                                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-right {
                                                                            justify-content: flex-end;
                                                                        }

                                                                        .apexcharts-legend-series {
                                                                            cursor: pointer;
                                                                            line-height: normal;
                                                                            display: flex;
                                                                            align-items: center;
                                                                        }

                                                                        .apexcharts-legend-text {
                                                                            position: relative;
                                                                            font-size: 14px;
                                                                        }

                                                                        .apexcharts-legend-text *,
                                                                        .apexcharts-legend-marker * {
                                                                            pointer-events: none;
                                                                        }

                                                                        .apexcharts-legend-marker {
                                                                            position: relative;
                                                                            display: flex;
                                                                            align-items: center;
                                                                            justify-content: center;
                                                                            cursor: pointer;
                                                                            margin-right: 1px;
                                                                        }

                                                                        .apexcharts-legend-series.apexcharts-no-click {
                                                                            cursor: auto;
                                                                        }

                                                                        .apexcharts-legend .apexcharts-hidden-zero-series,
                                                                        .apexcharts-legend .apexcharts-hidden-null-series {
                                                                            display: none !important;
                                                                        }

                                                                        .apexcharts-inactive-legend {
                                                                            opacity: 0.45;
                                                                        }
                                                                    </style>
                                                                </foreignObject>
                                                                <g id="SvgjsG2632"
                                                                    class="apexcharts-inner apexcharts-graphical"
                                                                    transform="translate(20.83333333333333, 0)">
                                                                    <defs id="SvgjsDefs2631">
                                                                        <clipPath id="gridRectMaskif64io2jj">
                                                                            <rect id="SvgjsRect2633"
                                                                                width="208.33333333333334"
                                                                                height="208.33333333333334" x="0" y="0"
                                                                                rx="0" ry="0"
                                                                                opacity="1" stroke-width="0"
                                                                                stroke="none" stroke-dasharray="0"
                                                                                fill="#fff"></rect>
                                                                        </clipPath>
                                                                        <clipPath id="gridRectBarMaskif64io2jj">
                                                                            <rect id="SvgjsRect2634"
                                                                                width="212.33333333333334"
                                                                                height="212.33333333333334" x="-2" y="-2"
                                                                                rx="0" ry="0"
                                                                                opacity="1" stroke-width="0"
                                                                                stroke="none" stroke-dasharray="0"
                                                                                fill="#fff"></rect>
                                                                        </clipPath>
                                                                        <clipPath id="gridRectMarkerMaskif64io2jj">
                                                                            <rect id="SvgjsRect2635"
                                                                                width="208.33333333333334"
                                                                                height="208.33333333333334" x="0" y="0"
                                                                                rx="0" ry="0"
                                                                                opacity="1" stroke-width="0"
                                                                                stroke="none" stroke-dasharray="0"
                                                                                fill="#fff"></rect>
                                                                        </clipPath>
                                                                        <clipPath id="forecastMaskif64io2jj"></clipPath>
                                                                        <clipPath id="nonForecastMaskif64io2jj"></clipPath>
                                                                        <filter id="SvgjsFilter2646"
                                                                            filterUnits="userSpaceOnUse" width="200%"
                                                                            height="200%" x="-50%" y="-50%">
                                                                            <feFlood id="SvgjsFeFlood2647"
                                                                                flood-color="#000000" flood-opacity="0.45"
                                                                                result="SvgjsFeFlood2647Out"
                                                                                in="SourceGraphic"></feFlood>
                                                                            <feComposite id="SvgjsFeComposite2648"
                                                                                in="SvgjsFeFlood2647Out" in2="SourceAlpha"
                                                                                operator="in"
                                                                                result="SvgjsFeComposite2648Out">
                                                                            </feComposite>
                                                                            <feOffset id="SvgjsFeOffset2649"
                                                                                dx="1" dy="1"
                                                                                result="SvgjsFeOffset2649Out"
                                                                                in="SvgjsFeComposite2648Out"></feOffset>
                                                                            <feGaussianBlur id="SvgjsFeGaussianBlur2650"
                                                                                stdDeviation="1 "
                                                                                result="SvgjsFeGaussianBlur2650Out"
                                                                                in="SvgjsFeOffset2649Out"></feGaussianBlur>
                                                                            <feMerge id="SvgjsFeMerge2651"
                                                                                result="SvgjsFeMerge2651Out"
                                                                                in="SourceGraphic">
                                                                                <feMergeNode id="SvgjsFeMergeNode2652"
                                                                                    in="SvgjsFeGaussianBlur2650Out">
                                                                                </feMergeNode>
                                                                                <feMergeNode id="SvgjsFeMergeNode2653"
                                                                                    in="[object Arguments]"></feMergeNode>
                                                                            </feMerge>
                                                                            <feBlend id="SvgjsFeBlend2654"
                                                                                in="SourceGraphic"
                                                                                in2="SvgjsFeMerge2651Out" mode="normal"
                                                                                result="SvgjsFeBlend2654Out"></feBlend>
                                                                        </filter>
                                                                        <filter id="SvgjsFilter2659"
                                                                            filterUnits="userSpaceOnUse" width="200%"
                                                                            height="200%" x="-50%" y="-50%">
                                                                            <feFlood id="SvgjsFeFlood2660"
                                                                                flood-color="#000000" flood-opacity="0.45"
                                                                                result="SvgjsFeFlood2660Out"
                                                                                in="SourceGraphic"></feFlood>
                                                                            <feComposite id="SvgjsFeComposite2661"
                                                                                in="SvgjsFeFlood2660Out" in2="SourceAlpha"
                                                                                operator="in"
                                                                                result="SvgjsFeComposite2661Out">
                                                                            </feComposite>
                                                                            <feOffset id="SvgjsFeOffset2662"
                                                                                dx="1" dy="1"
                                                                                result="SvgjsFeOffset2662Out"
                                                                                in="SvgjsFeComposite2661Out"></feOffset>
                                                                            <feGaussianBlur id="SvgjsFeGaussianBlur2663"
                                                                                stdDeviation="1 "
                                                                                result="SvgjsFeGaussianBlur2663Out"
                                                                                in="SvgjsFeOffset2662Out"></feGaussianBlur>
                                                                            <feMerge id="SvgjsFeMerge2664"
                                                                                result="SvgjsFeMerge2664Out"
                                                                                in="SourceGraphic">
                                                                                <feMergeNode id="SvgjsFeMergeNode2665"
                                                                                    in="SvgjsFeGaussianBlur2663Out">
                                                                                </feMergeNode>
                                                                                <feMergeNode id="SvgjsFeMergeNode2666"
                                                                                    in="[object Arguments]"></feMergeNode>
                                                                            </feMerge>
                                                                            <feBlend id="SvgjsFeBlend2667"
                                                                                in="SourceGraphic"
                                                                                in2="SvgjsFeMerge2664Out" mode="normal"
                                                                                result="SvgjsFeBlend2667Out"></feBlend>
                                                                        </filter>
                                                                        <filter id="SvgjsFilter2672"
                                                                            filterUnits="userSpaceOnUse" width="200%"
                                                                            height="200%" x="-50%" y="-50%">
                                                                            <feFlood id="SvgjsFeFlood2673"
                                                                                flood-color="#000000" flood-opacity="0.45"
                                                                                result="SvgjsFeFlood2673Out"
                                                                                in="SourceGraphic"></feFlood>
                                                                            <feComposite id="SvgjsFeComposite2674"
                                                                                in="SvgjsFeFlood2673Out" in2="SourceAlpha"
                                                                                operator="in"
                                                                                result="SvgjsFeComposite2674Out">
                                                                            </feComposite>
                                                                            <feOffset id="SvgjsFeOffset2675"
                                                                                dx="1" dy="1"
                                                                                result="SvgjsFeOffset2675Out"
                                                                                in="SvgjsFeComposite2674Out"></feOffset>
                                                                            <feGaussianBlur id="SvgjsFeGaussianBlur2676"
                                                                                stdDeviation="1 "
                                                                                result="SvgjsFeGaussianBlur2676Out"
                                                                                in="SvgjsFeOffset2675Out"></feGaussianBlur>
                                                                            <feMerge id="SvgjsFeMerge2677"
                                                                                result="SvgjsFeMerge2677Out"
                                                                                in="SourceGraphic">
                                                                                <feMergeNode id="SvgjsFeMergeNode2678"
                                                                                    in="SvgjsFeGaussianBlur2676Out">
                                                                                </feMergeNode>
                                                                                <feMergeNode id="SvgjsFeMergeNode2679"
                                                                                    in="[object Arguments]"></feMergeNode>
                                                                            </feMerge>
                                                                            <feBlend id="SvgjsFeBlend2680"
                                                                                in="SourceGraphic"
                                                                                in2="SvgjsFeMerge2677Out" mode="normal"
                                                                                result="SvgjsFeBlend2680Out"></feBlend>
                                                                        </filter>
                                                                        <filter id="SvgjsFilter2685"
                                                                            filterUnits="userSpaceOnUse" width="200%"
                                                                            height="200%" x="-50%" y="-50%">
                                                                            <feFlood id="SvgjsFeFlood2686"
                                                                                flood-color="#000000" flood-opacity="0.45"
                                                                                result="SvgjsFeFlood2686Out"
                                                                                in="SourceGraphic"></feFlood>
                                                                            <feComposite id="SvgjsFeComposite2687"
                                                                                in="SvgjsFeFlood2686Out" in2="SourceAlpha"
                                                                                operator="in"
                                                                                result="SvgjsFeComposite2687Out">
                                                                            </feComposite>
                                                                            <feOffset id="SvgjsFeOffset2688"
                                                                                dx="1" dy="1"
                                                                                result="SvgjsFeOffset2688Out"
                                                                                in="SvgjsFeComposite2687Out"></feOffset>
                                                                            <feGaussianBlur id="SvgjsFeGaussianBlur2689"
                                                                                stdDeviation="1 "
                                                                                result="SvgjsFeGaussianBlur2689Out"
                                                                                in="SvgjsFeOffset2688Out"></feGaussianBlur>
                                                                            <feMerge id="SvgjsFeMerge2690"
                                                                                result="SvgjsFeMerge2690Out"
                                                                                in="SourceGraphic">
                                                                                <feMergeNode id="SvgjsFeMergeNode2691"
                                                                                    in="SvgjsFeGaussianBlur2689Out">
                                                                                </feMergeNode>
                                                                                <feMergeNode id="SvgjsFeMergeNode2692"
                                                                                    in="[object Arguments]"></feMergeNode>
                                                                            </feMerge>
                                                                            <feBlend id="SvgjsFeBlend2693"
                                                                                in="SourceGraphic"
                                                                                in2="SvgjsFeMerge2690Out" mode="normal"
                                                                                result="SvgjsFeBlend2693Out"></feBlend>
                                                                        </filter>
                                                                    </defs>
                                                                    <g id="SvgjsG2638" class="apexcharts-pie">
                                                                        <g id="SvgjsG2639"
                                                                            transform="translate(0, 0) scale(1)">
                                                                            <circle id="SvgjsCircle2640"
                                                                                r="48.81300813008131"
                                                                                cx="104.16666666666667"
                                                                                cy="104.16666666666667"
                                                                                fill="transparent"></circle>
                                                                            <g id="SvgjsG2641" class="apexcharts-slices">
                                                                                <g id="SvgjsG2642"
                                                                                    class="apexcharts-series apexcharts-pie-series"
                                                                                    seriesName="Sales" rel="1"
                                                                                    data:realIndex="0">
                                                                                    <path id="SvgjsPath2643"
                                                                                        d="M 104.16666666666667 6.5406504065040565 A 97.62601626016261 97.62601626016261 0 0 1 173.1986847844558 35.134648548877536 L 138.68267572556124 69.6506576077721 A 48.81300813008131 48.81300813008131 0 0 0 104.16666666666667 55.353658536585364 L 104.16666666666667 6.5406504065040565 z "
                                                                                        fill="rgba(114,57,234,1)"
                                                                                        fill-opacity="1"
                                                                                        stroke-opacity="1"
                                                                                        stroke-linecap="butt"
                                                                                        stroke-width="0"
                                                                                        stroke-dasharray="0"
                                                                                        class="apexcharts-pie-area apexcharts-donut-slice-0"
                                                                                        index="0" j="0"
                                                                                        data:angle="45"
                                                                                        data:startAngle="0"
                                                                                        data:strokeWidth="0"
                                                                                        data:value="20"
                                                                                        data:pathOrig="M 104.16666666666667 6.5406504065040565 A 97.62601626016261 97.62601626016261 0 0 1 173.1986847844558 35.134648548877536 L 138.68267572556124 69.6506576077721 A 48.81300813008131 48.81300813008131 0 0 0 104.16666666666667 55.353658536585364 L 104.16666666666667 6.5406504065040565 z ">
                                                                                    </path>
                                                                                </g>
                                                                                <g id="SvgjsG2655"
                                                                                    class="apexcharts-series apexcharts-pie-series"
                                                                                    seriesName="Sales" rel="2"
                                                                                    data:realIndex="1">
                                                                                    <path id="SvgjsPath2656"
                                                                                        d="M 173.1986847844558 35.134648548877536 A 97.62601626016261 97.62601626016261 0 1 1 6.5406504065040565 104.16666666666669 L 55.353658536585364 104.16666666666667 A 48.81300813008131 48.81300813008131 0 1 0 138.68267572556124 69.6506576077721 L 173.1986847844558 35.134648548877536 z "
                                                                                        fill="rgba(23,198,83,1)"
                                                                                        fill-opacity="1"
                                                                                        stroke-opacity="1"
                                                                                        stroke-linecap="butt"
                                                                                        stroke-width="0"
                                                                                        stroke-dasharray="0"
                                                                                        class="apexcharts-pie-area apexcharts-donut-slice-1"
                                                                                        index="0" j="1"
                                                                                        data:angle="225"
                                                                                        data:startAngle="45"
                                                                                        data:strokeWidth="0"
                                                                                        data:value="100"
                                                                                        data:pathOrig="M 173.1986847844558 35.134648548877536 A 97.62601626016261 97.62601626016261 0 1 1 6.5406504065040565 104.16666666666669 L 55.353658536585364 104.16666666666667 A 48.81300813008131 48.81300813008131 0 1 0 138.68267572556124 69.6506576077721 L 173.1986847844558 35.134648548877536 z ">
                                                                                    </path>
                                                                                </g>
                                                                                <g id="SvgjsG2668"
                                                                                    class="apexcharts-series apexcharts-pie-series"
                                                                                    seriesName="Sales" rel="3"
                                                                                    data:realIndex="2">
                                                                                    <path id="SvgjsPath2669"
                                                                                        d="M 6.5406504065040565 104.16666666666669 A 97.62601626016261 97.62601626016261 0 0 1 22.99360077618728 49.92855806423265 L 63.580133721426975 77.04761236544965 A 48.81300813008131 48.81300813008131 0 0 0 55.353658536585364 104.16666666666667 L 6.5406504065040565 104.16666666666669 z "
                                                                                        fill="rgba(27,132,255,1)"
                                                                                        fill-opacity="1"
                                                                                        stroke-opacity="1"
                                                                                        stroke-linecap="butt"
                                                                                        stroke-width="0"
                                                                                        stroke-dasharray="0"
                                                                                        class="apexcharts-pie-area apexcharts-donut-slice-2"
                                                                                        index="0" j="2"
                                                                                        data:angle="33.75"
                                                                                        data:startAngle="270"
                                                                                        data:strokeWidth="0"
                                                                                        data:value="15"
                                                                                        data:pathOrig="M 6.5406504065040565 104.16666666666669 A 97.62601626016261 97.62601626016261 0 0 1 22.99360077618728 49.92855806423265 L 63.580133721426975 77.04761236544965 A 48.81300813008131 48.81300813008131 0 0 0 55.353658536585364 104.16666666666667 L 6.5406504065040565 104.16666666666669 z ">
                                                                                    </path>
                                                                                </g>
                                                                                <g id="SvgjsG2681"
                                                                                    class="apexcharts-series apexcharts-pie-series"
                                                                                    seriesName="Sales" rel="4"
                                                                                    data:realIndex="3">
                                                                                    <path id="SvgjsPath2682"
                                                                                        d="M 22.99360077618728 49.92855806423265 A 97.62601626016261 97.62601626016261 0 0 1 104.1496277125597 6.540651893433306 L 104.15814718961319 55.35365928004999 A 48.81300813008131 48.81300813008131 0 0 0 63.580133721426975 77.04761236544965 L 22.99360077618728 49.92855806423265 z "
                                                                                        fill="rgba(248,40,90,1)"
                                                                                        fill-opacity="1"
                                                                                        stroke-opacity="1"
                                                                                        stroke-linecap="butt"
                                                                                        stroke-width="0"
                                                                                        stroke-dasharray="0"
                                                                                        class="apexcharts-pie-area apexcharts-donut-slice-3"
                                                                                        index="0" j="3"
                                                                                        data:angle="56.25"
                                                                                        data:startAngle="303.75"
                                                                                        data:strokeWidth="0"
                                                                                        data:value="25"
                                                                                        data:pathOrig="M 22.99360077618728 49.92855806423265 A 97.62601626016261 97.62601626016261 0 0 1 104.1496277125597 6.540651893433306 L 104.15814718961319 55.35365928004999 A 48.81300813008131 48.81300813008131 0 0 0 63.580133721426975 77.04761236544965 L 22.99360077618728 49.92855806423265 z ">
                                                                                    </path>
                                                                                </g>
                                                                                <g id="SvgjsG2644"
                                                                                    class="apexcharts-datalabels"><text
                                                                                        id="SvgjsText2645"
                                                                                        font-family="inherit"
                                                                                        x="132.1865609095935"
                                                                                        y="36.52065796913293"
                                                                                        text-anchor="middle"
                                                                                        dominant-baseline="auto"
                                                                                        font-size="12px" font-weight="600"
                                                                                        fill="#ffffff"
                                                                                        class="apexcharts-text apexcharts-pie-label"
                                                                                        filter="url(#SvgjsFilter2646)"
                                                                                        style="font-family: inherit;">12.5%</text>
                                                                                </g>
                                                                                <g id="SvgjsG2657"
                                                                                    class="apexcharts-datalabels"><text
                                                                                        id="SvgjsText2658"
                                                                                        font-family="inherit"
                                                                                        x="132.1865609095935"
                                                                                        y="171.8126753642004"
                                                                                        text-anchor="middle"
                                                                                        dominant-baseline="auto"
                                                                                        font-size="12px" font-weight="600"
                                                                                        fill="#ffffff"
                                                                                        class="apexcharts-text apexcharts-pie-label"
                                                                                        filter="url(#SvgjsFilter2659)"
                                                                                        style="font-family: inherit;">62.5%</text>
                                                                                </g>
                                                                                <g id="SvgjsG2670"
                                                                                    class="apexcharts-datalabels"><text
                                                                                        id="SvgjsText2671"
                                                                                        font-family="inherit"
                                                                                        x="34.0999620845181"
                                                                                        y="82.91216420037654"
                                                                                        text-anchor="middle"
                                                                                        dominant-baseline="auto"
                                                                                        font-size="12px" font-weight="600"
                                                                                        fill="#ffffff"
                                                                                        class="apexcharts-text apexcharts-pie-label"
                                                                                        filter="url(#SvgjsFilter2672)"
                                                                                        style="font-family: inherit;">9.4%</text>
                                                                                </g>
                                                                                <g id="SvgjsG2683"
                                                                                    class="apexcharts-datalabels"><text
                                                                                        id="SvgjsText2684"
                                                                                        font-family="inherit"
                                                                                        x="69.65122754589481"
                                                                                        y="39.59282189657492"
                                                                                        text-anchor="middle"
                                                                                        dominant-baseline="auto"
                                                                                        font-size="12px" font-weight="600"
                                                                                        fill="#ffffff"
                                                                                        class="apexcharts-text apexcharts-pie-label"
                                                                                        filter="url(#SvgjsFilter2685)"
                                                                                        style="font-family: inherit;">15.6%</text>
                                                                                </g>
                                                                            </g>
                                                                        </g>
                                                                    </g>
                                                                    <line id="SvgjsLine2694" x1="0"
                                                                        y1="0" x2="208.33333333333334"
                                                                        y2="0" stroke="#b6b6b6"
                                                                        stroke-dasharray="0" stroke-width="1"
                                                                        stroke-linecap="butt"
                                                                        class="apexcharts-ycrosshairs"></line>
                                                                    <line id="SvgjsLine2695" x1="0"
                                                                        y1="0" x2="208.33333333333334"
                                                                        y2="0" stroke-dasharray="0"
                                                                        stroke-width="0" stroke-linecap="butt"
                                                                        class="apexcharts-ycrosshairs-hidden"></line>
                                                                </g>
                                                                <g id="SvgjsG2636" class="apexcharts-datalabels-group"
                                                                    transform="translate(0, 0) scale(1)"></g>
                                                                <g id="SvgjsG2637" class="apexcharts-datalabels-group"
                                                                    transform="translate(0, 0) scale(1)"></g>
                                                            </svg>
                                                            <div class="apexcharts-tooltip apexcharts-theme-dark">
                                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-0"
                                                                    style="order: 1;"><span
                                                                        class="apexcharts-tooltip-marker"
                                                                        style="background-color: rgb(114, 57, 234);"></span>
                                                                    <div class="apexcharts-tooltip-text"
                                                                        style="font-family: inherit; font-size: 12px;">
                                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                                class="apexcharts-tooltip-text-y-label"></span><span
                                                                                class="apexcharts-tooltip-text-y-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-1"
                                                                    style="order: 2;"><span
                                                                        class="apexcharts-tooltip-marker"
                                                                        style="background-color: rgb(23, 198, 83);"></span>
                                                                    <div class="apexcharts-tooltip-text"
                                                                        style="font-family: inherit; font-size: 12px;">
                                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                                class="apexcharts-tooltip-text-y-label"></span><span
                                                                                class="apexcharts-tooltip-text-y-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-2"
                                                                    style="order: 3;"><span
                                                                        class="apexcharts-tooltip-marker"
                                                                        style="background-color: rgb(27, 132, 255);"></span>
                                                                    <div class="apexcharts-tooltip-text"
                                                                        style="font-family: inherit; font-size: 12px;">
                                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                                class="apexcharts-tooltip-text-y-label"></span><span
                                                                                class="apexcharts-tooltip-text-y-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-3"
                                                                    style="order: 4;"><span
                                                                        class="apexcharts-tooltip-marker"
                                                                        style="background-color: rgb(248, 40, 90);"></span>
                                                                    <div class="apexcharts-tooltip-text"
                                                                        style="font-family: inherit; font-size: 12px;">
                                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                                class="apexcharts-tooltip-text-y-label"></span><span
                                                                                class="apexcharts-tooltip-text-y-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--end::Chart-->

                                                    <!--begin::Labels-->
                                                    <div class="mx-auto">
                                                        <!--begin::Label-->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <!--begin::Bullet-->
                                                            <div class="bullet bullet-dot w-8px h-7px bg-success me-2">
                                                            </div>
                                                            <!--end::Bullet-->

                                                            <!--begin::Label-->
                                                            <div class="fs-8 fw-semibold text-muted">Precent(133)</div>
                                                            <!--end::Label-->
                                                        </div>
                                                        <!--end::Label-->

                                                        <!--begin::Label-->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <!--begin::Bullet-->
                                                            <div class="bullet bullet-dot w-8px h-7px bg-primary me-2">
                                                            </div>
                                                            <!--end::Bullet-->

                                                            <!--begin::Label-->
                                                            <div class="fs-8 fw-semibold text-muted">Illness(9)</div>
                                                            <!--end::Label-->
                                                        </div>
                                                        <!--end::Label-->

                                                        <!--begin::Label-->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <!--begin::Bullet-->
                                                            <div class="bullet bullet-dot w-8px h-7px bg-info me-2"></div>
                                                            <!--end::Bullet-->

                                                            <!--begin::Label-->
                                                            <div class="fs-8 fw-semibold text-muted">Late(2)</div>
                                                            <!--end::Label-->
                                                        </div>
                                                        <!--end::Label-->

                                                        <!--begin::Label-->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <!--begin::Bullet-->
                                                            <div class="bullet bullet-dot w-8px h-7px bg-danger me-2">
                                                            </div>
                                                            <!--end::Bullet-->

                                                            <!--begin::Label-->
                                                            <div class="fs-8 fw-semibold text-muted">Absent(3)</div>
                                                            <!--end::Label-->
                                                        </div>
                                                        <!--end::Label-->
                                                    </div>
                                                    <!--end::Labels-->
                                                </div>
                                                <!--end::Container-->
                                            </div>
                                            <!--end::Wrapper-->
                                        </div>
                                        <!--end::Tap pane-->
                                        <!--begin::Tap pane-->
                                        <div class="tab-pane fade" id="kt_chart_widgets_22_tab_content_2" role="tabpanel"
                                            aria-labelledby="kt_chart_widgets_22_tab_2">
                                            <!--begin::Wrapper-->
                                            <div class="d-flex flex-wrap flex-md-nowrap">
                                                <!--begin::Items-->
                                                <div class="me-md-5 w-100">

                                                    <!--begin::Item-->
                                                    <div
                                                        class="d-flex border border-gray-300 border-dashed rounded p-6 mb-6">
                                                        <!--begin::Block-->
                                                        <div class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                            <!--begin::Symbol-->
                                                            <div class="symbol symbol-50px me-4">
                                                                <span class="symbol-label">
                                                                    <i
                                                                        class="ki-duotone ki-element-11 fs-2qx text-primary"><span
                                                                            class="path1"></span><span
                                                                            class="path2"></span><span
                                                                            class="path3"></span><span
                                                                            class="path4"></span></i>
                                                                </span>
                                                            </div>
                                                            <!--end::Symbol-->

                                                            <!--begin::Section-->
                                                            <div class="me-2">
                                                                <a href="#"
                                                                    class="text-gray-800 text-hover-primary fs-6 fw-bold">Homeworks</a>

                                                                <span class="text-gray-500 fw-bold d-block fs-7">Don’t
                                                                    forget to turn in your task</span>
                                                            </div>
                                                            <!--end::Section-->
                                                        </div>
                                                        <!--end::Block-->

                                                        <!--begin::Info-->
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-gray-900 fw-bolder fs-2x">423</span>

                                                            <span class="fw-semibold fs-2 text-gray-600 mx-1 pt-1">/</span>

                                                            <span
                                                                class="text-gray-600 fw-semibold fs-2 me-3 pt-2">154</span>

                                                            <span
                                                                class="badge badge-lg badge-light-danger align-self-center px-2">74%</span>
                                                        </div>
                                                        <!--end::Info-->
                                                    </div>
                                                    <!--end::Item-->

                                                    <!--begin::Item-->
                                                    <div
                                                        class="d-flex border border-gray-300 border-dashed rounded p-6 mb-6">
                                                        <!--begin::Block-->
                                                        <div class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                            <!--begin::Symbol-->
                                                            <div class="symbol symbol-50px me-4">
                                                                <span class="symbol-label">
                                                                    <i
                                                                        class="ki-duotone ki-abstract-24 fs-2qx text-primary"><span
                                                                            class="path1"></span><span
                                                                            class="path2"></span></i>
                                                                </span>
                                                            </div>
                                                            <!--end::Symbol-->

                                                            <!--begin::Section-->
                                                            <div class="me-2">
                                                                <a href="#"
                                                                    class="text-gray-800 text-hover-primary fs-6 fw-bold">Tests</a>

                                                                <span class="text-gray-500 fw-bold d-block fs-7">You take
                                                                    12 subjects at this semester</span>
                                                            </div>
                                                            <!--end::Section-->
                                                        </div>
                                                        <!--end::Block-->

                                                        <!--begin::Info-->
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-gray-900 fw-bolder fs-2x">43</span>

                                                            <span class="fw-semibold fs-2 text-gray-600 mx-1 pt-1">/</span>

                                                            <span
                                                                class="text-gray-600 fw-semibold fs-2 me-3 pt-2">53</span>

                                                            <span
                                                                class="badge badge-lg badge-light-info align-self-center px-2">65%</span>
                                                        </div>
                                                        <!--end::Info-->
                                                    </div>
                                                    <!--end::Item-->

                                                    <!--begin::Item-->
                                                    <div
                                                        class="d-flex border border-gray-300 border-dashed rounded p-6 mb-6">
                                                        <!--begin::Block-->
                                                        <div class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                            <!--begin::Symbol-->
                                                            <div class="symbol symbol-50px me-4">
                                                                <span class="symbol-label">
                                                                    <i class="ki-duotone ki-timer fs-2qx text-primary"><span
                                                                            class="path1"></span><span
                                                                            class="path2"></span><span
                                                                            class="path3"></span></i>
                                                                </span>
                                                            </div>
                                                            <!--end::Symbol-->

                                                            <!--begin::Section-->
                                                            <div class="me-2">
                                                                <a href="#"
                                                                    class="text-gray-800 text-hover-primary fs-6 fw-bold">Attendance</a>

                                                                <span class="text-gray-500 fw-bold d-block fs-7">Great, you
                                                                    always attending class. keep it up</span>
                                                            </div>
                                                            <!--end::Section-->
                                                        </div>
                                                        <!--end::Block-->

                                                        <!--begin::Info-->
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-gray-900 fw-bolder fs-2x">53</span>

                                                            <span class="fw-semibold fs-2 text-gray-600 mx-1 pt-1">/</span>

                                                            <span
                                                                class="text-gray-600 fw-semibold fs-2 me-3 pt-2">94</span>

                                                            <span
                                                                class="badge badge-lg badge-light-primary align-self-center px-2">87%</span>
                                                        </div>
                                                        <!--end::Info-->
                                                    </div>
                                                    <!--end::Item-->
                                                </div>
                                                <!--end::Items-->

                                                <!--begin::Container-->
                                                <div
                                                    class="d-flex justify-content-between flex-column w-225px w-md-600px mx-auto mx-md-0 pt-3 pb-10">
                                                    <!--begin::Title-->
                                                    <div class="fs-4 fw-bold text-gray-900 text-center mb-5">
                                                        Session Attendance <br>
                                                        for Current Academic Year
                                                    </div>
                                                    <!--end::Title-->

                                                    <!--begin::Chart-->
                                                    <div id="kt_chart_widgets_22_chart_2" class="mx-auto mb-4"
                                                        style="min-height: 7px;">
                                                        <div id="apexcharts2j66rgos"
                                                            class="apexcharts-canvas apexcharts2j66rgos apexcharts-theme-"
                                                            style="width: 250px; height: 7px;"><svg id="SvgjsSvg2575"
                                                                width="250" height="7"
                                                                xmlns="http://www.w3.org/2000/svg" version="1.1"
                                                                xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                xmlns:svgjs="http://svgjs.dev" class="apexcharts-svg"
                                                                xmlns:data="ApexChartsNS" transform="translate(0, 0)">
                                                                <foreignObject x="0" y="0" width="250" height="7">
                                                                    <div class="apexcharts-legend"
                                                                        xmlns="http://www.w3.org/1999/xhtml"></div>
                                                                    <style type="text/css">
                                                                        .apexcharts-flip-y {
                                                                            transform: scaleY(-1) translateY(-100%);
                                                                            transform-origin: top;
                                                                            transform-box: fill-box;
                                                                        }

                                                                        .apexcharts-flip-x {
                                                                            transform: scaleX(-1);
                                                                            transform-origin: center;
                                                                            transform-box: fill-box;
                                                                        }

                                                                        .apexcharts-legend {
                                                                            display: flex;
                                                                            overflow: auto;
                                                                            padding: 0 10px;
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-bottom,
                                                                        .apexcharts-legend.apx-legend-position-top {
                                                                            flex-wrap: wrap
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-right,
                                                                        .apexcharts-legend.apx-legend-position-left {
                                                                            flex-direction: column;
                                                                            bottom: 0;
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-left,
                                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-left,
                                                                        .apexcharts-legend.apx-legend-position-right,
                                                                        .apexcharts-legend.apx-legend-position-left {
                                                                            justify-content: flex-start;
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-center,
                                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-center {
                                                                            justify-content: center;
                                                                        }

                                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-right,
                                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-right {
                                                                            justify-content: flex-end;
                                                                        }

                                                                        .apexcharts-legend-series {
                                                                            cursor: pointer;
                                                                            line-height: normal;
                                                                            display: flex;
                                                                            align-items: center;
                                                                        }

                                                                        .apexcharts-legend-text {
                                                                            position: relative;
                                                                            font-size: 14px;
                                                                        }

                                                                        .apexcharts-legend-text *,
                                                                        .apexcharts-legend-marker * {
                                                                            pointer-events: none;
                                                                        }

                                                                        .apexcharts-legend-marker {
                                                                            position: relative;
                                                                            display: flex;
                                                                            align-items: center;
                                                                            justify-content: center;
                                                                            cursor: pointer;
                                                                            margin-right: 1px;
                                                                        }

                                                                        .apexcharts-legend-series.apexcharts-no-click {
                                                                            cursor: auto;
                                                                        }

                                                                        .apexcharts-legend .apexcharts-hidden-zero-series,
                                                                        .apexcharts-legend .apexcharts-hidden-null-series {
                                                                            display: none !important;
                                                                        }

                                                                        .apexcharts-inactive-legend {
                                                                            opacity: 0.45;
                                                                        }
                                                                    </style>
                                                                </foreignObject>
                                                                <g id="SvgjsG2577"
                                                                    class="apexcharts-inner apexcharts-graphical"
                                                                    transform="translate(125, 0)">
                                                                    <defs id="SvgjsDefs2576">
                                                                        <clipPath id="gridRectMask2j66rgos">
                                                                            <rect id="SvgjsRect2578" width="0"
                                                                                height="208.33333333333334" x="0" y="0"
                                                                                rx="0" ry="0"
                                                                                opacity="1" stroke-width="0"
                                                                                stroke="none" stroke-dasharray="0"
                                                                                fill="#fff"></rect>
                                                                        </clipPath>
                                                                        <clipPath id="gridRectBarMask2j66rgos">
                                                                            <rect id="SvgjsRect2579" width="4"
                                                                                height="212.33333333333334" x="-2" y="-2"
                                                                                rx="0" ry="0"
                                                                                opacity="1" stroke-width="0"
                                                                                stroke="none" stroke-dasharray="0"
                                                                                fill="#fff"></rect>
                                                                        </clipPath>
                                                                        <clipPath id="gridRectMarkerMask2j66rgos">
                                                                            <rect id="SvgjsRect2580" width="0"
                                                                                height="208.33333333333334" x="0" y="0"
                                                                                rx="0" ry="0"
                                                                                opacity="1" stroke-width="0"
                                                                                stroke="none" stroke-dasharray="0"
                                                                                fill="#fff"></rect>
                                                                        </clipPath>
                                                                        <clipPath id="forecastMask2j66rgos"></clipPath>
                                                                        <clipPath id="nonForecastMask2j66rgos"></clipPath>
                                                                        <filter id="SvgjsFilter2591"
                                                                            filterUnits="userSpaceOnUse" width="200%"
                                                                            height="200%" x="-50%" y="-50%">
                                                                            <feFlood id="SvgjsFeFlood2592"
                                                                                flood-color="#000000" flood-opacity="0.45"
                                                                                result="SvgjsFeFlood2592Out"
                                                                                in="SourceGraphic"></feFlood>
                                                                            <feComposite id="SvgjsFeComposite2593"
                                                                                in="SvgjsFeFlood2592Out" in2="SourceAlpha"
                                                                                operator="in"
                                                                                result="SvgjsFeComposite2593Out">
                                                                            </feComposite>
                                                                            <feOffset id="SvgjsFeOffset2594"
                                                                                dx="1" dy="1"
                                                                                result="SvgjsFeOffset2594Out"
                                                                                in="SvgjsFeComposite2593Out"></feOffset>
                                                                            <feGaussianBlur id="SvgjsFeGaussianBlur2595"
                                                                                stdDeviation="1 "
                                                                                result="SvgjsFeGaussianBlur2595Out"
                                                                                in="SvgjsFeOffset2594Out"></feGaussianBlur>
                                                                            <feMerge id="SvgjsFeMerge2596"
                                                                                result="SvgjsFeMerge2596Out"
                                                                                in="SourceGraphic">
                                                                                <feMergeNode id="SvgjsFeMergeNode2597"
                                                                                    in="SvgjsFeGaussianBlur2595Out">
                                                                                </feMergeNode>
                                                                                <feMergeNode id="SvgjsFeMergeNode2598"
                                                                                    in="[object Arguments]"></feMergeNode>
                                                                            </feMerge>
                                                                            <feBlend id="SvgjsFeBlend2599"
                                                                                in="SourceGraphic"
                                                                                in2="SvgjsFeMerge2596Out" mode="normal"
                                                                                result="SvgjsFeBlend2599Out"></feBlend>
                                                                        </filter>
                                                                        <filter id="SvgjsFilter2604"
                                                                            filterUnits="userSpaceOnUse" width="200%"
                                                                            height="200%" x="-50%" y="-50%">
                                                                            <feFlood id="SvgjsFeFlood2605"
                                                                                flood-color="#000000" flood-opacity="0.45"
                                                                                result="SvgjsFeFlood2605Out"
                                                                                in="SourceGraphic"></feFlood>
                                                                            <feComposite id="SvgjsFeComposite2606"
                                                                                in="SvgjsFeFlood2605Out" in2="SourceAlpha"
                                                                                operator="in"
                                                                                result="SvgjsFeComposite2606Out">
                                                                            </feComposite>
                                                                            <feOffset id="SvgjsFeOffset2607"
                                                                                dx="1" dy="1"
                                                                                result="SvgjsFeOffset2607Out"
                                                                                in="SvgjsFeComposite2606Out"></feOffset>
                                                                            <feGaussianBlur id="SvgjsFeGaussianBlur2608"
                                                                                stdDeviation="1 "
                                                                                result="SvgjsFeGaussianBlur2608Out"
                                                                                in="SvgjsFeOffset2607Out"></feGaussianBlur>
                                                                            <feMerge id="SvgjsFeMerge2609"
                                                                                result="SvgjsFeMerge2609Out"
                                                                                in="SourceGraphic">
                                                                                <feMergeNode id="SvgjsFeMergeNode2610"
                                                                                    in="SvgjsFeGaussianBlur2608Out">
                                                                                </feMergeNode>
                                                                                <feMergeNode id="SvgjsFeMergeNode2611"
                                                                                    in="[object Arguments]"></feMergeNode>
                                                                            </feMerge>
                                                                            <feBlend id="SvgjsFeBlend2612"
                                                                                in="SourceGraphic"
                                                                                in2="SvgjsFeMerge2609Out" mode="normal"
                                                                                result="SvgjsFeBlend2612Out"></feBlend>
                                                                        </filter>
                                                                        <filter id="SvgjsFilter2617"
                                                                            filterUnits="userSpaceOnUse" width="200%"
                                                                            height="200%" x="-50%" y="-50%">
                                                                            <feFlood id="SvgjsFeFlood2618"
                                                                                flood-color="#000000" flood-opacity="0.45"
                                                                                result="SvgjsFeFlood2618Out"
                                                                                in="SourceGraphic"></feFlood>
                                                                            <feComposite id="SvgjsFeComposite2619"
                                                                                in="SvgjsFeFlood2618Out" in2="SourceAlpha"
                                                                                operator="in"
                                                                                result="SvgjsFeComposite2619Out">
                                                                            </feComposite>
                                                                            <feOffset id="SvgjsFeOffset2620"
                                                                                dx="1" dy="1"
                                                                                result="SvgjsFeOffset2620Out"
                                                                                in="SvgjsFeComposite2619Out"></feOffset>
                                                                            <feGaussianBlur id="SvgjsFeGaussianBlur2621"
                                                                                stdDeviation="1 "
                                                                                result="SvgjsFeGaussianBlur2621Out"
                                                                                in="SvgjsFeOffset2620Out"></feGaussianBlur>
                                                                            <feMerge id="SvgjsFeMerge2622"
                                                                                result="SvgjsFeMerge2622Out"
                                                                                in="SourceGraphic">
                                                                                <feMergeNode id="SvgjsFeMergeNode2623"
                                                                                    in="SvgjsFeGaussianBlur2621Out">
                                                                                </feMergeNode>
                                                                                <feMergeNode id="SvgjsFeMergeNode2624"
                                                                                    in="[object Arguments]"></feMergeNode>
                                                                            </feMerge>
                                                                            <feBlend id="SvgjsFeBlend2625"
                                                                                in="SourceGraphic"
                                                                                in2="SvgjsFeMerge2622Out" mode="normal"
                                                                                result="SvgjsFeBlend2625Out"></feBlend>
                                                                        </filter>
                                                                    </defs>
                                                                    <g id="SvgjsG2583" class="apexcharts-pie">
                                                                        <g id="SvgjsG2584"
                                                                            transform="translate(0, 0) scale(1)">
                                                                            <circle id="SvgjsCircle2585" r="0"
                                                                                cx="0" cy="0"
                                                                                fill="transparent"></circle>
                                                                            <g id="SvgjsG2586" class="apexcharts-slices">
                                                                                <g id="SvgjsG2587"
                                                                                    class="apexcharts-series apexcharts-pie-series"
                                                                                    seriesName="Sales" rel="1"
                                                                                    data:realIndex="0">
                                                                                    <path id="SvgjsPath2588"
                                                                                        d="M e -16 4 A -4 -4 0 1 1 3.9657794454952415 -0.5221047688802063 L 0 0 A 0 0 0 1 0 0 0 L e -16 4 z "
                                                                                        fill="rgba(114,57,234,1)"
                                                                                        fill-opacity="1"
                                                                                        stroke-opacity="1"
                                                                                        stroke-linecap="butt"
                                                                                        stroke-width="0"
                                                                                        stroke-dasharray="0"
                                                                                        class="apexcharts-pie-area apexcharts-donut-slice-0"
                                                                                        index="0" j="0"
                                                                                        data:angle="262.5"
                                                                                        data:startAngle="0"
                                                                                        data:strokeWidth="0"
                                                                                        data:value="70"
                                                                                        data:pathOrig="M e -16 4 A -4 -4 0 1 1 3.9657794454952415 -0.5221047688802063 L 0 0 A 0 0 0 1 0 0 0 L e -16 4 z ">
                                                                                    </path>
                                                                                </g>
                                                                                <g id="SvgjsG2600"
                                                                                    class="apexcharts-series apexcharts-pie-series"
                                                                                    seriesName="Sales" rel="2"
                                                                                    data:realIndex="1">
                                                                                    <path id="SvgjsPath2601"
                                                                                        d="M 3.9657794454952415 -0.5221047688802063 A -4 -4 0 0 1 3.00735922991591 2.6373832604002754 L 0 0 A 0 0 0 0 0 0 0 L 3.9657794454952415 -0.5221047688802063 z "
                                                                                        fill="rgba(23,198,83,1)"
                                                                                        fill-opacity="1"
                                                                                        stroke-opacity="1"
                                                                                        stroke-linecap="butt"
                                                                                        stroke-width="0"
                                                                                        stroke-dasharray="0"
                                                                                        class="apexcharts-pie-area apexcharts-donut-slice-1"
                                                                                        index="0" j="1"
                                                                                        data:angle="48.75"
                                                                                        data:startAngle="262.5"
                                                                                        data:strokeWidth="0"
                                                                                        data:value="13"
                                                                                        data:pathOrig="M 3.9657794454952415 -0.5221047688802063 A -4 -4 0 0 1 3.00735922991591 2.6373832604002754 L 0 0 A 0 0 0 0 0 0 0 L 3.9657794454952415 -0.5221047688802063 z ">
                                                                                    </path>
                                                                                </g>
                                                                                <g id="SvgjsG2613"
                                                                                    class="apexcharts-series apexcharts-pie-series"
                                                                                    seriesName="Sales" rel="3"
                                                                                    data:realIndex="2">
                                                                                    <path id="SvgjsPath2614"
                                                                                        d="M 3.00735922991591 2.6373832604002754 A -4 -4 0 0 1 0.5221047688802065 3.9657794454952415 L 0 0 A 0 0 0 0 0 0 0 L 3.00735922991591 2.6373832604002754 z "
                                                                                        fill="rgba(27,132,255,1)"
                                                                                        fill-opacity="1"
                                                                                        stroke-opacity="1"
                                                                                        stroke-linecap="butt"
                                                                                        stroke-width="0"
                                                                                        stroke-dasharray="0"
                                                                                        class="apexcharts-pie-area apexcharts-donut-slice-2"
                                                                                        index="0" j="2"
                                                                                        data:angle="41.25"
                                                                                        data:startAngle="311.25"
                                                                                        data:strokeWidth="0"
                                                                                        data:value="11"
                                                                                        data:pathOrig="M 3.00735922991591 2.6373832604002754 A -4 -4 0 0 1 0.5221047688802065 3.9657794454952415 L 0 0 A 0 0 0 0 0 0 0 L 3.00735922991591 2.6373832604002754 z ">
                                                                                    </path>
                                                                                </g>
                                                                                <g id="SvgjsG2626"
                                                                                    class="apexcharts-series apexcharts-pie-series"
                                                                                    seriesName="Sales" rel="4"
                                                                                    data:realIndex="3">
                                                                                    <path id="SvgjsPath2627"
                                                                                        d="M 0.5221047688802065 3.9657794454952415 A -4 -4 0 0 1 0.0006981316972540385 3.9999999390765164 L 0 0 A 0 0 0 0 0 0 0 L 0.5221047688802065 3.9657794454952415 z "
                                                                                        fill="rgba(248,40,90,1)"
                                                                                        fill-opacity="1"
                                                                                        stroke-opacity="1"
                                                                                        stroke-linecap="butt"
                                                                                        stroke-width="0"
                                                                                        stroke-dasharray="0"
                                                                                        class="apexcharts-pie-area apexcharts-donut-slice-3"
                                                                                        index="0" j="3"
                                                                                        data:angle="7.5"
                                                                                        data:startAngle="352.5"
                                                                                        data:strokeWidth="0"
                                                                                        data:value="2"
                                                                                        data:pathOrig="M 0.5221047688802065 3.9657794454952415 A -4 -4 0 0 1 0.0006981316972540385 3.9999999390765164 L 0 0 A 0 0 0 0 0 0 0 L 0.5221047688802065 3.9657794454952415 z ">
                                                                                    </path>
                                                                                </g>
                                                                                <g id="SvgjsG2589"
                                                                                    class="apexcharts-datalabels"><text
                                                                                        id="SvgjsText2590"
                                                                                        font-family="inherit"
                                                                                        x="-1.5036796149579548"
                                                                                        y="-1.3186916302001377"
                                                                                        text-anchor="middle"
                                                                                        dominant-baseline="auto"
                                                                                        font-size="12px" font-weight="600"
                                                                                        fill="#ffffff"
                                                                                        class="apexcharts-text apexcharts-pie-label"
                                                                                        filter="url(#SvgjsFilter2591)"
                                                                                        style="font-family: inherit;">72.9%</text>
                                                                                </g>
                                                                                <g id="SvgjsG2602"
                                                                                    class="apexcharts-datalabels"><text
                                                                                        id="SvgjsText2603"
                                                                                        font-family="inherit"
                                                                                        x="1.9138806714644179"
                                                                                        y="0.5805693545089242"
                                                                                        text-anchor="middle"
                                                                                        dominant-baseline="auto"
                                                                                        font-size="12px" font-weight="600"
                                                                                        fill="#ffffff"
                                                                                        class="apexcharts-text apexcharts-pie-label"
                                                                                        filter="url(#SvgjsFilter2604)"
                                                                                        style="font-family: inherit;">13.5%</text>
                                                                                </g>
                                                                                <g id="SvgjsG2615"
                                                                                    class="apexcharts-datalabels"><text
                                                                                        id="SvgjsText2616"
                                                                                        font-family="inherit"
                                                                                        x="0.9427934736519957"
                                                                                        y="1.7638425286967099"
                                                                                        text-anchor="middle"
                                                                                        dominant-baseline="auto"
                                                                                        font-size="12px" font-weight="600"
                                                                                        fill="#ffffff"
                                                                                        class="apexcharts-text apexcharts-pie-label"
                                                                                        filter="url(#SvgjsFilter2617)"
                                                                                        style="font-family: inherit;">11.5%</text>
                                                                                </g>
                                                                            </g>
                                                                        </g>
                                                                    </g>
                                                                    <line id="SvgjsLine2628" x1="0"
                                                                        y1="0" x2="0" y2="0"
                                                                        stroke="#b6b6b6" stroke-dasharray="0"
                                                                        stroke-width="1" stroke-linecap="butt"
                                                                        class="apexcharts-ycrosshairs"></line>
                                                                    <line id="SvgjsLine2629" x1="0"
                                                                        y1="0" x2="0" y2="0"
                                                                        stroke-dasharray="0" stroke-width="0"
                                                                        stroke-linecap="butt"
                                                                        class="apexcharts-ycrosshairs-hidden"></line>
                                                                </g>
                                                                <g id="SvgjsG2581" class="apexcharts-datalabels-group"
                                                                    transform="translate(0, 0) scale(1)"></g>
                                                                <g id="SvgjsG2582" class="apexcharts-datalabels-group"
                                                                    transform="translate(0, 0) scale(1)"></g>
                                                            </svg>
                                                            <div class="apexcharts-tooltip apexcharts-theme-dark">
                                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-0"
                                                                    style="order: 1;"><span
                                                                        class="apexcharts-tooltip-marker"
                                                                        style="background-color: rgb(114, 57, 234);"></span>
                                                                    <div class="apexcharts-tooltip-text"
                                                                        style="font-family: inherit; font-size: 12px;">
                                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                                class="apexcharts-tooltip-text-y-label"></span><span
                                                                                class="apexcharts-tooltip-text-y-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-1"
                                                                    style="order: 2;"><span
                                                                        class="apexcharts-tooltip-marker"
                                                                        style="background-color: rgb(23, 198, 83);"></span>
                                                                    <div class="apexcharts-tooltip-text"
                                                                        style="font-family: inherit; font-size: 12px;">
                                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                                class="apexcharts-tooltip-text-y-label"></span><span
                                                                                class="apexcharts-tooltip-text-y-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-2"
                                                                    style="order: 3;"><span
                                                                        class="apexcharts-tooltip-marker"
                                                                        style="background-color: rgb(27, 132, 255);"></span>
                                                                    <div class="apexcharts-tooltip-text"
                                                                        style="font-family: inherit; font-size: 12px;">
                                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                                class="apexcharts-tooltip-text-y-label"></span><span
                                                                                class="apexcharts-tooltip-text-y-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-3"
                                                                    style="order: 4;"><span
                                                                        class="apexcharts-tooltip-marker"
                                                                        style="background-color: rgb(248, 40, 90);"></span>
                                                                    <div class="apexcharts-tooltip-text"
                                                                        style="font-family: inherit; font-size: 12px;">
                                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                                class="apexcharts-tooltip-text-y-label"></span><span
                                                                                class="apexcharts-tooltip-text-y-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                                        </div>
                                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--end::Chart-->

                                                    <!--begin::Labels-->
                                                    <div class="mx-auto">
                                                        <!--begin::Label-->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <!--begin::Bullet-->
                                                            <div class="bullet bullet-dot w-8px h-7px bg-success me-2">
                                                            </div>
                                                            <!--end::Bullet-->

                                                            <!--begin::Label-->
                                                            <div class="fs-8 fw-semibold text-muted">Precent(133)</div>
                                                            <!--end::Label-->
                                                        </div>
                                                        <!--end::Label-->

                                                        <!--begin::Label-->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <!--begin::Bullet-->
                                                            <div class="bullet bullet-dot w-8px h-7px bg-primary me-2">
                                                            </div>
                                                            <!--end::Bullet-->

                                                            <!--begin::Label-->
                                                            <div class="fs-8 fw-semibold text-muted">Illness(9)</div>
                                                            <!--end::Label-->
                                                        </div>
                                                        <!--end::Label-->

                                                        <!--begin::Label-->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <!--begin::Bullet-->
                                                            <div class="bullet bullet-dot w-8px h-7px bg-info me-2"></div>
                                                            <!--end::Bullet-->

                                                            <!--begin::Label-->
                                                            <div class="fs-8 fw-semibold text-muted">Late(2)</div>
                                                            <!--end::Label-->
                                                        </div>
                                                        <!--end::Label-->

                                                        <!--begin::Label-->
                                                        <div class="d-flex align-items-center mb-2">
                                                            <!--begin::Bullet-->
                                                            <div class="bullet bullet-dot w-8px h-7px bg-danger me-2">
                                                            </div>
                                                            <!--end::Bullet-->

                                                            <!--begin::Label-->
                                                            <div class="fs-8 fw-semibold text-muted">Absent(3)</div>
                                                            <!--end::Label-->
                                                        </div>
                                                        <!--end::Label-->
                                                    </div>
                                                    <!--end::Labels-->
                                                </div>
                                                <!--end::Container-->
                                            </div>
                                            <!--end::Wrapper-->
                                        </div>
                                        <!--end::Tap pane-->

                                    </div>
                                    <!--end::Tab Content-->
                                </div>
                                <!--end: Card Body-->
                            </div>
                            <!--end::Chart widget 22-->
                        </div>
                        <!--end::Col-->
                    </div>

                </div>
                <!--end::Row-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->

    </div>
@endsection
