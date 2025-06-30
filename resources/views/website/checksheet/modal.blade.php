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

                    <!-- Position dipindahkan ke atas -->
                    <div class="mb-3">
                        <label for="position">Position</label>
                        <select id="position_select" name="position" class="form-select" required>
                            <option value="" disabled selected>Select Position</option>
                            <!-- Opsi position akan diisi oleh JavaScript -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="competency_id">Category (Competency)</label>
                        <select
                            id="competency_id"
                            name="competency_id"
                            class="form-select"
                            required
                            disabled
                        >
                            <option value="" disabled selected>-- Select Competency --</option>
                            @foreach($competencies as $comp)
                                <option
                                    value="{{ $comp->id }}"
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
                    <div class="mb-3" hidden>
                        <label>Group Competency</label>
                        <input type="text" id="info_group" class="form-control" readonly>
                    </div>
                    <div class="mb-3" hidden>
                        <label>Department</label>
                        <input type="text" id="info_department" class="form-control" readonly>
                    </div>
                    <div class="mb-3" hidden>
                        <label>Sub Section</label>
                        <input type="text" id="info_subsection" class="form-control" readonly>
                    </div>
                    <div class="mb-3" hidden>
                        <label>Section</label>
                        <input type="text" id="info_section" class="form-control" readonly>
                    </div>
                    <div class="mb-3" hidden>
                        <label>Division</label>
                        <input type="text" id="info_division" class="form-control" readonly>
                    </div>
                    <div class="mb-3" hidden>
                        <label>Plant</label>
                        <input type="text" id="info_plant" class="form-control" readonly>
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
        const addModal = document.getElementById("addModal");
        const addForm = document.getElementById("addForm");
        const selectCompetency = document.getElementById("competency_id");
        const selectPosition = document.getElementById("position_select");
        
        // Elemen info relasi
        const infoGroup = document.getElementById("info_group");
        const infoDepartment = document.getElementById("info_department");
        const infoSubsection = document.getElementById("info_subsection");
        const infoSection = document.getElementById("info_section");
        const infoDivision = document.getElementById("info_division");
        const infoPlant = document.getElementById("info_plant");
        
        // Kumpulkan semua position unik dari competency
        const positionSet = new Set();
        
        // Loop melalui semua option competency
        document.querySelectorAll('#competency_id option').forEach(option => {
            if (option.value !== "") { // Skip placeholder
                const position = option.getAttribute('data-position');
                if (position) {
                    positionSet.add(position);
                }
            }
        });
        
        // Isi dropdown position dengan nilai unik
        positionSet.forEach(position => {
            const option = document.createElement('option');
            option.value = position;
            option.textContent = position;
            selectPosition.appendChild(option);
        });
        
        // Handler saat position berubah
        selectPosition.addEventListener('change', function() {
            const selectedPosition = this.value;
            
            // Aktifkan dropdown competency
            selectCompetency.disabled = false;
            
            // Reset pilihan competency
            selectCompetency.selectedIndex = 0;
            
            // Loop melalui semua option competency
            document.querySelectorAll('#competency_id option').forEach(option => {
                if (option.value === "") { // Placeholder
                    option.style.display = 'block';
                } else {
                    const position = option.getAttribute('data-position');
                    
                    // Tampilkan hanya competency dengan position yang sesuai
                    if (position === selectedPosition) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
            });
        });
        
        // Handler saat competency berubah
        selectCompetency.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            // Update info relasi
            infoGroup.value = selectedOption.getAttribute('data-group-name') || '';
            infoDepartment.value = selectedOption.getAttribute('data-department-name') || '';
            infoSubsection.value = selectedOption.getAttribute('data-subsection-name') || '';
            infoSection.value = selectedOption.getAttribute('data-section-name') || '';
            infoDivision.value = selectedOption.getAttribute('data-division-name') || '';
            infoPlant.value = selectedOption.getAttribute('data-plant-name') || '';
        });
        
        // Handler saat modal ditutup
        addModal.addEventListener('hidden.bs.modal', function() {
            // Reset form
            addForm.reset();
            
            // Reset info relasi
            infoGroup.value = '';
            infoDepartment.value = '';
            infoSubsection.value = '';
            infoSection.value = '';
            infoDivision.value = '';
            infoPlant.value = '';
            
            // Reset dropdown position
            selectPosition.innerHTML = '<option value="" disabled selected>Select Position</option>';
            
            // Isi ulang position (karena direset)
            positionSet.forEach(position => {
                const option = document.createElement('option');
                option.value = position;
                option.textContent = position;
                selectPosition.appendChild(option);
            });
            
            // Reset dan nonaktifkan dropdown competency
            selectCompetency.selectedIndex = 0;
            selectCompetency.disabled = true;
            
            // Tampilkan semua option competency
            document.querySelectorAll('#competency_id option').forEach(option => {
                option.style.display = 'block';
            });
        });
    });
</script>