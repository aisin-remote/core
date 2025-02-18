<div class="modal fade" id="updateAssessmentModal" tabindex="-1" aria-labelledby="updateAssessmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateAssessmentModalLabel">Update Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateAssessmentForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="assessment_id" name="assessment_id">

                    <div class="mb-4">
                        <label for="update_employee_npk" class="form-label">Employee</label>
                        <select class="form-control" id="update_employee_npk" name="employee_npk" required>
                            <option value="">Pilih Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->npk }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="update_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="update_date" name="date" required>
                    </div>

                    <!-- Loop untuk setiap bidang penilaian -->
                    @php
                        $fields = [
                            'vision_business_sense' => 'Vision & Business Sense',
                            'customer_focus' => 'Customer Focus',
                            'interpersonal_skil' => 'Interpersonal Skill',
                            'analysis_judgment' => 'Analysis & Judgment',
                            'planning_driving_action' => 'Planning & Driving Action',
                            'leading_motivating' => 'Leading & Motivating',
                            'teamwork' => 'Teamwork',
                            'drive_courage' => 'Drive & Courage'
                        ];
                    @endphp

                    @foreach ($fields as $key => $label)
                        <div class="mb-4">
                            <label class="form-label">{{ $label }}</label>
                            <div class="d-flex justify-content-between">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $key }}" id="update_{{ $key }}_{{ $i }}" value="{{ $i }}" required>
                                        <label class="form-check-label" for="update_{{ $key }}_{{ $i }}">{{ $i }}</label>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach

                    <div class="mb-4">
                        <label for="update_upload" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="update_upload" name="upload">
                        <p class="mt-2">File saat ini: <a id="current_upload" href="#" target="_blank">Lihat File</a></p>
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div><script>
    document.addEventListener("DOMContentLoaded", function () {
        var updateAssessmentModal = document.getElementById("updateAssessmentModal");

        updateAssessmentModal.addEventListener("show.bs.modal", function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute("data-id");
            var employeeNpk = button.getAttribute("data-employee_npk");
            var date = button.getAttribute("data-date");
            var visionBusinessSense = button.getAttribute("data-vision_business_sense");
            var customerFocus = button.getAttribute("data-customer_focus");
            var interpersonalSkill = button.getAttribute("data-interpersonal_skil");
            var analysisJudgment = button.getAttribute("data-analysis_judgment");
            var planningDrivingAction = button.getAttribute("data-planning_driving_action");
            var leadingMotivating = button.getAttribute("data-leading_motivating");
            var teamwork = button.getAttribute("data-teamwork");
            var driveCourage = button.getAttribute("data-drive_courage");

            // Set nilai form di modal
            document.getElementById("assessment_id").value = id;
            document.getElementById("update_employee_npk").value = employeeNpk;
            document.getElementById("update_date").value = date;

            // Mengatur nilai radio button
            var fields = [
                "vision_business_sense",
                "customer_focus",
                "interpersonal_skil",
                "analysis_judgment",
                "planning_driving_action",
                "leading_motivating",
                "teamwork",
                "drive_courage"
            ];

            fields.forEach(function (field) {
                var value = button.getAttribute("data-" + field);
                if (value) {
                    var radio = document.querySelector(`input[name="${field}"][value="${value}"]`);
                    if (radio) {
                        radio.checked = true;
                    }
                }
            });

            // Set action URL form update
            document.getElementById("updateAssessmentForm").action = "/assessment/" + id;
        });
    });
</script>
