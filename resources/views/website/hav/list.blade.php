@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">HAV List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-end">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                    role="tablist" style="cursor:pointer">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 active filter-tab"" data-filter="all">Show All</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="Manager">Manager</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="Supervisor">Supervisor</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="Leader">Leader</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="JP">JP</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="Operator">Operator</a>
                    </li>
                </ul>
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            {{-- <th>Photo</th> --}}
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Last HAV</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-start">
                            <td>1</td>
                            {{-- <td class="text-center"> --}}
                            {{-- <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                alt="Employee Photo" class="rounded" width="40" height="40"
                                style="object-fit: cover;"> --}}
                            {{-- </td> --}}
                            <td>4239</td>
                            <td>Herizal Arfiansyah</td>
                            <td>AII</td>
                            <td>Manager</td>
                            <td>MIS</td>
                            <td>10A</td>
                            <td><span class="badge badge-light-warning fs-7 fw-bold">Career Person</span></td>
                            <td class="text-center">
                                <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_create_app">
                                    <i class="bi bi-eye"></i> Summary
                                </a>
                            </td>
                        </tr>
                        {{-- @forelse ($employees as $index => $employee)
                        <tr data-position="{{ $employee->position }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="text-center">
                                <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                    alt="Employee Photo" class="rounded" width="40" height="40"
                                    style="object-fit: cover;">
                            </td>
                            <td>{{ $employee->npk }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->company_name }}</td>
                            <td>{{ $employee->position }}</td>
                            <td>{{ $employee->departments->first()->name }}</td>
                            <td>{{ $employee->grade }}</td>
                            <td>{{ \Carbon\Carbon::parse($employee->birthday_date)->age }}</td>
                            <td class="text-center">
                                <a href="{{ route('employee.show', $employee->npk) }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i> Summary
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No employees found</td>
                        </tr>
                    @endforelse --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="modal fade" id="kt_modal_create_app" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-900px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2>Herizal Arfiansyah</h2>
                    <!--end::Modal title-->

                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->

                <!--begin::Modal body-->
                <div class="modal-body py-lg-10 px-lg-10">
                    <!--begin::Stepper-->
                    <div class="stepper stepper-pills stepper-column d-flex flex-column flex-xl-row flex-row-fluid"
                        id="kt_modal_create_app_stepper">

                        <!--begin::Content-->
                        <div class="flex-row-fluid py-lg-5 px-lg-15">
                            <!--begin::Form-->
                            <form class="form" novalidate="novalidate" id="kt_modal_create_app_form">
                                <!--begin::Step 1-->
                                <div class="current" data-kt-stepper-element="content">
                                    <div class="w-100" style="margin-right: 10px;">

                                        <!--begin::Input group-->
                                        <div class="fv-row">
                                            <!--begin::Label-->
                                            <label class="d-flex align-items-center fs-5 fw-semibold mb-4">
                                                <span class="required">Astra Leadership Competency Score</span>


                                                <span class="ms-1" data-bs-toggle="tooltip"
                                                    title="Select your app category">
                                                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6"><span
                                                            class="path1"></span><span class="path2"></span><span
                                                            class="path3"></span></i></span> </label>
                                            <!--end::Label-->

                                            <!--begin:Options-->
                                            <div class="fv-row">
                                                <table class="table table-bordered" border="1" cellpadding="8"
                                                    cellspacing="0">
                                                    <thead>
                                                        <tr>
                                                            <th>ALC</th>
                                                            <th>Score</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Vision & Business Sense</td>
                                                            <td>2</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Customer Focus</td>
                                                            <td>3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Interpersonal Skill</td>
                                                            <td>3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Analysis & Judgment</td>
                                                            <td>3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Planning & Driving Action</td>
                                                            <td>3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Leading & Motivating</td>
                                                            <td>3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Teamwork</td>
                                                            <td>3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Drive & Courage</td>
                                                            <td>3</td>
                                                        </tr>
                                                    </tbody>
                                                </table>


                                            </div>
                                            <!--end:Options-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="w-100" style="margin-left: 10px;">

                                        <label class="d-flex align-items-center fs-5 fw-semibold mb-4">
                                            <span class="required">PK Last 3 Years</span>


                                            <span class="ms-1" data-bs-toggle="tooltip"
                                                title="Select your app category">
                                                <i class="ki-duotone ki-information-5 text-gray-500 fs-6"><span
                                                        class="path1"></span><span class="path2"></span><span
                                                        class="path3"></span></i></span> </label>


                                        <!--begin::Input group-->
                                        <div class="fv-row">

                                            <table class="table table-bordered" border="1" cellpadding="8"
                                                cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>PK</th>
                                                        <th>Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>2022</td>
                                                        <td>B+</td>
                                                    </tr>
                                                    <tr>
                                                        <td>2023</td>
                                                        <td>B+</td>
                                                    </tr>
                                                    <tr>
                                                        <td>2024</td>
                                                        <td>B+</td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <!-- jQuery dulu -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#hav-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('hav.ajax.list') }}',
                columns: [{
                        data: 'npk',
                        name: 'npk'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }
                ]
            });
        });
    </script>
@endpush
