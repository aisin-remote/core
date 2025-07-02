<!-- Pastikan sudah include jQuery dan Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Perbaikan tampilan dropdown */
    .select2-container {
        z-index: 1060 !important; 
    }
    .select2-selection {
        height: 38px !important; /* Tinggi normal */
        padding: 6px !important;
    }
    .select2-selection__rendered {
        line-height: 26px !important; /* Jarak vertikal normal */
    }
</style>

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
                        <label for="position">Position</label>
                        <select id="position_select" name="position" class="form-select" required>
                            <option value="" disabled selected>Select Position</option>
                            <!-- Position options will be populated by JS -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="competency_id">Category (Competency)</label>
                        <select
                            id="competency_id"
                            name="competency_id"
                            class="form-select select2-competency"
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

                    <!-- Hidden relation info fields -->
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
        
        // Relation info elements
        const infoGroup = document.getElementById("info_group");
        const infoDepartment = document.getElementById("info_department");
        const infoSubsection = document.getElementById("info_subsection");
        const infoSection = document.getElementById("info_section");
        const infoDivision = document.getElementById("info_division");
        const infoPlant = document.getElementById("info_plant");
        
        // Collect unique positions from competencies
        const positionSet = new Set();
        const competencyOptions = [];
        
        // Simpan semua opsi competency asli
        document.querySelectorAll('#competency_id option').forEach(option => {
            if (option.value !== "") {
                const position = option.getAttribute('data-position');
                if (position) {
                    positionSet.add(position);
                }
                competencyOptions.push(option.cloneNode(true));
            }
        });
        
        // Populate position dropdown
        positionSet.forEach(position => {
            const option = document.createElement('option');
            option.value = position;
            option.textContent = position;
            selectPosition.appendChild(option);
        });
        
        // Initialize Select2 for competency dropdown
        $(document).ready(function() {
            $('.select2-competency').select2({
                placeholder: '-- Select Competency --',
                dropdownParent: $('#addModal'),
                minimumResultsForSearch: 1,
                width: '100%' // Pastikan lebar penuh
            });

            // Initially disable
            $('.select2-competency').prop('disabled', true).trigger('change');
        });

        // Handle position change - PERBAIKAN UTAMA
        selectPosition.addEventListener('change', function() {
            const selectedPosition = this.value;
            
            // Enable competency dropdown
            $('.select2-competency').prop('disabled', false).trigger('change');
            
            // Reset competency selection
            $('.select2-competency').val(null).trigger('change');
            
            // Hapus semua opsi kecuali placeholder
            $('#competency_id').empty().append('<option value="" disabled selected>-- Select Competency --</option>');
            
            // Tambahkan hanya opsi dengan position yang sesuai
            competencyOptions.forEach(option => {
                const position = option.getAttribute('data-position');
                if (position === selectedPosition) {
                    selectCompetency.appendChild(option.cloneNode(true));
                }
            });
            
            // Perbarui Select2
            $('.select2-competency').select2('destroy');
            $('.select2-competency').select2({
                placeholder: '-- Select Competency --',
                dropdownParent: $('#addModal'),
                minimumResultsForSearch: 1,
                width: '100%'
            });
        });
        
        // Handle competency change
        selectCompetency.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            // Update relation info
            infoGroup.value = selectedOption.getAttribute('data-group-name') || '';
            infoDepartment.value = selectedOption.getAttribute('data-department-name') || '';
            infoSubsection.value = selectedOption.getAttribute('data-subsection-name') || '';
            infoSection.value = selectedOption.getAttribute('data-section-name') || '';
            infoDivision.value = selectedOption.getAttribute('data-division-name') || '';
            infoPlant.value = selectedOption.getAttribute('data-plant-name') || '';
        });
        
        // Handle modal close
        addModal.addEventListener('hidden.bs.modal', function() {
            // Reset form
            addForm.reset();
            
            // Reset relation info
            infoGroup.value = '';
            infoDepartment.value = '';
            infoSubsection.value = '';
            infoSection.value = '';
            infoDivision.value = '';
            infoPlant.value = '';
            
            // Reset position dropdown
            selectPosition.innerHTML = '<option value="" disabled selected>Select Position</option>';
            positionSet.forEach(position => {
                const option = document.createElement('option');
                option.value = position;
                option.textContent = position;
                selectPosition.appendChild(option);
            });
            
            // Reset competency dropdown
            selectCompetency.innerHTML = '<option value="" disabled selected>-- Select Competency --</option>';
            competencyOptions.forEach(option => {
                selectCompetency.appendChild(option.cloneNode(true));
            });
            
            // Reset and disable competency dropdown
            $('.select2-competency').val(null).trigger('change');
            $('.select2-competency').prop('disabled', true).trigger('change');
            
            // Reinitialize Select2
            $('.select2-competency').select2('destroy');
            $('.select2-competency').select2({
                placeholder: '-- Select Competency --',
                dropdownParent: $('#addModal'),
                minimumResultsForSearch: 1,
                width: '100%'
            });
        });
    });
</script>