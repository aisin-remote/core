{{-- resources/views/website/competency/modal.blade.php --}}
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Competency</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addForm" action="{{ route('competencies.store') }}" method="POST">
          @csrf

          {{-- Competency Name --}}
          <div class="mb-3">
            <label class="form-label">Competency Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>

          {{-- Group Competency --}}
          <div class="mb-3">
            <label class="form-label">Group Competency</label>
            <select name="group_competency_id" class="form-select" required>
              <option value="" disabled selected>-- Select Group --</option>
              @foreach($groups as $g)
                <option value="{{ $g->id }}">{{ $g->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Position --}}
          <div class="mb-3">
            <label class="form-label">Position</label>
            <select id="add_position" name="position" class="form-select" required>
              <option value="" disabled selected>-- Select Position --</option>
              @foreach(['Operator','JP','Leader','Supervisor','Section Head','Coordinator','Manager','GM','Director'] as $pos)
                <option value="{{ $pos }}">{{ $pos }}</option>
              @endforeach
            </select>
          </div>

          {{-- Hierarchy selects --}}
          <div id="grp-sub" class="mb-3 d-none">
            <label class="form-label">Sub Section</label>
            <select id="add_sub_section_id" name="sub_section_id" class="form-select">
              <option value="">-- Select Sub Section --</option>
              @foreach($subSections as $ss)
                <option value="{{ $ss->id }}" data-section="{{ $ss->section_id }}">
                  {{ $ss->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div id="grp-sec" class="mb-3 d-none">
            <label class="form-label">Section</label>
            <select id="add_section_id" name="section_id" class="form-select">
              <option value="">-- Select Section --</option>
              @foreach($sections as $sec)
                <option value="{{ $sec->id }}" data-department="{{ $sec->department_id }}">
                  {{ $sec->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div id="grp-dept" class="mb-3 d-none">
            <label class="form-label">Department</label>
            <select id="add_department_id" name="department_id" class="form-select">
              <option value="">-- Select Department --</option>
              @foreach($departments as $dpt)
                <option value="{{ $dpt->id }}" data-division="{{ $dpt->division_id }}">
                  {{ $dpt->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div id="grp-div" class="mb-3 d-none">
            <label class="form-label">Division</label>
            <select id="add_division_id" name="division_id" class="form-select">
              <option value="">-- Select Division --</option>
              @foreach($divisions as $div)
                <option value="{{ $div->id }}" data-plant="{{ $div->plant_id }}">
                  {{ $div->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div id="grp-plant" class="mb-3 d-none">
            <label class="form-label">Plant</label>
            <select id="add_plant_id" name="plant_id" class="form-select">
              <option value="">-- Select Plant --</option>
              @foreach($plants as $pl)
                <option value="{{ $pl->id }}">{{ $pl->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Weight & Plan --}}
          <div class="mb-3">
            <label class="form-label">Weight</label>
            <input type="number" name="weight" class="form-control" min="1" max="4" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Plan</label>
            <input type="number" name="plan" class="form-control" min="1" max="4" required>
          </div>

          <div class="modal-footer">
            {{-- Ubah type="submit" menjadi type="button" dan beri id="btnSaveAdd" --}}
            <button type="button" class="btn btn-primary" id="btnSaveAdd">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // --- Hierarki Selects ---
  const posSel = document.getElementById('add_position');
  const hierarchyGroups = {
    'Operator':    ['grp-sub'],
    'JP':          ['grp-sub'],
    'Leader':      ['grp-sub'],
    'Supervisor':  ['grp-sec'],
    'Section Head':['grp-sec'],
    'Coordinator': ['grp-dept'],
    'Manager':     ['grp-dept'],
    'GM':          ['grp-div'],
    'Director':    ['grp-plant'],
  };
  const allGroupsIds = ['grp-sub','grp-sec','grp-dept','grp-div','grp-plant'];

  // Saat memilih posisi → sembunyikan semua hierarki, lalu tampilkan yang relevan
  posSel.addEventListener('change', () => {
    allGroupsIds.forEach(id => document.getElementById(id).classList.add('d-none'));
    (hierarchyGroups[posSel.value] || []).forEach(id =>
      document.getElementById(id).classList.remove('d-none')
    );
  });

  // Chain‐up auto‐fill (subSection → section → department → division → plant)
  document.getElementById('add_sub_section_id')?.addEventListener('change', e => {
    const sid = e.target.selectedOptions[0]?.dataset.section;
    if (sid) {
      document.getElementById('add_section_id').value = sid;
      document.getElementById('add_section_id').dispatchEvent(new Event('change'));
    }
  });
  document.getElementById('add_section_id')?.addEventListener('change', e => {
    const did = e.target.selectedOptions[0]?.dataset.department;
    if (did) {
      document.getElementById('add_department_id').value = did;
      document.getElementById('add_department_id').dispatchEvent(new Event('change'));
    }
  });
  document.getElementById('add_department_id')?.addEventListener('change', e => {
    const vid = e.target.selectedOptions[0]?.dataset.division;
    if (vid) {
      document.getElementById('add_division_id').value = vid;
      document.getElementById('add_division_id').dispatchEvent(new Event('change'));
    }
  });
  document.getElementById('add_division_id')?.addEventListener('change', e => {
    const pid = e.target.selectedOptions[0]?.dataset.plant;
    if (pid) {
      document.getElementById('add_plant_id').value = pid;
    }
  });

  // Reset form + hierarki selects saat modal ditutup
  document.getElementById('addModal')?.addEventListener('hidden.bs.modal', () => {
    document.getElementById('addForm').reset();
    allGroupsIds.forEach(id => document.getElementById(id).classList.add('d-none'));
  });

  // --- AJAX Submit “Add Competency” ---
  const addForm = document.getElementById('addForm');
  const btnSaveAdd = document.getElementById('btnSaveAdd');

  btnSaveAdd.addEventListener('click', function() {
    // Buat FormData
    const formData = new FormData(addForm);
    this.disabled = true;

    fetch("{{ route('competencies.store') }}", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: formData
    })
    .then(response => {
      this.disabled = false;
      if (!response.ok) {
        return response.json().then(errJson => {
          let msg = errJson.message || 'Gagal menyimpan data';
          return Promise.reject(new Error(msg));
        });
      }
      return response.json();
    })
    .then(data => {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: data.message || 'Competency added successfully!',
        timer: 1500,
        showConfirmButton: false
      });
      // Tutup modal setelah sukses
      bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
      addForm.reset();
      // Reload page setelah 1 detik
      setTimeout(() => location.reload(), 1000);
    })
    .catch(err => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: err.message
      });
      this.disabled = false;
    });
  });
});
</script>
@endpush
