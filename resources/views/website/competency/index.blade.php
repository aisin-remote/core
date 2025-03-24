@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Competency' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Competency' }}
@endsection

@section('main')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Competency List</h3>
                <button class="btn btn-success me-2" id="btnAdd">
                    <i class="fas fa-plus"></i> Add Competency
                </button>
            </div>
            <div class="card-body">
                <!-- Search Input -->
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search..." onkeyup="searchData()">
                </div>
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="competencyTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Competency</th>
                            <th>Description</th>
                            <th>Group Competency</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="competencyTableBody">
                        <tr>
                            <td>1</td>
                            <td>Organizational Awareness</td>
                            <td>Deskripsi</td>
                            <td>Basic</td>
                            <td>ENG</td>
                            <td>Operator</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm" onclick="showDetail('Organizational Awareness','Deskripsi','Basic','ENG','Operator')">
                                    <i class="bi bi-eye"></i> Show
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Journal</td>
                            <td>Deskripsi</td>
                            <td>Functional</td>
                            <td>ACCOUNTING</td>
                            <td>ACC</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm" onclick="showDetail('Journal','Deskripsi','Functional','ACCOUNTING','ACC')">
                                    <i class="bi bi-eye"></i> Show
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Problem Solving</td>
                            <td>Deskripsi</td>
                            <td>Managerial</td>
                            <td>ENG</td>
                            <td>Job Profesional</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm" onclick="showDetail('Problem Solving','Deskripsi','Managerial','ENG','Job Profesional')">
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
        // Fungsi search: filter data berdasarkan input
        function searchData() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let table = document.getElementById("competencyTable");
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

        // Tampilkan detail competency menggunakan SweetAlert
        function showDetail(competency, description, group, department, role) {
            Swal.fire({
                title: 'Competency Detail',
                html: `<p><strong>Competency :</strong> ${competency}</p>
                       <p><strong>Description :</strong> ${description}</p>
                       <p><strong>Group Competency :</strong> ${group}</p>
                       <p><strong>Department :</strong> ${department}</p>
                       <p><strong>Role :</strong> ${role}</p>`,
                icon: 'info',
                confirmButtonText: 'Close'
            });
        }

        // Tambah data competency
        document.getElementById('btnAdd').addEventListener('click', function() {
            Swal.fire({
                title: 'Add Competency',
                html:
                    '<input id="swal-input1" class="swal2-input" placeholder="Competency">' +
                    '<input id="swal-input2" class="swal2-input" placeholder="Description">' +
                    '<input id="swal-input3" class="swal2-input" placeholder="Group Competency">' +
                    '<input id="swal-input4" class="swal2-input" placeholder="Department">' +
                    '<input id="swal-input5" class="swal2-input" placeholder="Role">',
                focusConfirm: false,
                showCancelButton: true,
                preConfirm: () => {
                    const competency = document.getElementById('swal-input1').value;
                    const description = document.getElementById('swal-input2').value;
                    const group = document.getElementById('swal-input3').value;
                    const department = document.getElementById('swal-input4').value;
                    const role = document.getElementById('swal-input5').value;
                    if (!competency || !description || !group || !department || !role) {
                        Swal.showValidationMessage('Semua field harus diisi');
                        return false;
                    }
                    return { competency, description, group, department, role };
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
            const tableBody = document.getElementById("competencyTableBody");
            const newRow = tableBody.insertRow();
            const rowCount = tableBody.rows.length;
            newRow.innerHTML = `
                <td>${rowCount}</td>
                <td>${data.competency}</td>
                <td>${data.description}</td>
                <td>${data.group}</td>
                <td>${data.department}</td>
                <td>${data.role}</td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-info btn-sm" onclick="showDetail('${data.competency}', '${data.description}', '${data.group}', '${data.department}', '${data.role}')">
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
            const competency = row.cells[1].innerText;
            const description = row.cells[2].innerText;
            const group = row.cells[3].innerText;
            const department = row.cells[4].innerText;
            const role = row.cells[5].innerText;
            
            Swal.fire({
                title: 'Edit Competency',
                html:
                    `<input id="swal-input1" class="swal2-input" placeholder="Competency" value="${competency}">` +
                    `<input id="swal-input2" class="swal2-input" placeholder="Description" value="${description}">` +
                    `<input id="swal-input3" class="swal2-input" placeholder="Group Competency" value="${group}">` +
                    `<input id="swal-input4" class="swal2-input" placeholder="Department" value="${department}">` +
                    `<input id="swal-input5" class="swal2-input" placeholder="Role" value="${role}">`,
                focusConfirm: false,
                showCancelButton: true,
                preConfirm: () => {
                    const newCompetency = document.getElementById('swal-input1').value;
                    const newDescription = document.getElementById('swal-input2').value;
                    const newGroup = document.getElementById('swal-input3').value;
                    const newDepartment = document.getElementById('swal-input4').value;
                    const newRole = document.getElementById('swal-input5').value;
                    if (!newCompetency || !newDescription || !newGroup || !newDepartment || !newRole) {
                        Swal.showValidationMessage('Semua field harus diisi');
                        return false;
                    }
                    return { newCompetency, newDescription, newGroup, newDepartment, newRole };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    row.cells[1].innerText = result.value.newCompetency;
                    row.cells[2].innerText = result.value.newDescription;
                    row.cells[3].innerText = result.value.newGroup;
                    row.cells[4].innerText = result.value.newDepartment;
                    row.cells[5].innerText = result.value.newRole;
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
            const tableBody = document.getElementById("competencyTableBody");
            for (let i = 0; i < tableBody.rows.length; i++) {
                tableBody.rows[i].cells[0].innerText = i + 1;
            }
        }
    </script>
@endpush
