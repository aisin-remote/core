@extends('layouts.root.main')

@section('title', $title ?? 'IPA')
@section('breadcrumbs', $title ?? 'IPA')

@push('custom-css')
    <style>
        .container-xxl {
            max-width: 1100px;
        }

        .card-alert {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(2, 6, 23, .06);
        }

        .card-alert .card-body {
            padding: 1.25rem 1.25rem;
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            margin-right: .6rem;
        }

        .alert-warning .alert-icon {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #f59e0b33;
        }

        .alert-info .alert-icon {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #3b82f633;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-4 px-6">
        <div class="card card-alert {{ ($alert['type'] ?? 'info') === 'warning' ? 'alert-warning' : 'alert-info' }}">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-start">
                    <div class="alert-icon">
                        @if (($alert['type'] ?? 'info') === 'warning')
                            <i class="fas fa-exclamation-triangle"></i>
                        @else
                            <i class="fas fa-info-circle"></i>
                        @endif
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">Informasi</h5>
                        <div class="text-muted">{{ $alert['message'] ?? '—' }}</div>
                    </div>
                </div>
                <div>
                    <a href="{{ $alert['cta_route'] ?? '#' }}" class="btn btn-primary btn-sm">
                        {{ $alert['cta_text'] ?? 'Ke Halaman IPP' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
