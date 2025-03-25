@extends('layouts.root.main')

@section('title', $title ?? 'Emp Competency')
@section('breadcrumbs', $title ?? 'Emp Competency')

@section('main')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Emp Competency List</h3>
                    <a href="{{ route('emp_competency.create') }}" class="btn btn-primary ms-3">
                        <i class="fas fa-plus"></i> Add
                    </a>
            </div>
            <div class="card-body">
                <!-- Search Input -->
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search..." onkeyup="searchData()">
                </div>
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="empCompetencyTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>User</th>
                            <th>Group Competency</th>
                            <th>Competency</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Weight</th>
                            <th>Plan</th>
                            <th>Act</th>
                            <th>Progress</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="empCompetencyTableBody">
                        <tr>
                            <td>1</td>
                            <td>Tatang</td>
                            <td>Basic</td>
                            <td>Organizational Awareness</td>
                            <td>Operator</td>
                            <td>ENG</td>
                            <td>2</td>
                            <td>3</td>
                            <td>3</td>
                            <td>Selesai</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm" onclick="showDetail('Tatang','Basic','Organizational Awareness','Operator','ENG','2','3','3','Selesai')">
                                    <i class="bi bi-eye"></i> Show
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Daniel</td>
                            <td>Functional</td>
                            <td>Journal</td>
                            <td>ACC</td>
                            <td>ACCOUNTING</td>
                            <td>2</td>
                            <td>4</td>
                            <td>3</td>
                            <td>Belum Selesai</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm" onclick="showDetail('Daniel','Functional','Journal','ACC','ACCOUNTING','2','4','3','Belum Selesai')">
                                    <i class="bi bi-eye"></i> Show
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Fungsi search buat filter data tabel
        function searchData() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let table = document.getElementById("empCompetencyTable");
            let trs = table.getElementsByTagName("tr");
            
            for (let i = 1; i < trs.length; i++) {
                let tds = trs[i].getElementsByTagName("td");
                let match = false;
                for (let j = 0; j < tds.length; j++) {
                    if (tds[j].innerText.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
                trs[i].style.display = match ? "" : "none";
            }
        }

        // Tampilkan detail data dengan SweetAlert
        function showDetail(user, group, competency, role, department, weight, plan, act, progress) {
            Swal.fire({
                title: 'Emp Competency Detail',
                width: '600px',
                html: `<p><strong>User :</strong> ${user}</p>
                       <p><strong>Group Competency :</strong> ${group}</p>
                       <p><strong>Competency :</strong> ${competency}</p>
                       <p><strong>Role :</strong> ${role}</p>
                       <p><strong>Department :</strong> ${department}</p>
                       <p><strong>Weight :</strong> ${weight}</p>
                       <p><strong>Plan :</strong> ${plan}</p>
                       <p><strong>Act :</strong> ${act}</p>
                       <p><strong>Progress :</strong> ${progress}</p>`,
                icon: 'info',
                confirmButtonText: 'Close'
            });
        }

        // Tambah data Emp Competency
        document.getElementById('btnAdd').addEventListener('click', function() {
            Swal.fire({
                title: 'Add Emp Competency',
                width: '600px',
                html:
                    '<input id="swal-input1" class="swal2-input" placeholder="User">' +
                    '<input id="swal-input2" class="swal2-input" placeholder="Group Competency">' +
                    '<input id="swal-input3" class="swal2-input" placeholder="Competency">' +
                    '<input id="swal-input4" class="swal2-input" placeholder="Role">' +
                    '<input id="swal-input5" class="swal2-input" placeholder="Department">' +
                    '<input id="swal-input6" class="swal2-input" placeholder="Weight">' +
                    '<input id="swal-input7" class="swal2-input" placeholder="Plan">' +
                    '<input id="swal-input8" class="swal2-input" placeholder="Act">' +
                    '<input id="swal-input9" class="swal2-input" placeholder="Progress">',
                focusConfirm: false,
                showCancelButton: true,
                preConfirm: () => {
                    const user = document.getElementById('swal-input1').value;
                    const group = document.getElementById('swal-input2').value;
                    const competency = document.getElementById('swal-input3').value;
                    const role = document.getElementById('swal-input4').value;
                    const department = document.getElementById('swal-input5').value;
                    const weight = document.getElementById('swal-input6').value;
                    const plan = document.getElementById('swal-input7').value;
                    const act = document.getElementById('swal-input8').value;
                    const progress = document.getElementById('swal-input9').value;
                    if (!user || !group || !competency || !role || !department || !weight || !plan || !act || !progress) {
                        Swal.showValidationMessage('Semua field harus diisi');
                        return false;
                    }
                    return { user, group, competency, role, department, weight, plan, act, progress };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    addRow(result.value);
                    Swal.fire('Added!', 'Data berhasil ditambahkan', 'success');
                }
            });
        });

        // Fungsi untuk menambah baris baru di tabel
        function addRow(data) {
            const tableBody = document.getElementById("empCompetencyTableBody");
            const newRow = tableBody.insertRow();
            const rowCount = tableBody.rows.length;
            newRow.innerHTML = `
                <td>${rowCount}</td>
                <td>${data.user}</td>
                <td>${data.group}</td>
                <td>${data.competency}</td>
                <td>${data.role}</td>
                <td>${data.department}</td>
                <td>${data.weight}</td>
                <td>${data.plan}</td>
                <td>${data.act}</td>
                <td>${data.progress}</td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-info btn-sm" onclick="showDetail('${data.user}', '${data.group}', '${data.competency}', '${data.role}', '${data.department}', '${data.weight}', '${data.plan}', '${data.act}', '${data.progress}')">
                        <i class="bi bi-eye"></i> Show
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </td>
            `;
            updateRowNumbers();
        }

        // Fungsi untuk mengedit baris
        function editRow(btn) {
            const row = btn.parentNode.parentNode;
            const user = row.cells[1].innerText;
            const group = row.cells[2].innerText;
            const competency = row.cells[3].innerText;
            const role = row.cells[4].innerText;
            const department = row.cells[5].innerText;
            const weight = row.cells[6].innerText;
            const plan = row.cells[7].innerText;
            const act = row.cells[8].innerText;
            const progress = row.cells[9].innerText;

            Swal.fire({
                title: 'Edit Emp Competency',
                width: '600px',
                html:
                    `<input id="swal-input1" class="swal2-input" placeholder="User" value="${user}">` +
                    `<input id="swal-input2" class="swal2-input" placeholder="Group Competency" value="${group}">` +
                    `<input id="swal-input3" class="swal2-input" placeholder="Competency" value="${competency}">` +
                    `<input id="swal-input4" class="swal2-input" placeholder="Role" value="${role}">` +
                    `<input id="swal-input5" class="swal2-input" placeholder="Department" value="${department}">` +
                    `<input id="swal-input6" class="swal2-input" placeholder="Weight" value="${weight}">` +
                    `<input id="swal-input7" class="swal2-input" placeholder="Plan" value="${plan}">` +
                    `<input id="swal-input8" class="swal2-input" placeholder="Act" value="${act}">` +
                    `<input id="swal-input9" class="swal2-input" placeholder="Progress" value="${progress}">`,
                focusConfirm: false,
                showCancelButton: true,
                preConfirm: () => {
                    const newUser = document.getElementById('swal-input1').value;
                    const newGroup = document.getElementById('swal-input2').value;
                    const newCompetency = document.getElementById('swal-input3').value;
                    const newRole = document.getElementById('swal-input4').value;
                    const newDepartment = document.getElementById('swal-input5').value;
                    const newWeight = document.getElementById('swal-input6').value;
                    const newPlan = document.getElementById('swal-input7').value;
                    const newAct = document.getElementById('swal-input8').value;
                    const newProgress = document.getElementById('swal-input9').value;
                    if (!newUser || !newGroup || !newCompetency || !newRole || !newDepartment || !newWeight || !newPlan || !newAct || !newProgress) {
                        Swal.showValidationMessage('Semua field harus diisi');
                        return false;
                    }
                    return { newUser, newGroup, newCompetency, newRole, newDepartment, newWeight, newPlan, newAct, newProgress };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    row.cells[1].innerText = result.value.newUser;
                    row.cells[2].innerText = result.value.newGroup;
                    row.cells[3].innerText = result.value.newCompetency;
                    row.cells[4].innerText = result.value.newRole;
                    row.cells[5].innerText = result.value.newDepartment;
                    row.cells[6].innerText = result.value.newWeight;
                    row.cells[7].innerText = result.value.newPlan;
                    row.cells[8].innerText = result.value.newAct;
                    row.cells[9].innerText = result.value.newProgress;
                    Swal.fire('Updated!', 'Data berhasil diupdate', 'success');
                }
            });
        }

        // Fungsi untuk menghapus baris
        function deleteRow(btn) {
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data akan dihapus sementara",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const row = btn.parentNode.parentNode;
                    row.parentNode.removeChild(row);
                    updateRowNumbers();
                    Swal.fire('Deleted!', 'Data berhasil dihapus', 'success');
                }
            });
        }

        // Update nomor baris tiap kali ada penambahan/hapus data
        function updateRowNumbers() {
            const tableBody = document.getElementById("empCompetencyTableBody");
            for (let i = 0; i < tableBody.rows.length; i++) {
                tableBody.rows[i].cells[0].innerText = i + 1;
            }
        }
    </script>
@endpush
