<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Competency</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editForm">
            <input type="hidden" id="edit_id" name="id">
            @csrf
  
            <!-- Name -->
            <div class="mb-3">
              <label for="edit_name" class="form-label">Competency Name</label>
              <input type="text" class="form-control" id="edit_name" name="name" required>
            </div>
  
            <!-- Group Competency -->
            <div class="mb-3">
              <label for="edit_group_competency_id" class="form-label">Group Competency</label>
              <select class="form-select" id="edit_group_competency_id" name="group_competency_id" required>
                <option value="" disabled>Select Group</option>
              </select>
            </div>
  
            <!-- Position -->
            <div class="mb-3">
              <label for="edit_position" class="form-label">Position</label>
              <select id="edit_position" name="position" class="form-select" required>
                <option value="" disabled>Select Position</option>
                @foreach(['Director','GM','Manager','Coordinator','Section Head','Supervisor','Leader','JP','Operator'] as $pos)
                  <option value="{{ $pos }}">{{ $pos }}</option>
                @endforeach
              </select>
            </div>
  
            <!-- Dynamic Hierarchy Fields -->
            <div id="edit_grp-sub" class="mb-3 d-none">
              <label class="form-label">Sub Section</label>
              <select id="edit_sub_section_id" name="sub_section_id" class="form-select">
                <option value="">-- Select Sub Section --</option>
                @foreach($subSections as $ss)
                  <option value="{{ $ss->id }}" data-section="{{ $ss->section_id }}">{{ $ss->name }}</option>
                @endforeach
              </select>
            </div>
  
            <div id="edit_grp-sec" class="mb-3 d-none">
              <label class="form-label">Section</label>
              <select id="edit_section_id" name="section_id" class="form-select">
                <option value="">-- Select Section --</option>
                @foreach($sections as $sec)
                  <option value="{{ $sec->id }}" data-department="{{ $sec->department_id }}">{{ $sec->name }}</option>
                @endforeach
              </select>
            </div>
  
            <div id="edit_grp-dept" class="mb-3 d-none">
              <label class="form-label">Department</label>
              <select id="edit_department_id" name="department_id" class="form-select">
                <option value="">-- Select Department --</option>
                @foreach($departments as $dpt)
                  <option value="{{ $dpt->id }}" data-division="{{ $dpt->division_id }}">{{ $dpt->name }}</option>
                @endforeach
              </select>
            </div>
  
            <div id="edit_grp-div" class="mb-3 d-none">
              <label class="form-label">Division</label>
              <select id="edit_division_id" name="division_id" class="form-select">
                <option value="">-- Select Division --</option>
                @foreach($divisions as $div)
                  <option value="{{ $div->id }}" data-plant="{{ $div->plant_id }}">{{ $div->name }}</option>
                @endforeach
              </select>
            </div>
  
            <div id="edit_grp-plant" class="mb-3 d-none">
              <label class="form-label">Plant</label>
              <select id="edit_plant_id" name="plant_id" class="form-select">
                <option value="">-- Select Plant --</option>
                @foreach($plants as $pl)
                  <option value="{{ $pl->id }}">{{ $pl->name }}</option>
                @endforeach
              </select>
            </div>
  
            <!-- Weight & Plan -->
            <div class="mb-3">
              <label for="edit_weight" class="form-label">Weight</label>
              <input type="number" class="form-control" id="edit_weight" name="weight" min="0" max="4" required>
            </div>
            <div class="mb-3">
              <label for="edit_plan" class="form-label">Plan</label>
              <input type="number" class="form-control" id="edit_plan" name="plan" min="0" max="4" required>
            </div>
  
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const posSel   = document.getElementById('edit_position');
      const allGroups = ['edit_grp-sub','edit_grp-sec','edit_grp-dept','edit_grp-div','edit_grp-plant'];
      const groupsMap = {
        'Operator':   ['edit_grp-sub'],
        'JP':         ['edit_grp-sub'],
        'Leader':     ['edit_grp-sub'],
        'Supervisor': ['edit_grp-sec'],
        'Section Head':['edit_grp-sec'],
        'Coordinator':['edit_grp-dept'],
        'Manager':    ['edit_grp-dept'],
        'GM':         ['edit_grp-div'],
        'Director':   ['edit_grp-plant'],
      };
  
      // toggle dynamic fields
      function toggleFields() {
        allGroups.forEach(id => document.getElementById(id).classList.add('d-none'));
        (groupsMap[posSel.value] || []).forEach(id =>
          document.getElementById(id).classList.remove('d-none')
        );
      }
  
      // chain-up handlers
      document.getElementById('edit_sub_section_id')?.addEventListener('change', e => {
        const secId = e.target.selectedOptions[0]?.dataset.section;
        if (secId) document.getElementById('edit_section_id').value = secId;
      });
      document.getElementById('edit_section_id')?.addEventListener('change', e => {
        const deptId = e.target.selectedOptions[0]?.dataset.department;
        if (deptId) document.getElementById('edit_department_id').value = deptId;
      });
      document.getElementById('edit_department_id')?.addEventListener('change', e => {
        const divId = e.target.selectedOptions[0]?.dataset.division;
        if (divId) document.getElementById('edit_division_id').value = divId;
      });
      document.getElementById('edit_division_id')?.addEventListener('change', e => {
        const plantId = e.target.selectedOptions[0]?.dataset.plant;
        if (plantId) document.getElementById('edit_plant_id').value = plantId;
      });
  
      // build group select on edit click
      document.addEventListener('click', async e => {
        if (!e.target.classList.contains('edit-btn')) return;
        const id = e.target.dataset.id;
        const res = await fetch(`/competencies/${id}/edit`, { headers: { Accept: 'application/json' }});
        const d = await res.json();
  
        document.getElementById('edit_id').value                = d.id;
        document.getElementById('edit_name').value              = d.name;
        document.getElementById('edit_weight').value            = d.weight;
        document.getElementById('edit_plan').value              = d.plan;
  
        // group options
        const grpEl = document.getElementById('edit_group_competency_id');
        grpEl.innerHTML = '<option value="" disabled>Select Group</option>';
        d.all_groups.forEach(g => {
          grpEl.innerHTML += `<option value="${g.id}" ${g.id==d.group_competency_id?'selected':''}>${g.name}</option>`;
        });
  
        // set position & show fields
        posSel.value = d.position;
        toggleFields();
        // set current dynamic values
        document.getElementById('edit_sub_section_id').value = d.sub_section_id || '';
        document.getElementById('edit_section_id').value     = d.section_id     || '';
        document.getElementById('edit_department_id').value  = d.department_id  || '';
        document.getElementById('edit_division_id').value    = d.division_id    || '';
        document.getElementById('edit_plant_id').value       = d.plant_id       || '';
  
        new bootstrap.Modal(document.getElementById('editModal')).show();
      });
  
      // on position change
      posSel.addEventListener('change', () => toggleFields());
  
      // reset on close
      document.getElementById('editModal').addEventListener('hidden.bs.modal', () => {
        document.getElementById('editForm').reset();
        toggleFields();
      });
  
      // submit handler
      document.getElementById('editForm').addEventListener('submit', async e => {
        e.preventDefault();
        const id = document.getElementById('edit_id').value;
        const formData = new FormData();
        ['name','group_competency_id','position','weight','plan'].forEach(f =>
          formData.append(f, document.getElementById(`edit_${f.replace('_','_')}`).value)
        );
        // dynamic fields
        ['sub_section','section','department','division','plant'].forEach(pref => {
          const el = document.getElementById(`edit_${pref}_id`);
          formData.append(`${pref}_id`, el? el.value : '');
        });
        formData.append('_method', 'PUT');
  
        try {
          const res = await fetch(`/competencies/${id}`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: formData
          });
          const data = await res.json();
          if (res.ok) {
            Swal.fire('Updated!', data.message, 'success').then(() => location.reload());
          } else {
            Swal.fire('Error', data.message || 'Validation failed', 'error');
          }
        } catch (err) {
          console.error(err);
          Swal.fire('Error', 'Something went wrong', 'error');
        }
      });
    });
  </script>
  