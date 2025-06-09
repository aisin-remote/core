@extends('layouts.root.main')

@section('title', 'Approval Skill Matrix')

@section('main')
<div class="app-container container-fluid">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Approval Skill Matrix</h3>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if($pending->isEmpty())
        <p class="text-center text-muted">Tidak ada evidence untuk di-approve.</p>
      @else
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Employee</th>
            <th>Competency</th>
            <th>Evidence File</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pending as $i => $ec)
          <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $ec->employee->name }}</td>
            <td>{{ $ec->competency->name }}</td>
            <td>
              <a href="{{ asset('storage/'.$ec->file) }}"
                 class="btn btn-sm btn-secondary"
                 download="{{ basename($ec->file) }}">
                <i class="fas fa-download"></i> Download
              </a>
            </td>
            <td class="text-center">
              <form action="{{ route('skillMatrix.approve', $ec->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                  <i class="fas fa-check"></i> Approve
                </button>
              </form>
              <form action="{{ route('skillMatrix.unapprove', $ec->id) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Unapprove dan hapus evidence?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger">
                  <i class="fas fa-times"></i> Unapprove
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif
    </div>
  </div>
</div>
@endsection
