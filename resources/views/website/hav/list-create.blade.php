@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
@endsection

@section('main')
    <div id="kt_app_content" class="app-content  flex-column-fluid ">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container  container-fluid ">
            
            


            <!--begin::Timeline-->
<div class="card">
    
    <div class="mb-18">        
        <!--begin::Heading-->
        <div class="text-center mb-17 mt-8">
            <!--begin::Title-->
            <h3 class="fs-2hx text-gray-900 mb-5">Choose Employee</h3>
            <!--end::Title-->

        </div>  
        <!--end::Heading-->    
        
        <!--begin::Wrapper-->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 gy-10">
                        <!--begin::Item-->
                @foreach ($employees as $item)
                <a href="{{url('hav/generate-create',['id' => $item->id])}}" class="text-gray-900 fw-bold text-hover-primary fs-3">
                    <div class="col text-center mb-9">
                        <!--begin::Photo-->
                        <div class="octagon mx-auto mb-2 d-flex w-150px h-150px bgi-no-repeat bgi-size-contain bgi-position-center" style="background-image:url('{{ asset('storage/' . $item->photo) }}')">
                        </div>
                        <!--end::Photo-->
        
                        <!--begin::Person-->
                        <div class="mb-0">
                            <!--begin::Name-->
                            <h3> {{$item->name}}   </h3>      
                            <!--end::Name-->
                            
                            <!--begin::Position-->
                            <div class="text-muted fs-6 fw-semibold">{{$item->npk}}</div> 
                            <!--begin::Position-->                 
                        </div>
                        <!--end::Person-->                
                    </div>
                </a>  
                @endforeach
                 
        </div>
        <!--end::Wrapper-->    
    </div>
    <!--end::Team-->


</div>
<!--end::Timeline-->




        </div>
    </div>


    <script>
        function addEducation() {
            let container = document.getElementById("education-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("education-entry", "mt-3");
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Degree</label>
                            <input type="text" name="degree[]" class="form-control" placeholder="e.g., S1 - Teknik Industri">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">University</label>
                            <input type="text" name="university[]" class="form-control" placeholder="e.g., Universitas Indonesia">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Year</label>
                            <input type="text" name="year[]" class="form-control" placeholder="e.g., 2019 - 2022">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">Remove</button>
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
        }

        function addWorkExperience() {
            let container = document.getElementById("work-experience-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("work-entry", "mt-3");
            newEntry.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="job_title[]" class="form-control" placeholder="e.g., Human Resource Manager" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="text" name="work_period[]" class="form-control" placeholder="e.g., 2024 - Present" required>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-danger btn-sm " onclick="removeWorkExperience(this)">Remove</button>
            </div>
        `;
            container.appendChild(newEntry);
        }

        function removeWorkExperience(button) {
            button.closest('.work-entry').remove();
        }

        function addPromotion() {
            let container = document.getElementById("promotion-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("promotion-entry", "mt-3");
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Previous Position</label>
                            <input type="text" name="previous_position[]" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Position</label>
                            <input type="text" name="current_position[]" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Promotion Date</label>
                            <input type="date" name="last_promotion_date[]" class="form-control" required>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">Remove</button>
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
        }

        function removeEntry(button) {
            button.closest('.promotion-entry, .education-entry').remove();
        }
    </script>
@endsection

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
