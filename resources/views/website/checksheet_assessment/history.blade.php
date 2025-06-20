@extends('layouts.root.main')

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header">
      <h3>Histori Penilaian Belum Lolos - {{ $competency->name }}</h3>
    </div>
    <div class="card-body">
      @if($failedAttempts->isEmpty())
        <div class="alert alert-info">
          Tidak ada histori penilaian yang belum lolos untuk kompetensi ini.
        </div>
      @else
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Percobaan</th>
              <th>Tanggal Penilaian</th>
              <th>Checksheet Tidak Lolos</th>
            </tr>
          </thead>
          <tbody>
            @foreach($failedAttempts as $attempt => $assessments)
              @php
                $firstAssessment = $assessments->first();
              @endphp
              <tr>
                <td>Percobaan ke-{{ $attempt }}</td>
                <td>{{ $firstAssessment->created_at->format('d M Y H:i') }}</td>
                <td>
                  <ul class="mb-0">
                    @foreach($assessments as $assessment)
                      <li>
                        {{ $assessment->checksheet->name }} 
                        (Skor: {{ $assessment->score }})
                      </li>
                    @endforeach
                  </ul>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div>
            <a href="{{ route('checksheet-assessment.index', [
              'employeeId' => $employeeId,
              'competencyId' => $competency->id
            ]) }}" class="btn btn-secondary px-4 ms-2">
            <i class="bi bi-arrow-left-circle me-2"></i>
              Kembali
            </a>
          </div>
      @endif
    </div>
  </div>
</div>
@endsection