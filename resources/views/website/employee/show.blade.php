@extends('layouts.root.main')

@section('main')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <!-- Header Card -->
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i> Employee Detail
                </h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-card-list me-2"></i>NPK</label>
                            <p class="form-control-plaintext">{{ $employee->npk }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-person me-2"></i>Name</label>
                            <p class="form-control-plaintext">{{ $employee->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-credit-card-2-front me-2"></i>Identity Number</label>
                            <p class="form-control-plaintext">{{ $employee->identity_number }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-calendar me-2"></i>Birthday Date</label>
                            <p class="form-control-plaintext">{{ $employee->birthday_date }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-gender-ambiguous me-2"></i>Gender</label>
                            <p class="form-control-plaintext">{{ $employee->gender }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-building me-2"></i>Company Name</label>
                            <p class="form-control-plaintext">{{ $employee->company_name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-briefcase me-2"></i>Function</label>
                            <p class="form-control-plaintext">{{ $employee->function }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-award me-2"></i>Position Name</label>
                            <p class="form-control-plaintext">{{ $employee->position_name }}</p>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-door-open me-2"></i>Aisin Entry Date</label>
                            <p class="form-control-plaintext">{{ $employee->aisin_entry_date }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-clock-history me-2"></i>Working Period</label>
                            <p class="form-control-plaintext">{{ $employee->working_period }} years</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-diagram-3 me-2"></i>Company Group</label>
                            <p class="form-control-plaintext">{{ $employee->company_group }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-globe2 me-2"></i>Foundation Group</label>
                            <p class="form-control-plaintext">{{ $employee->foundation_group }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-person-badge me-2"></i>Position</label>
                            <p class="form-control-plaintext">{{ $employee->position }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-stars me-2"></i>Grade</label>
                            <p class="form-control-plaintext">{{ $employee->grade }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold"><i class="bi bi-calendar-check me-2"></i>Last Promote Date</label>
                            <p class="form-control-plaintext">{{ $employee->last_promote_date }}</p>
                        </div>
                        @if ($employee->photo)
                        <div class="mb-3">
                            <h5><i class="bi bi-image"></i> Employee Photo</h5>
                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="Employee Photo" class="img-thumbnail shadow" width="200">
                        </div>
                    @endif

                    </div>
                </div>

                <!-- Foto -->

                <!-- Tombol Back -->
                <div class="text-end mt-4">
                    <a href="{{ route('employee.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
