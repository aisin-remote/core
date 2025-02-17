<div class="modal fade" id="addAssessmentModal" tabindex="-1" aria-labelledby="addAssessmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssessmentModalLabel">Tambah Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assessmentForm">
                    @csrf
                    <div class="mb-4">
                        <label for="employee_npk" class="form-label">Employee</label>
                        <select class="form-control" id="employee_npk" name="employee_npk" required>
                            <option value="">Pilih Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->npk }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>

                    <!-- Vision & Business Sense -->
                    <div class="mb-4">
                        <label class="form-label">Vision & Business Sense</label>
                        <div class="d-flex justify-content-between">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="vision_business_sense" id="vision_business_sense" value="{{ $i }}" required>
                                    <label class="form-check-label" for="vision_business_sense">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    </div>


                    <!-- Customer Focus -->
                    <div class="mb-4">
                        <label class="form-label">Customer Focus</label>
                        <div class="d-flex justify-content-between">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="customer_focus" id="customer_focus_{{ $i }}" value="{{ $i }}" required>
                                    <label class="form-check-label" for="customer_focus">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Interpersonal Skill -->
                    <div class="mb-4">
                        <label class="form-label">Interpersonal Skill</label>
                        <div class="d-flex justify-content-between">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="interpersonal_skil" id="interpersonal_skil" value="{{ $i }}" required>
                                    <label class="form-check-label" for="interpersonal_skil">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Analysis & Judgment -->
                    <div class="mb-4">
                        <label class="form-label">Analysis & Judgment</label>
                        <div class="d-flex justify-content-between">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="analysis_judgment" id="analysis_judgment" value="{{ $i }}" required>
                                    <label class="form-check-label" for="analysis_judgment">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Planning & Driving Action -->
                    <div class="mb-4">
                        <label class="form-label">Planning & Driving Action</label>
                        <div class="d-flex justify-content-between">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="planning_driving_action" id="planning_driving_action" value="{{ $i }}" required>
                                    <label class="form-check-label" for="planning_driving_action">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Leading & Motivating -->
                    <div class="mb-4">
                        <label class="form-label">Leading & Motivating</label>
                        <div class="d-flex justify-content-between">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="leading_motivating" id="leading_motivating" value="{{ $i }}" required>
                                    <label class="form-check-label" for="leading_motivating">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Teamwork -->
                    <div class="mb-4">
                        <label class="form-label">Teamwork</label>
                        <div class="d-flex justify-content-between">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="teamwork" id="teamwork" value="{{ $i }}" required>
                                    <label class="form-check-label" for="teamwork">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Drive & Courage -->
                    <div class="mb-4">
                        <label class="form-label">Drive & Courage</label>
                        <div class="d-flex justify-content-between">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="drive_courage" id="drive_courage" value="{{ $i }}" required>
                                    <label class="form-check-label" for="drive_courage">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="upload" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="upload" name="upload">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
