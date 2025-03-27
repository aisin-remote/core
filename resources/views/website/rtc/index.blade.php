@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@section('main')
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content  flex-column-fluid ">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container  container-fluid ">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Division List</h3>
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
                            <a href="{{ route('employee.create') }}" class="btn btn-primary me-3">
                                <i class="fas fa-plus"></i>
                                Add
                            </a>
                            <button type="button" class="btn btn-info me-3" data-bs-toggle="modal"
                                data-bs-target="#importModal">
                                <i class="fas fa-upload"></i>
                                Import
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th class="text-center">Division</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (request()->path() == 'rtc/aii')
                                    <tr>
                                        <td class="text-center">1.</td>
                                        <td class="text-center">Engineering & Production</td>
                                        <td class="text-center">
                                            <a href="{{ route('rtc.detail') }}" class="btn btn-primary btn-sm">Detail</a>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
