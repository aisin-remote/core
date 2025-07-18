@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Change Password' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Change Password' }}
@endsection

@section('main')
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Sukses!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ url('/') }}";
                    }
                });
            });
        </script>
    @endif

    <div class="app-container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-light-primary border-0 cursor-pointer">
                        <h5 class="card-title mb-0">
                            @if (auth()->user()->is_first_login)
                                {{ __('Set New Password') }}
                            @else
                                {{ __('Change Password') }}
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('change-password.post') }}" id="passwordForm">
                            @csrf

                            @unless (auth()->user()->is_first_login)
                                <div class="mb-3 row">
                                    <label for="current_password" class="col-md-4 col-form-label">
                                        {{ __('Current Password') }}
                                    </label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input id="current_password" type="password"
                                                class="form-control @error('current_password') is-invalid @enderror"
                                                name="current_password" autocomplete="current-password">
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <div class="invalid-feedback d-block">
                                                {{-- {{ $message }} --}}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            @endunless

                            <div class="mb-3 row">
                                <label for="new_password" class="col-md-4 col-form-label">
                                    {{ __('New Password') }}
                                </label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input id="new_password" type="password"
                                            class="form-control @error('new_password') is-invalid @enderror"
                                            name="new_password" required autocomplete="new-password">
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text">
                                        <small>Password must meet the following requirements:</small>
                                        <ul class="list-unstyled">
                                            <li class="text-danger" id="length"><i class="fas fa-check-circle"></i>
                                                Minimum 8 characters</li>
                                            <li class="text-danger" id="uppercase"><i class="fas fa-check-circle"></i>
                                                At least 1 uppercase letter</li>
                                            <li class="text-danger" id="number"><i class="fas fa-check-circle"></i>
                                                At least 1 number</li>
                                            <li class="text-danger" id="special"><i class="fas fa-check-circle"></i>
                                                At least 1 special character</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="new_password_confirmation" class="col-md-4 col-form-label">
                                    {{ __('Confirm New Password') }}
                                </label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input id="new_password_confirmation" type="password"
                                            class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                            name="new_password_confirmation" required autocomplete="new-password">
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('new_password_confirmation')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                        <i class="fas fa-key me-1"></i>
                                        @if (auth()->user()->is_first_login)
                                            {{ __('Save Password') }}
                                        @else
                                            {{ __('Change Password') }}
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('new_password_confirmation');
            const submitBtn = document.getElementById('submitBtn');
            const currentPassword = document.getElementById('current_password');

            // Password requirements elements
            const lengthReq = document.getElementById('length');
            const uppercaseReq = document.getElementById('uppercase');
            const numberReq = document.getElementById('number');
            const specialReq = document.getElementById('special');

            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(function(button) {
                button.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            function validatePassword() {
                const password = newPassword.value;
                let isValid = true;

                // Validate length
                if (password.length >= 8) {
                    lengthReq.classList.remove('text-danger');
                    lengthReq.classList.add('text-success');
                } else {
                    lengthReq.classList.remove('text-success');
                    lengthReq.classList.add('text-danger');
                    isValid = false;
                }

                // Validate uppercase
                if (/[A-Z]/.test(password)) {
                    uppercaseReq.classList.remove('text-danger');
                    uppercaseReq.classList.add('text-success');
                } else {
                    uppercaseReq.classList.remove('text-success');
                    uppercaseReq.classList.add('text-danger');
                    isValid = false;
                }

                // Validate number
                if (/\d/.test(password)) {
                    numberReq.classList.remove('text-danger');
                    numberReq.classList.add('text-success');
                } else {
                    numberReq.classList.remove('text-success');
                    numberReq.classList.add('text-danger');
                    isValid = false;
                }

                // Validate special character
                if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                    specialReq.classList.remove('text-danger');
                    specialReq.classList.add('text-success');
                } else {
                    specialReq.classList.remove('text-success');
                    specialReq.classList.add('text-danger');
                    isValid = false;
                }

                // Validate confirmation
                if (password !== confirmPassword.value) {
                    isValid = false;
                }

                // For non-first login, check current password
                if (currentPassword && !currentPassword.value) {
                    isValid = false;
                }

                submitBtn.disabled = !isValid;
            }

            newPassword.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
            if (currentPassword) {
                currentPassword.addEventListener('input', validatePassword);
            }
        });
    </script>

    <style>
        .toggle-password {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .invalid-feedback.d-block {
            display: block !important;
        }
    </style>
@endpush
