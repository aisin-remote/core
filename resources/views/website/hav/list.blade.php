@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('main')
<div id="kt_app_content_container" class="app-container container-fluid">
    <div class="app-content container-fluid">
        <!--begin::Tables Widget 11-->
        <div class="card mb-5 mb-xl-8">
            <!--begin::Header-->
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Employee HAV List</span>

                    <span class="text-muted mt-1 fw-semibold fs-7">Over 500 new products</span>
                </h3>
                <div class="card-toolbar">
                    <a href="#" class="btn btn-sm btn-light-primary"> 
                        <i class="ki-duotone ki-plus fs-2"></i>                New Member
                    </a>
                </div>
            </div>
            <!--end::Header-->

            <!--begin::Body-->
            <div class="card-body py-3">
                <!--begin::Table container-->
                <div class="table-responsive">
                    <!--begin::Table-->
                    <table class="table align-middle gs-0 gy-4">
                        <!--begin::Table head-->
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-325px rounded-start">Employee</th>
                                <th class="min-w-125px">NPK</th>
                                <th class="min-w-125px">Department</th>
                                <th class="min-w-200px">Grade</th>
                                <th class="min-w-150px">Last HAV</th>
                                <th class="min-w-200px text-end rounded-end"></th>
                            </tr>
                        </thead>
                        <!--end::Table head-->

                        <!--begin::Table body-->
                        <tbody>
                                <tr>                            
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-50px me-5">
                                                <img src="{{ asset('assets/media/avatars/Arif.jpg') }}" class="" alt=""/>
                                            </div>
                                            
                                            <div class="d-flex justify-content-start flex-column">
                                                <a href="#" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6">Arief Widodo</a>
                                                
                                            </div>
                                        </div>                                
                                    </td>

                                    <td>
                                        <a href="#" class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">001905</a>
                                        
                                    </td>
                                    
                                    <td>
                                        <a href="#" class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">Production Engineering</a>
                                        
                                    </td>

                                    <td>
                                        <a href="#" class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">11A</a>
                                        
                                    </td>
                                    
                                    <td>
                                        <span class="badge badge-light-warning fs-7 fw-bold">Strong Performer</span>
                                    </td>                            

                                    <td class="text-end">                               </a>
                                        
                                        <a href="#" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#kt_modal_create_app">
                                            <i class="fa fa-eye"><span class="path1"></span><span class="path2"></span></i>  

                                        <a href="/hav/update/4" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                            <i class="fa fa-pencil"><span class="path1"></span><span class="path2"></span></i>                                </a>

                                        <a href="#" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                            <i class="fa fa-trash"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>                                </a>
                                    </td>
                                </tr>
                                
                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Table container-->
            </div>
            <!--begin::Body-->
        </div>
        <!--end::Tables Widget 11-->
        
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
                <h2>Arief Widodo</h2>
                <!--end::Modal title-->

                <!--begin::Close-->
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body py-lg-10 px-lg-10">
                <!--begin::Stepper-->
                <div class="stepper stepper-pills stepper-column d-flex flex-column flex-xl-row flex-row-fluid" id="kt_modal_create_app_stepper">

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

                
            <span class="ms-1"  data-bs-toggle="tooltip" title="Select your app category" >
	        <i class="ki-duotone ki-information-5 text-gray-500 fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>            </label>
            <!--end::Label-->

            <!--begin:Options-->
            <div class="fv-row">
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-compass fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">Vision & Business Sense</span>
                        </span>
                        <!--end:Info-->

                        
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>3</h3>
                    </span>
                    <!--end:Input-->
                </label>
                <!--end::Option-->
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-element-11 fs-1 text-danger"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">Customer Focus</span>

                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->

                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>2</h3>
                    </span>
                    <!--end:Input-->
                </label>
                <!--end::Option-->

                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-timer fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">Interpersonal Skill</span>
                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>3</h3>
                    </span>
                    <!--end:Input-->

                </label>
                <!--end::Option-->
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-timer fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">Analysis & Judgment</span>
                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>2</h3>
                    </span>
                    <!--end:Input-->

                </label>
                <!--end::Option-->
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-timer fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">Planning & Driving Action</span>
                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>2</h3>
                    </span>
                    <!--end:Input-->

                </label>
                <!--end::Option-->
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-timer fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">Leading & Motivating</span>
                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>3</h3>
                    </span>
                    <!--end:Input-->

                </label>
                <!--end::Option-->
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-timer fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">Teamwork</span>
                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>3</h3>
                    </span>
                    <!--end:Input-->

                </label>
                <!--end::Option-->
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-timer fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">Drive & Courage</span>
                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>3</h3>
                    </span>
                    <!--end:Input-->

                </label>
                <!--end::Option-->
                
            </div>
            <!--end:Options-->
        </div>
        <!--end::Input group-->
    </div>
    <div class="w-100"  style="margin-left: 10px;">
        

        <!--begin::Input group-->
        <div class="fv-row">
            <!--begin::Label-->
            <label class="d-flex align-items-center fs-5 fw-semibold mb-4">
                <span class="required">PK Last 3 Year</span>

                
            <span class="ms-1"  data-bs-toggle="tooltip" title="Select your app category" >
	        <i class="ki-duotone ki-information-5 text-gray-500 fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>            </label>
            <!--end::Label-->

            <!--begin:Options-->
            <div class="fv-row">
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-danger">
                                <i class="ki-duotone ki-compass fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">2022</span>
                        </span>
                        <!--end:Info-->

                        
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>BS</h3>
                    </span>
                    <!--end:Input-->
                </label>
                <!--end::Option-->
                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-danger">
                                <i class="ki-duotone ki-element-11 fs-1 text-danger"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">2023</span>

                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->

                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>BS</h3>
                    </span>
                    <!--end:Input-->
                </label>
                <!--end::Option-->

                <!--begin:Option-->
                <label class="d-flex flex-stack mb-5 cursor-pointer">
                    <!--begin:Label-->
                    <span class="d-flex align-items-center me-2">
                        <!--begin:Icon-->
                        <span class="symbol symbol-50px me-6">
                            <span class="symbol-label bg-light-danger">
                                <i class="ki-duotone ki-timer fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>                            </span>
                        </span>
                        <!--end:Icon-->

                        <!--begin:Info-->
                        <span class="d-flex flex-column">
                            <span class="fw-bold fs-6">2024</span>
                        </span>
                        <!--end:Info-->
                    </span>
                    <!--end:Label-->


                    <!--begin:Input-->
                    <span class="form-check form-check-custom form-check-solid">
                        <h3>BS</h3>
                    </span>
                    <!--end:Input-->

                </label>
                <!--end::Option-->
                
            </div>
            <!--end:Options-->
        </div>
        <!--end::Input group-->
    </div>
</div>


@endsection


@push('scripts')<!-- jQuery dulu -->
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
            ajax: '{{ route("hav.ajax.list") }}',
            columns: [
                { data: 'npk', name: 'npk' },
                { data: 'nama', name: 'nama' },
                { data: 'status', name: 'status' }
            ]
        });
    });
</script>
@endpush
