@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('main')
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <!--begin::Row-->
        <div class="row g-5 gx-xl-10 mb-2 mb-xl-10">
            @php
                $borderColors = [
                    'bg-light-secondary',
                    'bg-light-warning',
                    'bg-light-success',
                    'bg-light-primary',
                    'bg-light-secondary',
                    'bg-light-warning',
                    'bg-light-success',
                    'bg-light-success',
                    'bg-light-secondary',
                    'bg-light-warning',
                    'bg-light-warning',
                    'bg-light-warning',
                    'bg-light-secondary',
                    'bg-light-secondary',
                    'bg-light-secondary',
                    'bg-light-secondary',
                ];

                $percentage = [
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '3.3%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '3.3%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                ];
            @endphp
            <div class="card" style="width: 95%;">
                <div>
                    <div class="text-center mb-3 mt-3">
                        <h3 class="fs-2hx text-gray-900">HAV Quadrant</h3>
                    </div>
                    <div>
                        <a class="btn btn-success btn-sm history-btn" href="{{ route('hav.export') }}"> Download Summary </a>
                    </div>
                </div>
                <div class="row mt-5 pr-10">
                    @foreach ($titles as $i => $title)
                    @php
                        $havList = $orderedHavGrouped[$i] ?? collect();
                        $colorClass = $borderColors[$loop->index] ?? 'bg-light-secondary';
                        $persen = $havList->count() > 0
                            ? number_format(($havList->count() / $orderedHavGrouped->flatten(1)->count()) * 100, 1) . '%'
                            : '0.0%';
                            $jsonData = $havList->map(function ($h) {
                                // dd($h);
                                return [
                                    'npk' => $h->employee->npk ?? '-',
                                    'name' => $h->employee->name ?? '-',
                                    'department' => $h->employee->departments[0]->name ?? '-',
                                    'grade' => $h->employee->grade ?? '-',
                                ];
                            });
                    @endphp
                
                    <div class="col-3">
                        <a href="#"
                            class="open-modal"
                            data-id="{{ $i }}"
                            data-title="Quadrant {{ $i }} - {{ $title }}"
                            data-hav='@json($jsonData)'
                            data-toggle="modal"
                            data-target="#tes">
                            <div class="card {{ $colorClass }} card-md-stretch mb-xl-6 card-clickable">
                                <div class="card-body" style="padding: 10px;">
                                    <div class="card-title fw-bold text-center text-dark fs-5 mb-3 d-block">
                                         {{ $i }}. {{ $title }}
                                    </div>
                                    <div class="card-body bg-white text-center" style="height: 50px;">
                                        <h1>
                                            <span class="text-dark fw-bold me-2">{{ $havList->count() }}</span>
                                        </h1>
                                    </div>
                                    <div class="py-1 text-center">
                                        {{-- <span class="text-danger fw-bold me-2">{{ $persen }}</span> --}}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach


                </div>
            </div>
            <!--end::Content container-->
        </div>
    </div>

    <div class="modal fade" id="tes" tabindex="-1" aria-labelledby="addAssessmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAssessmentModalLabel">Create Assessment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                </div>
            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function () {
            $('.open-modal').on('click', function (e) {
                e.preventDefault();
        
                let title = $(this).data('title');
                let data = $(this).data('hav'); // sudah array of objects
                $('#addAssessmentModalLabel').text(title);

                console.log(data);
        
                // Build HTML table
                if (data.length === 0) {
                    $('#tes .modal-body').html('<p class="text-center">Belum ada data untuk quadrant ini.</p>');
                    return;
                }
        
                let html = `
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>NPK</th>
                                <th>Nama</th>
                                <th>Department</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
        
                data.forEach(function (item) {
                    html += `
                        <tr>
                            <td>${item.npk}</td>
                            <td>${item.name}</td>
                            <td>${item.department}</td>
                            <td>${item.grade}</td>
                        </tr>
                    `;
                });
        
                html += `</tbody></table>`;
                $('#tes .modal-body').html(html);
        
                $('#tes').modal('show');
            });
        });
        </script>
        
        
@endpush
