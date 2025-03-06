@extends('layouts.root.main')

@section('main')
    <div class="container mt-4">
        <div class="card shadow-sm p-4">
            <h4 class="fw-bold mb-4">Employee Profile</h4>
            <div class="row">
                <!-- Kiri: Foto Profil -->
                <div class="col-md-3 text-center">
                    <p class="fw-bold">Profile Picture</p>
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="Employee Photo"A
                    class="shadow img-fluid" width="500">
                </div>

                <!-- Kanan: Detail Profil -->
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="fw-bold">Employee Name</label>
                            <input type="text" class="form-control" value="{{ $employee->name }}" readonly>

                            <label class="fw-bold mt-2">Identity No</label>
                            <input type="text" class="form-control" value="{{ $employee->identity_number }}" readonly>

                            <label class="fw-bold mt-2">No. KTP</label>
                            <input type="text" class="form-control" value="{{ $employee->npk }}" readonly>

                            <label class="fw-bold mt-2">Gender</label>
                            <div>
                                <input type="radio" {{ $employee->gender == 'Male' ? 'checked' : '' }}> Male
                                <input type="radio" {{ $employee->gender == 'Female' ? 'checked' : '' }}> Female
                            </div>

                            <label class="fw-bold mt-2">Birth Date</label>
                            <input type="text" class="form-control" value="{{ $employee->birthday_date }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold">Aisin Entry Date</label>
                            <input type="text" class="form-control" value="{{ $employee->aisin_entry_date }}" readonly>

                            <label class="fw-bold mt-2">Working Period</label>
                            <div class="d-flex">
                                <input type="text" class="form-control" value="{{ $employee->working_period }}" readonly>
                                <span class="ms-2 align-self-center">Years</span>
                            </div>

                            <label class="fw-bold mt-2">Company Group</label>
                            <input type="text" class="form-control" value="{{ $employee->company_group }}" readonly>

                            <label class="fw-bold mt-2">Function Group</label>
                            <input type="text" class="form-control" value="{{ $employee->function }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="fw-bold">Company Name</label>
                    <input type="text" class="form-control" value="{{ $employee->company_name }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Position</label>
                    <input type="text" class="form-control" value="{{ $employee->position }}" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="fw-bold">Position Name</label>
                    <input type="text" class="form-control" value="{{ $employee->position_name }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Grade</label>
                    <input type="text" class="form-control" value="{{ $employee->grade }}" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="fw-bold">Last Promote Date</label>
                    <input type="text" class="form-control" value="{{ $employee->last_promote_date }}" readonly>
                </div>
            </div>

            <!-- Tombol Back -->
            <div class="text-end mt-4">
                <a href="{{ route('employee.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Back
                </a>
            </div>
        </div>
    </div>
@endsection
