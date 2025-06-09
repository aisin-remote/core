<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Checksheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm" method="POST" action="{{ route('checksheet.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="competency_id">Category (Competency)</label>
                        <select
                            id="competency_id"
                            name="competency_id"
                            class="form-select"
                            required
                        >
                            <option value="" disabled selected>-- Select Competency --</option>

                            @foreach($competencies as $comp)
                                <option
                                    value="{{ $comp->id }}"
                                    {{-- data untuk menampilkan nama relasi, bukan ID --}}
                                    data-group-name="{{ optional($comp->group_competency)->name }}"
                                    data-department-name="{{ optional($comp->department)->name }}"
                                    data-subsection-name="{{ optional($comp->sub_section)->name }}"
                                    data-section-name="{{ optional($comp->section)->name }}"
                                    data-division-name="{{ optional($comp->division)->name }}"
                                    data-plant-name="{{ optional($comp->plant)->name }}"
                                    data-position="{{ $comp->position }}"
                                >
                                    {{ $comp->name }}
                                    @if($comp->position)
                                    – {{ $comp->position }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Tampilkan informasi relasi berdasarkan pilihan (dengan nama, bukan ID) -->
                    <div class="mb-3">
                        <label>Group Competency</label>
                        <input type="text" id="info_group" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Department</label>
                        <input type="text" id="info_department" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Sub Section</label>
                        <input type="text" id="info_subsection" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Section</label>
                        <input type="text" id="info_section" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Division</label>
                        <input type="text" id="info_division" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Plant</label>
                        <input type="text" id="info_plant" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="position">Position</label>
                        <select id="position_select" name="position" class="form-select" required>
                          <option value="" disabled selected>Select Position</option>
                        </select>
                    </div>

                    <!-- Footer Modal -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
      const addModal         = document.getElementById("addModal");
      const addForm          = document.getElementById("addForm");
      const selectCompetency = document.getElementById("competency_id");
      const selectPosition   = document.getElementById("position_select");
  
      // Elemen‐elemen read‐only (jika mau menampilkan info‐info lain)
      const infoGroup      = document.getElementById("info_group");
      const infoDepartment = document.getElementById("info_department");
      const infoSubsection = document.getElementById("info_subsection");
      const infoSection    = document.getElementById("info_section");
      const infoDivision   = document.getElementById("info_division");
      const infoPlant      = document.getElementById("info_plant");
  
      selectCompetency.addEventListener("change", function() {
        // <option> yang terpilih
        const opt = selectCompetency.options[selectCompetency.selectedIndex];
  
        // 1) Isi info‐info relasi (jika memang ingin menampilkan nama relasi)
        infoGroup.value      = opt.getAttribute("data-group-name")      || "";
        infoDepartment.value = opt.getAttribute("data-department-name") || "";
        infoSubsection.value = opt.getAttribute("data-subsection-name") || "";
        infoSection.value    = opt.getAttribute("data-section-name")    || "";
        infoDivision.value   = opt.getAttribute("data-division-name")   || "";
        infoPlant.value      = opt.getAttribute("data-plant-name")      || "";
  
        // 2) Ambil nilai enum position dari data‐attribute
        const positionText = opt.getAttribute("data-position") || "";
  
        // Reset dulu dropdown Position (hanya placeholder)
        selectPosition.innerHTML = '<option value="" disabled selected>Select Position</option>';
  
        // Jika ada string position (enum) → buat satu <option> dan pilih langsung
        if (positionText !== "") {
          const newOpt = document.createElement("option");
          newOpt.value    = positionText;
          newOpt.text     = positionText;
          newOpt.selected = true;
          selectPosition.appendChild(newOpt);
        }
        // Jika positionText kosong → biarkan dropdown hanya berisi placeholder
      });
  
      // Reset semua ketika modal ditutup
      addModal.addEventListener("hidden.bs.modal", function() {
        addForm.reset();
        infoGroup.value      = "";
        infoDepartment.value = "";
        infoSubsection.value = "";
        infoSection.value    = "";
        infoDivision.value   = "";
        infoPlant.value      = "";
        selectPosition.innerHTML = '<option value="" disabled selected>Select Position</option>';
      });
    });
  </script>