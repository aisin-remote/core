@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

<style>
    .org-chart {
        text-align: center;
    }

    .leader {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        padding: 20px;
        width: 300px;
        border-radius: 10px;
        display: inline-block;
        margin-bottom: 20px;
        position: relative;
    }

    /* Garis ke bawah dari leader */
    .leader::after {
        content: "";
        width: 2px;
        height: 30px;
        background: #dee2e6;
        position: absolute;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
    }

    .gm {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        padding: 20px;
        width: 250px;
        border-radius: 10px;
        text-align: center;
        position: relative;
        /* Tambahkan ini */
    }

    /* Garis vertikal di bawah GM */
    .gm::after {
        content: "";
        width: 2px;
        height: 30px;
        background: #dee2e6;
        position: absolute;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
    }

    .org-chart {
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Memastikan semua elemen di tengah */
    }

    .leader,
    .gm {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        padding: 20px;
        width: 250px;
        border-radius: 10px;
        text-align: center;
    }

    /* Garis vertikal penghubung antara Director dan GM */
    .connector {
        width: 2px;
        height: 30px;
        background: #dee2e6;
    }

    /* Kontainer tim */
    .team {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        padding-top: 20px;
        position: relative;
    }

    /* Garis horizontal penghubung antar tim */
    .team::before {
        content: "";
        width: 100%;
        height: 2px;
        background: #dee2e6;
        position: absolute;
        top: 0;
    }

    /* Garis vertikal dari tiap tim ke anggotanya */
    .team-title {
        font-size: 14px;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
        display: inline-block;
        margin-bottom: 10px;
        position: relative;
    }

    .team-title::after {
        content: "";
        width: 2px;
        height: 20px;
        background: #dee2e6;
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
    }

    .member {
        background: white;
        border: 1px solid #dee2e6;
        padding: 15px;
        width: 300px;
        border-radius: 10px;
        text-align: center;
        min-width: 150px;
    }

    /* Warna masing-masing tim */
    .designers {
        background: #fd7e14;
        color: white;
    }

    .developers {
        background: #0d6efd;
        color: white;
    }

    .qa {
        background: #6f42c1;
        color: white;
    }

    .scrum {
        background: #ffc107;
        color: black;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .team {
            flex-direction: column;
        }

        .team::before {
            display: none;
        }
    }
</style>

@section('main')
    <div class="d-flex flex-column flex-column-fluid">

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content  flex-column-fluid ">


            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container  container-fluid ">
                <div class="card">
                    <img src="{{ asset('assets/media/website/rtc.png') }}" alt="" width="1200px">
                </div>
            </div>
        </div>
    </div>
@endsection
