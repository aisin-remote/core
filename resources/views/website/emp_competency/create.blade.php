@extends('layouts.root.main')

@section('title', $title ?? 'Create Emp Competency')
@section('breadcrumbs', $title ?? 'Create Emp Competency')

@section('main')
<div class="container">
    <h1 class="mb-4">{{ $title }}</h1>
    <form action="{{ route('emp_competency.store') }}" method="POST">
         @csrf
         <div class="mb-3">
             <label for="competency_id" class="form-label">Competency ID</label>
             <input type="number" class="form-control" id="competency_id" name="competency_id" placeholder="Masukkan Competency ID" required>
         </div>
         <div class="mb-3">
             <label for="employee_id" class="form-label">Employee ID</label>
             <input type="number" class="form-control" id="employee_id" name="employee_id" placeholder="Masukkan Employee ID" required>
         </div>
         <div class="mb-3">
             <label for="act" class="form-label">Act</label>
             <input type="number" class="form-control" id="act" name="act" placeholder="Masukkan Act" required>
         </div>
         <div class="mb-3">
             <label for="plan" class="form-label">Plan</label>
             <input type="number" class="form-control" id="plan" name="plan" placeholder="Masukkan Plan" required>
         </div>
         <div class="mb-3">
             <label for="progress" class="form-label">Progress</label>
             <input type="number" class="form-control" id="progress" name="progress" placeholder="Masukkan Progress" required>
         </div>
         <div class="mb-3">
             <label for="weight" class="form-label">Weight</label>
             <input type="number" class="form-control" id="weight" name="weight" placeholder="Masukkan Weight" required>
         </div>
         <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
