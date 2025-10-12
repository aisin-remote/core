@extends('layouts.root.main')

@section('title', $title ?? 'Employee')

@section('breadcrumbs', $title ?? 'Employee')

@section('main')
    {{-- Success Alert --}}
    @if ($message = session()->pull('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Success!",
                    text: "{{ $message }}",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    {{-- Error Alert --}}
    @if ($message = session()->pull('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "{{ $message }}",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form action="{{ route('employee.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4">Personal Information</h4>
                            <div class="mb-3">
                                <label class="form-label">NPK</label>
                                <input type="text" name="npk" class="form-control" value="{{ old('npk') }}">
                                @error('npk')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Birthday Date</label>
                                <input type="date" name="birthday_date" class="form-control"
                                    value="{{ old('birthday_date') }}">
                                @error('birthday_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="number" name="phone_number" id="phone_number" class="form-control"
                                    value="{{ old('phone_number') }}">
                                @error('phone_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Photo <small class="text-muted">(JPG, PNG, JPEG)</small></label>
                                <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
                                @error('photo')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female
                                    </option>
                                </select>
                                @error('gender')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4">Company Information</h4>
                            <div class="mb-3">
                                <label class="form-label">Join Date</label>
                                <input type="date" name="aisin_entry_date" class="form-control"
                                    value="{{ old('aisin_entry_date') }}">
                                @error('aisin_entry_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Company Group</label>
                                <input type="text" name="company_group" class="form-control"
                                    value="{{ old('company_group') }}">
                                @error('company_group')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" name="email" class="form-control" value="{{ old('email') }}"
                                    placeholder="employee@example.com">
                                <input type="hidden" name="force_email_duplicate" id="force_email_duplicate"
                                    value="0">
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <div class="col-lg-12 mb-3">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <label class="form-label">Company</label>
                                    </label>
                                    <select name="company_name" aria-label="Select a Country" data-control="select2"
                                        data-placeholder="Select Company..." class="form-select form-select-lg fw-semibold">
                                        <option value="">Select Company</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AIIA">
                                            Aisin Indonesia Automotive
                                        </option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AII">
                                            Aisin Indonesia
                                        </option>
                                    </select>
                                </div>
                                @error('company_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <div class="col-lg-12 mb-3">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <label class="form-label">Position</label>
                                    </label>
                                    <select name="position" aria-label="Select a Country" data-control="select2"
                                        data-placeholder="Select Position..."
                                        class="form-select form-select-lg fw-semibold">
                                        <option value="">Select Position</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="Direktur">Direktur</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="GM">General Manager
                                        </option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="Act GM">Act General Manager
                                        </option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="Manager">Manager</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="Act Manager">Act Manager
                                        </option>
                                        <option data-kt-flag="flags/aland-islands.svg" value="Coordinator">Coordinator
                                        </option>
                                        <option data-kt-flag="flags/aland-islands.svg" value="Act Coordinator">Act
                                            Coordinator
                                        </option>
                                        <option data-kt-flag="flags/albania.svg" value="Section Head">Section Head
                                        </option>
                                        <option data-kt-flag="flags/albania.svg" value="Act Section Head">Act Section Head
                                        </option>
                                        <option data-kt-flag="flags/algeria.svg" value="Supervisor">Supervisor</option>
                                        <option data-kt-flag="flags/algeria.svg" value="Act Supervisor">Act Supervisor
                                        </option>
                                        <option data-kt-flag="flags/algeria.svg" value="Leader">Leader</option>
                                        <option data-kt-flag="flags/algeria.svg" value="Act Leader">Act Leader</option>
                                        <option data-kt-flag="flags/algeria.svg" value="JP">JP</option>
                                        <option data-kt-flag="flags/algeria.svg" value="Act JP">Act JP</option>
                                        <option data-kt-flag="flags/algeria.svg" value="Operator">Operator</option>
                                    </select>
                                </div>
                                @error('position')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            @if (auth()->user()->role === 'HRD')
                                <div id="additional-fields" class="mb-5"></div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Grade</label>
                                <select name="grade" class="form-control">
                                    <option value="">-- Select Grade --</option>
                                    @foreach ($grade as $g)
                                        <option value="{{ $g->aisin_grade }}"
                                            {{ old('grade') == $g->aisin_grade ? 'selected' : '' }}>
                                            {{ $g->aisin_grade }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('grade')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Educational Background & Working Experience -->
                <div class="row mt-4">
                    <div class="col-lg-12 mb-6">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4 text-center">Educational Background</h4>
                            <div id="education-container"></div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" onclick="addEducation()">Add More</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4 text-center">Working Experience</h4>
                            <div id="work-experience-container"></div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" onclick="addWorkExperience()">Add
                                    More</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            document.getElementById('phone_number').addEventListener('input', function(e) {
                let val = this.value;

                // Cegah angka lebih dari 14 digit
                if (val.length > 14) {
                    this.value = val.slice(0, 14);
                }
            });
            // Inisialisasi Select2
            $('[name="position"]').select2({
                placeholder: "Select Position...",
                allowClear: true
            });

            // Variabel dari server
            window.subSections = {!! json_encode($subSections) !!};
            window.sections = {!! json_encode($sections) !!};
            window.departments = {!! json_encode($departments) !!};
            window.divisions = {!! json_encode($divisions) !!};
            window.plants = {!! json_encode($plants) !!};

            const positionSelect = $('[name="position"]');
            const additionalFieldsContainer = $('#additional-fields');

            positionSelect.on('change', function() {
                const selectedPosition = $(this).val();
                additionalFieldsContainer.html(''); // Clear

                let label = '';
                let name = '';
                let options = [];

                switch (selectedPosition) {
                    case 'Act Leader':
                    case 'Leader':
                        label = 'Sub Section (as Leader)';
                        name = 'sub_section_id';
                        options = subSections;
                        break;
                    case 'JP':
                    case 'Act JP':
                    case 'Operator':
                        label = 'Sub Section';
                        name = 'sub_section_id';
                        options = subSections;
                        break;
                    case 'Section Head':
                    case 'Act Section Head':
                        label = 'Section';
                        name = 'section_id';
                        options = sections;
                        break;
                    case 'Supervisor':
                    case 'Act Supervisor':
                        label = 'Section';
                        name = 'section_id';
                        options = sections;
                        break;
                    case 'Manager':
                    case 'Act Manager':
                        label = 'Department';
                        name = 'department_id';
                        options = departments;
                        break;
                    case 'Coordinator':
                    case 'Act Coordinator':
                        label = 'Department';
                        name = 'department_id';
                        options = departments;
                        break;
                    case 'GM':
                    case 'Act GM':
                        label = 'Division';
                        name = 'division_id';
                        options = divisions;
                        break;
                    case 'Direktur':
                        label = 'Plant';
                        name = 'plant_id';
                        options = plants;
                        break;
                    default:
                        return;
                }

                const selectHtml = `
                <label class="form-label">${label}</label>
                <select name="${name}" class="form-select form-select-lg fw-semibold">
                    <option value="">Select ${label}</option>
                    ${options.map(option => `<option value="${option.id}">${option.name}</option>`).join('')}
                </select>
            `;

                additionalFieldsContainer.html(selectHtml);
            });

            // Trigger if sudah ada isinya saat load
            if (positionSelect.val()) {
                positionSelect.trigger('change');
            }
        });
    </script>

    <script>
        function addEducation() {
            let container = document.getElementById("education-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("education-entry", "p-3", "rounded", "mt-3", "position-relative");

            newEntry.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-1">
                    <label class="form-label">Degree</label>
                    <select name="level[]" aria-label="Select a Category"
                        data-control="select2"
                        data-placeholder="Select categories..."
                        class="form-select form-select-lg fw-semibold">
                        <option value="">Select Category</option>
                        <option value="SMK">
                            SMK</option>
                        <option value="D3">
                            D3</option>
                        <option value="S1">
                            S1</option>
                        <option value="S2">
                            S2</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Major</label>
                    <input type="text" name="major[]" class="form-control" placeholder="e.g., S1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">University</label>
                    <input type="text" name="institute[]" class="form-control" placeholder="e.g., Universitas Indonesia">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date[]" class="form-control" placeholder="e.g., 2019">
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <div class="w-100">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date[]" class="form-control" placeholder="e.g., 2022">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm ms-2 mt-8" onclick="removeEntry(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

            container.appendChild(newEntry);
        }

        // Fungsi untuk menghapus entry
        function removeEntry(button) {
            button.closest(".education-entry").remove();
        }


        function addWorkExperience() {
            let container = document.getElementById("work-experience-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("work-entry", "p-3", "rounded", "mt-3", "position-relative");

            newEntry.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <input type="text" name="company[]" class="form-control" placeholder="e.g., Human Resource Manager" required>
                </div>
                <div class="col-md-3    ">
                    <label class="form-label">Job Title</label>
                    <input type="text" name="work_position[]" class="form-control" placeholder="e.g., Human Resource Manager" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="work_start_date[]" class="form-control" placeholder="e.g., 2020" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">End Date</label>
                    <input type="date" name="work_end_date[]" class="form-control" placeholder="e.g., Present" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeWorkExperience(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

            container.appendChild(newEntry);
        }

        function removeWorkExperience(button) {
            button.closest('.work-entry').remove();
        }

        function addPromotion() {
            let container = document.getElementById("promotion-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("promotion-entry", "mt-3");
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Previous Position</label>
                            <input type="text" name="previous_position[]" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Position</label>
                            <input type="text" name="current_position[]" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Promotion Date</label>
                            <input type="date" name="last_promotion_date[]" class="form-control" required>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">Remove</button>
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
        }

        function removeEntry(button) {
            button.closest('.promotion-entry, .education-entry').remove();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const emailInput = $('[name="email"]');
            const form = $('form[action="{{ route('employee.store') }}"]');
            // kamu sebenarnya sudah tidak pakai forceFlag untuk kasus "wajib ganti email"
            // tapi biarkan ada tidak masalah
            const forceFlag = $('#force_email_duplicate');

            let lastCheckedEmail = '';
            let lastCheckResult = null;
            let alertedEmail = null; // <=== email yang sudah pernah ditunjukkan alert (agar hanya sekali)
            let blurTimer = null; // <=== untuk debounce

            async function checkEmail(email) {
                if (!email) return {
                    exists: false
                };
                if (email === lastCheckedEmail && lastCheckResult !== null) return lastCheckResult;
                const res = await fetch(
                    `{{ route('employee.checkEmail') }}?email=${encodeURIComponent(email)}`);
                const data = await res.json();
                lastCheckedEmail = email;
                lastCheckResult = data;
                return data;
            }

            function markInvalid(input, message) {
                input.addClass('is-invalid');
                // jika kamu punya invalid-feedback di bawah input, bisa isi di situ juga
                // atau biarkan SweetAlert saja yang tampil
            }

            function clearInvalid(input) {
                input.removeClass('is-invalid');
            }

            // Cek saat BLUR — dengan debounce & guard
            emailInput.on('input', () => {
                // kalau user mengubah email -> reset guard dan invalid state
                alertedEmail = null;
                clearInvalid(emailInput);
            });

            emailInput.on('blur', function() {
                clearTimeout(blurTimer);
                blurTimer = setTimeout(async () => {
                    const email = emailInput.val().trim();
                    forceFlag.val(
                        '0'); // tidak dipakai untuk mode "wajib ganti", tetap reset saja
                    if (!email) return;

                    // kalau email ini sudah pernah di-alert, jangan alert lagi sampai user mengubah value
                    if (email === alertedEmail) return;

                    let data;
                    try {
                        data = await checkEmail(email);
                    } catch (e) {
                        return; // gagal cek → diam
                    }

                    if (data.exists) {
                        alertedEmail = email; // <=== tandai sudah di-alert
                        markInvalid(emailInput);

                        await Swal.fire({
                            title: 'Email already used',
                            html: 'This email is already used. Please enter a different email address.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });

                        // Jangan .focus() yang bisa bikin siklus blur/focus aneh di beberapa browser/UI
                        // Cukup select teks agar user langsung mengetik mengganti
                        emailInput.trigger('select');
                    }
                }, 250);
            });

            // Intercept submit — stop & alert, tapi HANYA kalau belum pernah di-alert untuk value yang sama
            form.on('submit', async function(e) {
                const email = emailInput.val()?.trim();
                if (!email) return; // biar validasi server/HTML yang jalan

                let data;
                try {
                    data = await checkEmail(email);
                } catch (err) {
                    return; // kalau tidak bisa cek, biarkan server yang validasi
                }

                if (data.exists) {
                    e.preventDefault();

                    // kalau email sama & sudah di-alert, jangan spam alert kedua kalinya;
                    // cukup biarkan user mengganti email.
                    if (email !== alertedEmail) {
                        alertedEmail = email;
                        markInvalid(emailInput);

                        await Swal.fire({
                            title: 'Email already used',
                            html: 'This email is already used. Please enter a different email address.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }

                    // seleksi teks agar mudah diganti
                    emailInput.trigger('select');
                    return;
                }
            });
        });
    </script>
    <script>
        $(function() {
            const form = $('form[action="{{ route('employee.store') }}"]');
            const submitBtn = form.find('button[type="submit"]');
            const emailInput = $('[name="email"]');

            function clearErrors() {
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.dynamic-error').remove(); // <div class="text-danger dynamic-error">...</div>
            }

            function placeError(fieldName, messages) {
                // Cari elemen by name (dukungan untuk array: field.index → field[])
                let input = form.find(`[name="${fieldName}"]`);
                if (!input.length && fieldName.includes('.')) {
                    const base = fieldName.split('.')[0];
                    input = form.find(`[name="${base}[]"]`).eq(parseInt(fieldName.split('.')[1], 10));
                }
                if (!input.length) {
                    // fallback: tampilkan di atas tombol submit
                    submitBtn.before(`<div class="text-danger dynamic-error mb-2">${messages[0]}</div>`);
                    return;
                }
                input.addClass('is-invalid');
                const errorDiv = $(`<div class="text-danger dynamic-error mt-1">${messages[0]}</div>`);
                if (input.next('.select2').length) {
                    // jika select2, taruh setelah container select2
                    input.next('.select2').after(errorDiv);
                } else {
                    input.after(errorDiv);
                }
            }

            async function ajaxSubmit(e) {
                e.preventDefault();
                clearErrors();

                // siapkan FormData & headers
                const formData = new FormData(form[0]);
                const token = form.find('input[name="_token"]').val();

                // disable button + spinner
                submitBtn.prop('disabled', true).data('orig', submitBtn.html());
                submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

                try {
                    const res = await fetch(form.attr('action'), {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (res.status === 422) {
                        const json = await res.json(); // { errors: {field: [msg]} }
                        const errs = json.errors || {};
                        // tampilkan per-field
                        Object.keys(errs).forEach(k => placeError(k, errs[k]));

                        // scroll ke error pertama + swal
                        const firstErr = form.find('.dynamic-error').first();
                        if (firstErr.length) {
                            $('html, body').animate({
                                scrollTop: firstErr.offset().top - 120
                            }, 250);
                        }
                        await Swal.fire({
                            title: 'Validation error',
                            text: 'Please fix the highlighted fields.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return; // jangan refresh
                    }

                    if (!res.ok) {
                        const json = await res.json().catch(() => ({}));
                        await Swal.fire({
                            title: 'Error',
                            text: json.error || 'Something went wrong. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    const json = await res.json(); // {message, redirect_url?}
                    await Swal.fire({
                        title: 'Success!',
                        text: json.message || 'Saved.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                    // OPSIONAL: redirect setelah OK (atau tetap di halaman tanpa refresh)
                    if (json.redirect_url) {
                        window.location.href = json.redirect_url;
                    }
                } catch (err) {
                    await Swal.fire({
                        title: 'Network error',
                        text: 'Please check your connection and try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    // restore button
                    submitBtn.prop('disabled', false).html(submitBtn.data('orig'));
                }
            }

            form.on('submit', ajaxSubmit);
        });
    </script>
@endpush
