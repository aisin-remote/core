<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Checksheet User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm" method="POST" action="{{ route('checksheet_user.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="question">Name</label>
                        <input type="text" class="form-control" id="question" name="question" required>
                    </div>

                    <div class="mb-3">
                        <label for="position">Position</label>
                        <select id="position_select" name="position" class="form-select" required>
                            <option value="" disabled selected>Select Position</option>
                            <option value="Operator">Operator</option>
                            <option value="JP">JP</option>
                            <option value="Act JP">Act JP</option>
                            <option value="Leader">Leader</option>
                            <option value="Act Leader">Act Leader</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Section Head">Section Head</option>
                            <option value="Coordinator">Coordinator</option>
                            <option value="Manager">Manager</option>
                            <option value="GM">GM</option>
                            <option value="Director">Director</option>
                        </select>
                    </div>

                    <!-- Field untuk Sub Section (Operator, JP, Act JP, Leader, Act Leader) -->
                    <div class="mb-3 d-none" id="sub_section_field">
                        <label for="sub_section_id">Sub Section</label>
                        <select id="sub_section_id" name="sub_section_id" class="form-select">
                            <option value="" disabled selected>-- Select Sub Section --</option>
                            @foreach ($subSections as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field untuk Section (Supervisor, Section Head) -->
                    <div class="mb-3 d-none" id="section_field">
                        <label for="section_id">Section</label>
                        <select id="section_id" name="section_id" class="form-select">
                            <option value="" disabled selected>-- Select Section --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field untuk Department (Coordinator, Manager) -->
                    <div class="mb-3 d-none" id="department_field">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="" disabled selected>-- Select Department --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field untuk Division (GM) -->
                    <div class="mb-3 d-none" id="division_field">
                        <label for="division_id">Division</label>
                        <select id="division_id" name="division_id" class="form-select">
                            <option value="" disabled selected>-- Select Division --</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field untuk Plant (Director) -->
                    <div class="mb-3 d-none" id="plant_field">
                        <label for="plant_id">Plant</label>
                        <select id="plant_id" name="plant_id" class="form-select">
                            <option value="" disabled selected>-- Select Plant --</option>
                            @foreach ($plants as $plant)
                                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Competency Field (akan diisi via AJAX) -->
                    <div class="mb-3 d-none" id="competency_field">
                        <label for="competency_id">Competency</label>
                        <select id="competency_id" name="competency_id" class="form-select" required>
                            <option value="" disabled selected>-- Select Competency --</option>
                            <!-- Diisi via AJAX -->
                        </select>
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
        const positionSelect = document.getElementById('position_select');
        const levelFields = [
            { id: 'sub_section_field', positions: ['Operator','JP','Act JP','Leader','Act Leader'] },
            { id: 'section_field', positions: ['Supervisor','Section Head'] },
            { id: 'department_field', positions: ['Coordinator','Manager'] },
            { id: 'division_field', positions: ['GM'] },
            { id: 'plant_field', positions: ['Director'] }
        ];
        
        const levelSelects = [
            'sub_section_id', 'section_id', 'department_id', 'division_id', 'plant_id'
        ];

        // Tampilkan field sesuai posisi
        positionSelect.addEventListener('change', function() {
            const selectedPosition = this.value;
            
            // Sembunyikan semua field
            levelFields.forEach(field => {
                document.getElementById(field.id).classList.add('d-none');
                document.querySelector(`#${field.id} select`).required = false;
            });
            
            // Sembunyikan competency field
            document.getElementById('competency_field').classList.add('d-none');
            document.getElementById('competency_id').required = false;
            document.getElementById('competency_id').innerHTML = '<option value="" disabled selected>-- Select Competency --</option>';

            // Tampilkan field yang sesuai
            levelFields.forEach(field => {
                if (field.positions.includes(selectedPosition)) {
                    document.getElementById(field.id).classList.remove('d-none');
                    document.querySelector(`#${field.id} select`).required = true;
                }
            });
        });

        // Event listener untuk semua level selects
        levelSelects.forEach(selectId => {
            const selectElement = document.getElementById(selectId);
            if (selectElement) {
                selectElement.addEventListener('change', function() {
                    if (positionSelect.value) {
                        fetchCompetencies();
                    }
                });
            }
        });

        // Fungsi untuk mengambil competency
        function fetchCompetencies() {
            const position = positionSelect.value;
            let levelId = null;
            let levelType = null;
            
            // Tentukan level type dan ID berdasarkan position
            switch (position) {
                case 'Operator':
                case 'JP':
                case 'Act JP':
                case 'Leader':
                case 'Act Leader':
                    levelType = 'sub_section';
                    levelId = document.getElementById('sub_section_id').value;
                    break;
                
                case 'Supervisor':
                case 'Section Head':
                    levelType = 'section';
                    levelId = document.getElementById('section_id').value;
                    break;
                
                case 'Coordinator':
                case 'Manager':
                    levelType = 'department';
                    levelId = document.getElementById('department_id').value;
                    break;
                
                case 'GM':
                    levelType = 'division';
                    levelId = document.getElementById('division_id').value;
                    break;
                
                case 'Director':
                    levelType = 'plant';
                    levelId = document.getElementById('plant_id').value;
                    break;
            }
            
            if (!levelId) return;

            // Kirim request AJAX
            fetch(`/get-competencies?position=${position}&${levelType}_id=${levelId}`)
                .then(response => response.json())
                .then(data => {
                    const competencySelect = document.getElementById('competency_id');
                    competencySelect.innerHTML = '<option value="" disabled selected>-- Select Competency --</option>';
                    
                    data.forEach(comp => {
                        const option = document.createElement('option');
                        option.value = comp.id;
                        option.textContent = comp.name;
                        competencySelect.appendChild(option);
                    });
                    
                    // Tampilkan competency field
                    document.getElementById('competency_field').classList.remove('d-none');
                    document.getElementById('competency_id').required = true;
                });
        }

        // Reset form saat modal ditutup
        const addModal = document.getElementById('addModal');
        addModal.addEventListener('hidden.bs.modal', function() {
            levelFields.forEach(field => {
                document.getElementById(field.id).classList.add('d-none');
                document.querySelector(`#${field.id} select`).required = false;
                document.querySelector(`#${field.id} select`).value = '';
            });
            
            document.getElementById('competency_field').classList.add('d-none');
            document.getElementById('competency_id').required = false;
            document.getElementById('competency_id').innerHTML = '<option value="" disabled selected>-- Select Competency --</option>';
            
            document.getElementById('addForm').reset();
        });
    });
</script>