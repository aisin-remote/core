@extends('layouts.root.manage')

@section('title', 'Edit IDP')

@section('toolbar')
    <div class="idp-header">
        <h1 class="idp-title">Individual Development Plan</h1>
        <a href="{{ url()->previous() }}" class="btn btn-dark">Cancel</a>
    </div>
@endsection

@section('main')
    @php
        // Defaults (English)
        $categories = $categories ?? [
            'On Job Development',
            'Self Development',
            'Shadowing',
            'Mentoring',
            'Training',
            'Feedback',
        ];
        $statusMap = $statusMap ?? [
            0 => 'Draft',
            1 => 'Submitted',
            2 => 'In Review',
            3 => 'Approved',
            4 => 'Completed',
        ];
        $alcs = $alcs ?? [
            1 => 'Vision & Business Sense',
            2 => 'Customer Focus',
            3 => 'Interpersonal Skill',
            4 => 'Analysis & Judgment',
            5 => 'Planning & Driving Action',
            6 => 'Leading & Motivating',
            7 => 'Teamwork',
            8 => 'Drive & Courage',
        ];

        // Employee summary from assessment relation or aliases
        $emp = $idp->assessment->employee ?? null;
        $empName = $emp->name ?? ($idp->employee_name ?? '—');
        $empCompany = $emp->company_name ?? ($idp->employee_company_name ?? '—');
        $empPosition = $emp->position ?? ($idp->employee_position ?? '—');
    @endphp

    <div class="app-container container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-2">Please recheck your inputs:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            {{-- LEFT: Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('idp.update', $idp->id) }}" class="needs-validation" novalidate>
                    @csrf @method('PUT')
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between">
                            <div class="fs-5 fw-semibold mb-0">IDP Form</div>
                        </div>
                        <div class="card-body">

                            {{-- Category --}}
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="" disabled {{ old('category', $idp->category) ? '' : 'selected' }}>
                                        Select a category
                                    </option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}" @selected(old('category', $idp->category) === $cat)>{{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Category is required.</div>
                                @error('category')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- ALC --}}
                            <div class="mb-3">
                                <label class="form-label">ALC <span class="text-danger">*</span></label>
                                <select name="alc_id" class="form-select" required>
                                    <option value="" disabled {{ old('alc_id', $idp->alc_id) ? '' : 'selected' }}>
                                        Select an ALC
                                    </option>
                                    @foreach ($alcs as $aid => $aname)
                                        <option value="{{ $aid }}" @selected((int) old('alc_id', $idp->alc_id) === (int) $aid)>
                                            {{ $aname }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">ALC is required.</div>
                                @error('alc_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Program --}}
                            <div class="mb-3">
                                <label class="form-label">Development Program <span class="text-danger">*</span></label>
                                <input type="text" name="development_program" class="form-control" maxlength="160"
                                    data-max="160" value="{{ old('development_program', $idp->development_program) }}"
                                    required>
                                <div class="form-text"><span class="char-left">0</span> characters left (max 160).</div>
                                <div class="invalid-feedback">Development program is required.</div>
                                @error('development_program')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Target --}}
                            <div class="mb-3">
                                <label class="form-label">Development Target <span class="text-danger">*</span></label>
                                <textarea name="development_target" class="form-control" rows="6" maxlength="1200" data-max="1200" required>{{ old('development_target', $idp->development_target) }}</textarea>
                                <div class="form-text"><span class="char-left">0</span> characters left (max 1200).</div>
                                <div class="invalid-feedback">Development target is required.</div>
                                @error('development_target')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Date --}}
                            <div class="mb-3">
                                <label class="form-label">Target Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ old('date', \Illuminate\Support\Str::of($idp->date)->substr(0, 10)) }}"
                                    required>
                                <div class="invalid-feedback">Date is required.</div>
                                @error('date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    @foreach ($statusMap as $val => $label)
                                        <option value="{{ $val }}" @selected((int) old('status', $idp->status) === (int) $val)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ url()->previous() }}" class="btn btn-light">Cancel</a>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- RIGHT: Employee Summary + Delete --}}
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="fs-6 fw-semibold mb-0">Employee Summary</div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Name</div>
                            <div class="fs-6 fw-semibold">{{ $empName }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Company</div>
                            <div class="fs-6 fw-semibold">{{ $empCompany }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Position</div>
                            <div class="fs-6 fw-semibold">{{ $empPosition }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Assessment</div>
                            <div class="fs-6">
                                #{{ $idp->assessment_id }} &middot; ALC:
                                <span class="fw-semibold">{{ $alcs[$idp->alc_id] ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Delete --}}
                <div class="card mt-4 border-danger-subtle">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="fs-6 fw-semibold text-danger mb-0">Delete IDP</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">This action cannot be undone.</p>

                        {{-- NOTE: make sure your route expects the primary key ($idp->id) --}}
                        <form id="delete-idp-form" method="POST" action="{{ route('idp.destroy', $idp->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" id="btn-delete-idp" class="btn btn-outline-danger w-100">
                                Delete IDP
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Bootstrap validation
        (function() {
            'use strict';
            document.querySelectorAll('.needs-validation').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Character counters
        document.querySelectorAll('[data-max]').forEach(function(el) {
            const max = parseInt(el.getAttribute('data-max'), 10) || 0;
            const hint = el.parentElement.querySelector('.form-text .char-left');
            const update = () => {
                const left = Math.max(0, max - (el.value?.length || 0));
                if (hint) hint.textContent = left;
            };
            el.addEventListener('input', update);
            update();
        });

        // Autosize textarea
        document.querySelectorAll('textarea').forEach(function(ta) {
            const resize = () => {
                ta.style.height = 'auto';
                ta.style.height = (ta.scrollHeight + 2) + 'px';
            };
            ta.addEventListener('input', resize);
            resize();
        });
    </script>

    <style>
        .card-body,
        .form-control,
        .form-select,
        .btn {
            font-size: 1.02rem;
        }

        .form-text {
            font-size: .9rem;
        }

        .form-control,
        .form-select,
        .btn {
            border-radius: .75rem;
        }

        .idp-header {
            display: flex;
            flex: content;
            align-items: center;
            justify-content: space-between;
            gap: .75rem
        }

        .idp-title {
            margin: 0;
            font-weight: 600;
        }
    </style>

    {{-- SweetAlert confirm for delete (Enter cancels) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btn-delete-idp');
            const form = document.getElementById('delete-idp-form');
            if (!btn || !form) return;

            btn.addEventListener('click', function() {
                const opts = {
                    title: 'Delete IDP?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    allowEnterKey: true,
                    allowEscapeKey: true,
                };

                if (window.Swal) {
                    Swal.fire(opts).then((res) => {
                        if (res.isConfirmed) form.submit();
                    });
                } else {
                    if (confirm('Are you sure you want to delete this IDP?')) form.submit();
                }
            });
        });
    </script>
@endpush
