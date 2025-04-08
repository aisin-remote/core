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
                <div class="org-chart mb-8">
                    <!-- Director -->
                    <div class="leader">
                        <h4>Dedi Irwanto</h4>
                        <span class="badge badge-primary p-2 px-6">Director</span>
                    </div>

                    <!-- Garis ke bawah -->
                    <div class="connector"></div>

                    <!-- GM -->
                    <div class="gm">
                        <h4>Ricky P</h4>
                        <span class="badge badge-info p-2 px-6" style="background-color: #fd7e14 !important">GM ENG</span>
                    </div>
                </div>

                <!-- Teams -->
                <div class="org-chart">
                    <div class="row justify-content-center">
                        <!-- Designers -->
                        <div class="col-md-3 text-center">
                            <span class="team-title designers">ENG Body</span>
                            <div class="team">
                                <div class="member p-3 border rounded shadow-sm bg-light" style="width: 300px;">
                                    <div class="fw-bold fs-4 fst-italic">Lutfi Dahlan</div>
                                    <hr class="my-2">
                                    <div class="container text-start my-5">
                                        <div>Age: -</div>
                                        <div>LOS: -</div>
                                        <div>LCP: -</div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="fw-bold">Candidates:</div>
                                    <div class="container text-start my-5">
                                        <div><strong>S/T:</strong> <span class="ms-4">-</span></div>
                                        <div><strong>M/T:</strong> <span class="ms-4">-</span></div>
                                        <div><strong>L/T:</strong> <span class="ms-4">-</span></div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="small text-muted fst-italic">(Gol, Usia, HAV)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Developers -->
                        <div class="col-md-3 text-center">
                            <span class="team-title developers">ENG Unit</span>
                            <div class="team">
                                <div class="member p-3 border rounded shadow-sm bg-light" style="width: 300px;">
                                    <div class="fw-bold text-danger fs-4 fst-italic">Ricky P</div>
                                    <hr class="my-2">
                                    <div class="container text-start my-5">
                                        <div>Age: -</div>
                                        <div>LOS: -</div>
                                        <div>LCP: -</div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="fw-bold">Candidates:</div>
                                    <div class="container text-start my-5">
                                        <div><strong>S/T:</strong> <span class="ms-4">-</span></div>
                                        <div><strong>M/T:</strong> <span class="ms-4">-</span></div>
                                        <div><strong>L/T:</strong> <span
                                                class="mx-4 badge badge-primary">Ikhsanudin</span>(4D,
                                            35,
                                            8)</div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="small text-muted fst-italic">(Gol, Usia, HAV)</div>
                                </div>
                            </div>
                        </div>

                        <!-- QA Engineers -->
                        <div class="col-md-3 text-center">
                            <span class="team-title qa">QA Unit</span>
                            <div class="team">
                                <div class="member p-3 border rounded shadow-sm bg-light" style="width: 300px;">
                                    <div class="fw-bold fs-4 fst-italic">Junjunan</div>
                                    <hr class="my-2">
                                    <div class="container text-start my-5">
                                        <div>Age: -</div>
                                        <div>LOS: -</div>
                                        <div>LCP: -</div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="fw-bold">Candidates:</div>
                                    <div class="container text-start my-5">
                                        <div><strong>S/T:</strong> <span class="ms-4">-</span></div>
                                        <div><strong>M/T:</strong> <span class="ms-4">-</span></div>
                                        <div><strong>L/T:</strong> <span class="mx-4 badge badge-primary">Joni</span>(4D,
                                            35,
                                            8)</div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="small text-muted fst-italic">(Gol, Usia, HAV)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Scrum Master -->
                        <div class="col-md-3 text-center">
                            <span class="team-title scrum">QA Body</span>
                            <div class="team">
                                <div class="member p-3 border rounded shadow-sm bg-light" style="width: 300px;">
                                    <div class="fw-bold fs-4 fst-italic">Harpan Sapli</div>
                                    <hr class="my-2">
                                    <div class="container text-start my-5">
                                        <div>Age: -</div>
                                        <div>LOS: -</div>
                                        <div>LCP: -</div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="fw-bold">Candidates:</div>
                                    <div class="container text-start my-5">
                                        <div><strong>S/T:</strong> <span class="ms-4">-</span></div>
                                        <div><strong>M/T:</strong> <span class="ms-4">-</span></div>
                                        <div><strong>L/T:</strong> <span class="mx-4 badge badge-primary">Aji M.</span>(4D,
                                            35,
                                            8)</div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="small text-muted fst-italic">(Gol, Usia, HAV)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
