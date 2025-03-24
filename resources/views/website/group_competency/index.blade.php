@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Group Competency' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Group Competency' }}
@endsection

@section('main')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Group Competency</h3>
                <div class="d-flex align-items-center">
                    <button class="btn btn-success me-2" id="btnAdd">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Search input -->
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search..." onkeyup="searchData()">
                </div>
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="groupTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Nama Group</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="groupTableBody">
                        <tr>
                            <td>1</td>
                            <td>Basic</td>
                            <td>Deskripsi untuk grup Basic</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm" onclick="showDetail('Basic','Deskripsi untuk grup Basic')">
                                    <i class="bi bi-eye"></i> Show
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Functional</td>
                            <td>Deskripsi untuk grup Functional</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm" onclick="showDetail('Functional','Deskripsi untuk grup Functional')">
                                    <i class="bi bi-eye"></i> Show
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Managerial</td>
                            <td>Deskripsi untuk grup Managerial</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm" onclick="showDetail('Managerial','Deskripsi untuk grup Managerial')">
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
        // Fungsi search: filter baris tabel berdasarkan input
        function searchData() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let table = document.getElementById("groupTable");
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

        // Tampilkan detail grup dengan format nama dan deskripsi
        function showDetail(name, description) {
            Swal.fire({
                title: 'Detail Group Competency',
                html: `<p><strong>Nama :</strong> ${name}</p>
                       <p><strong>Deskripsi :</strong> ${description}</p>`,
                icon: 'info',
                confirmButtonText: 'Close'
            });
        }

        // Add row (tambah data) dengan SweetAlert input
        document.getElementById('btnAdd').addEventListener('click', function() {
            Swal.fire({
                title: 'Add Group Competency',
                html:
                    '<input id="swal-input1" class="swal2-input" placeholder="Nama Group">' +
                    '<input id="swal-input2" class="swal2-input" placeholder="Deskripsi">',
                focusConfirm: false,
                showCancelButton: true,
                preConfirm: () => {
                    const name = document.getElementById('swal-input1').value;
                    const description = document.getElementById('swal-input2').value;
                    if (!name || !description) {
                        Swal.showValidationMessage('Semua field harus diisi');
                        return false;
                    }
                    return { name: name, description: description };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    addRow(result.value.name, result.value.description);
                    Swal.fire('Added!', 'Data berhasil ditambahkan', 'success');
                }
            });
        });

        // Fungsi untuk menambah baris ke tabel
        function addRow(name, description) {
            const tableBody = document.getElementById("groupTableBody");
            const newRow = tableBody.insertRow();
            const rowCount = tableBody.rows.length;
            newRow.innerHTML = `
                <td>${rowCount}</td>
                <td>${name}</td>
                <td>${description}</td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm" onclick="editRow(this)">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-info btn-sm" onclick="showDetail('${name}', '${description}')">
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
            const name = row.cells[1].innerText;
            const description = row.cells[2].innerText;

            Swal.fire({
                title: 'Edit Group Competency',
                html:
                    `<input id="swal-input1" class="swal2-input" placeholder="Nama Group" value="${name}">` +
                    `<input id="swal-input2" class="swal2-input" placeholder="Deskripsi" value="${description}">`,
                focusConfirm: false,
                showCancelButton: true,
                preConfirm: () => {
                    const newName = document.getElementById('swal-input1').value;
                    const newDescription = document.getElementById('swal-input2').value;
                    if (!newName || !newDescription) {
                        Swal.showValidationMessage('Semua field harus diisi');
                        return false;
                    }
                    return { name: newName, description: newDescription };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    row.cells[1].innerText = result.value.name;
                    row.cells[2].innerText = result.value.description;
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

        // Fungsi update nomor baris setiap kali ada penambahan/hapus data
        function updateRowNumbers() {
            const tableBody = document.getElementById("groupTableBody");
            for (let i = 0; i < tableBody.rows.length; i++) {
                tableBody.rows[i].cells[0].innerText = i + 1;
            }
        }
    </script>
@endpush
