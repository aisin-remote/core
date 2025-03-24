@extends('layouts.root.main')

@section('title', $title ?? 'Edit Emp Competency')
@section('breadcrumbs', $title ?? 'Edit Emp Competency')

@section('main')
<div class="container">
    <h1 class="mb-4">{{ $title }}</h1>
    <form action="{{ route('emp_competency.update', [$empCompetency['competency_id'], $empCompetency['employee_id']]) }}" method="POST">
         @csrf
         @method('PUT')
         <div class="mb-3">
             <label for="competency_id" class="form-label">Competency ID</label>
             <input type="number" class="form-control" id="competency_id" name="competency_id" value="{{ old('competency_id', $empCompetency['competency_id']) }}" required>
         </div>
         <div class="mb-3">
             <label for="employee_id" class="form-label">Employee ID</label>
             <input type="number" class="form-control" id="employee_id" name="employee_id" value="{{ old('employee_id', $empCompetency['employee_id']) }}" required>
         </div>
         <div class="mb-3">
             <label for="act" class="form-label">Act</label>
             <input type="number" class="form-control" id="act" name="act" value="{{ old('act', $empCompetency['act']) }}" required>
         </div>
         <div class="mb-3">
             <label for="plan" class="form-label">Plan</label>
             <input type="number" class="form-control" id="plan" name="plan" value="{{ old('plan', $empCompetency['plan']) }}" required>
         </div>
         <div class="mb-3">
             <label for="progress" class="form-label">Progress</label>
             <input type="number" class="form-control" id="progress" name="progress" value="{{ old('progress', $empCompetency['progress']) }}" required>
         </div>
         <div class="mb-3">
             <label for="weight" class="form-label">Weight</label>
             <input type="number" class="form-control" id="weight" name="weight" value="{{ old('weight', $empCompetency['weight']) }}" required>
         </div>
         <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
