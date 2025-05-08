<style>
  .modal {
    background-color: rgba(0,0,0,0.5) !important;
    overflow-y: auto !important;
  }
  .modal-backdrop {
    z-index: 1040 !important;
  }
  .modal-content {
    margin: 2rem auto;
    max-height: 90vh;
    overflow-y: auto;
  }
  .form-check-input {
    border-color: #000 !important;
  }
  .form-check-input:checked {
    background-color: #000 !important;
    border-color: #000 !important;
  }
  .form-check-label {
    color: #000 !important;
    font-weight: 500;
  }
</style>

<!-- Modal 1: Index -->
<div class="modal fade" id="checksheetIndexModal" tabindex="-1" aria-labelledby="checksheetIndexModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header text-white">
        <h5 class="modal-title">Checksheet List</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <button type="button" id="btnAddChecksheet" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add
        </button>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th class="text-center">Date</th>
                <th class="text-center">Status</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="checksheetList">
              <tr>
                <td colspan="3" class="text-center">Belum ada checksheet</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal 2: Create/Edit -->
<div class="modal fade" id="checksheetCreateModal" tabindex="-1" aria-labelledby="checksheetCreateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header text-white">
        <h5 class="modal-title">Checksheet</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="checksheetForm">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Date</label>
              <input type="date" class="form-control" id="checksheetDate" readonly required>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered">  
              <thead class="table-light">
                <tr>
                  <th class="text-center">No</th>
                  <th class="text-center">Requirement</th>
                  <th class="text-center" style="width:15%">Grade</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody id="checkItems">
                <tr>
                  <td class="text-center">1</td>
                  <td>
                    <input type="text" class="form-control" placeholder="Requirement" required>
                  </td>
                  <td>
                    <div class="d-flex gap-3">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="grade" value="Tidak Pernah" required>
                        <label class="form-check-label">Tidak Pernah</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="grade" value="Kadang-kadang">
                        <label class="form-check-label">Kadang-kadang</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="grade" value="Selalu">
                        <label class="form-check-label">Selalu</label>
                      </div>
                    </div>
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            <button type="button" class="btn btn-success" id="addCheckItem">
              <i class="fas fa-plus-circle"></i> Add Line
            </button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali</button>
        <button type="button" class="btn btn-primary" id="saveChecksheet">
          <i class="fas fa-save"></i> Save
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal 3: View -->
<div class="modal fade" id="viewChecksheetModal" tabindex="-1" aria-labelledby="viewChecksheetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header text-white">
        <h5 class="modal-title">Detail Checksheet</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Tanggal:</strong> <span id="viewDate"></span>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th class="text-center">No</th>
                <th class="text-center">Requirement</th>
                <th class="text-center">Nilai</th>
              </tr>
            </thead>
            <tbody id="viewItems"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali</button>
      </div>
    </div>
  </div>
</div>

<script>
  let currentEditingId = null;
  let rowCounter = 1;

  document.addEventListener('DOMContentLoaded', () => {
    if (!localStorage.getItem('checksheets')) {
      localStorage.setItem('checksheets', JSON.stringify([]));
    }

    manageModalTransitions();
    updateChecksheetList();

    document.getElementById('btnAddChecksheet').addEventListener('click', () => {
      resetForm();
      $('#checksheetIndexModal').modal('hide');
      $('#checksheetCreateModal').modal('show');
    });

    document.getElementById('addCheckItem').addEventListener('click', () => {
      rowCounter++;
      const newRow = document.createElement('tr');
      newRow.innerHTML = `
      <td class="text-center">${rowCounter}</td>
      <td><input type="text" class="form-control" placeholder="Requirement" required></td>
      <td>
        <div class="d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade${rowCounter}" value="Tidak Pernah" required>
            <label class="form-check-label">Tidak Pernah</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade${rowCounter}" value="Kadang-kadang">
            <label class="form-check-label">Kadang-kadang</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade${rowCounter}" value="Selalu">
            <label class="form-check-label">Selalu</label>
          </div>
        </div>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    `;
      document.getElementById('checkItems').appendChild(newRow);
    });

    document.getElementById('saveChecksheet').addEventListener('click', () => {
      const items = [];
      document.querySelectorAll('#checkItems tr').forEach(r => {
        const val = r.cells[2].querySelector('input[type="radio"]:checked').value;
        items.push({
          requirement: r.cells[1].querySelector('input').value,
          grade: val
        });
      });

      const data = {
        id: currentEditingId || Date.now(),
        date: document.getElementById('checksheetDate').value,
        items
      };

      let arr = JSON.parse(localStorage.getItem('checksheets'));
      arr = currentEditingId ? arr.map(c => c.id === currentEditingId ? data : c) : [...arr, data];
      localStorage.setItem('checksheets', JSON.stringify(arr));

      updateChecksheetList();
      $('#checksheetCreateModal').modal('hide');
    });

    document.getElementById('checksheetList').addEventListener('click', e => {
      const btn = e.target.closest('button');
      if (!btn) return;

      const id = parseInt(btn.dataset.id, 10);
      const arr = JSON.parse(localStorage.getItem('checksheets'));
      const cs = arr.find(c => c.id === id);

      if (btn.classList.contains('btn-view')) showViewModal(cs);
      if (btn.classList.contains('btn-delete')) deleteChecksheet(id);
    });
  });

  function manageModalTransitions() {
    $('.modal').on('hidden.bs.modal', () => {
      $('body').removeClass('modal-open');
      $('.modal-backdrop').remove();
    });

    $('#checksheetCreateModal, #viewChecksheetModal').on('hidden.bs.modal', () => {
      $('#checksheetIndexModal').modal('show');
    });

    $('#checksheetIndexModal').on('hidden.bs.modal', () => {
      $('body').css('overflow', 'auto');
    });
  }

  function showViewModal(c) {
    $('#checksheetIndexModal').modal('hide');
    document.getElementById('viewDate').textContent = c.date;
    document.getElementById('viewGrade').textContent = '';

    const tb = document.getElementById('viewItems');
    tb.innerHTML = '';
    c.items.forEach((i, j) => {
      const r = document.createElement('tr');
      r.innerHTML = `
        <td class="text-center">${j + 1}</td>
        <td>${i.requirement}</td>
        <td class="text-center">${i.grade}</td>
      `;
      tb.appendChild(r);
    });

    $('#viewChecksheetModal').modal('show');
  }

  function showViewModal(c) {
    $('#checksheetIndexModal').modal('hide');
    document.getElementById('viewDate').textContent = c.date;
    const tb = document.getElementById('viewItems');
    tb.innerHTML = '';
    c.items.forEach((i,j)=>{
      const r = document.createElement('tr');
      r.innerHTML = `
        <td class="text-center">${j+1}</td>
        <td>${i.requirement}</td>
        <td class="text-center">${i.grade}</td>
      `;
      tb.appendChild(r);
    });
    $('#viewChecksheetModal').modal('show');
  }


  function openEditModal(c) {
    $('#checksheetIndexModal').modal('hide');
    currentEditingId = c.id;
    document.getElementById('checksheetDate').value = c.date;

    const tb = document.getElementById('checkItems');
    tb.innerHTML = '';
    c.items.forEach((i, j) => {
      const r = document.createElement('tr');
      r.innerHTML = `
      <td class="text-center">${j + 1}</td>
      <td><input type="text" class="form-control" value="${i.requirement}" required></td>
      <td>
        <div class="d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade${j + 1}" value="Tidak Pernah" 
              ${i.grade === 'Tidak Pernah' ? 'checked' : ''}>
            <label class="form-check-label">Tidak Pernah</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade${j + 1}" value="Kadang-kadang"
              ${i.grade === 'Kadang-kadang' ? 'checked' : ''}>
            <label class="form-check-label">Kadang-kadang</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade${j + 1}" value="Selalu"
              ${i.grade === 'Selalu' ? 'checked' : ''}>
            <label class="form-check-label">Selalu</label>
          </div>
        </div>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    `;
      tb.appendChild(r);
    });
    rowCounter = c.items.length;
    $('#checksheetCreateModal').modal('show');
  }

  function deleteRow(btn) {
    btn.closest('tr').remove();
    document.querySelectorAll('#checkItems tr').forEach((r, i) => {
      r.cells[0].textContent = i + 1;
    });
  }

  function deleteChecksheet(id) {
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: 'Data yang dihapus tidak dapat dikembalikan!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!'
    }).then(r => {
      if (r.isConfirmed) {
        const arr = JSON.parse(localStorage.getItem('checksheets')).filter(c => c.id !== id);
        localStorage.setItem('checksheets', JSON.stringify(arr));
        updateChecksheetList();
        $('#checksheetIndexModal').modal('show');
      }
    });
  }

  function updateChecksheetList() {
    const arr = JSON.parse(localStorage.getItem('checksheets'));
    const tb = document.getElementById('checksheetList');
    tb.innerHTML = '';

    if (!arr.length) {
      tb.innerHTML = '<tr><td colspan="3" class="text-center">Belum ada checksheet</td></tr>';
      return;
    }

    arr.forEach(c => {
      const allSelalu = c.items.every(item => item.grade === 'Selalu');
      const status = allSelalu ? 'Lulus' : 'Tidak Lulus';

      const r = document.createElement('tr');
      r.innerHTML = `
        <td class="text-center">${c.date}</td>
        <td class="text-center">
          <span>${status}</span>
        </td>
        <td class="text-center">
          <button class="btn btn-sm btn-primary btn-view" data-id="${c.id}"><i class="fas fa-eye"></i></button>
          <button class="btn btn-sm btn-danger btn-delete" data-id="${c.id}"><i class="fas fa-trash"></i></button>
        </td>
      `;
      tb.appendChild(r);
    });
  }

  function resetForm() {
    currentEditingId = null;
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('checksheetDate').value = today;
    document.getElementById('checkItems').innerHTML = `
    <tr>
      <td class="text-center">1</td>
      <td><input type="text" class="form-control" placeholder="Requirement" required></td>
      <td>
        <div class="d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade0" value="Tidak Pernah" required>
            <label class="form-check-label">Tidak Pernah</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade0" value="Kadang-kadang">
            <label class="form-check-label">Kadang-kadang</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="grade0" value="Selalu">
            <label class="form-check-label">Selalu</label>
          </div>
        </div>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
  `;
    rowCounter = 1;
  }
</script>
