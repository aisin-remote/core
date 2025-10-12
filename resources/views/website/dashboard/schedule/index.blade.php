@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Schedule' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Schedule' }}
@endsection


@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid text-center">
        <img src="{{ asset('assets/media/website/master.jpeg') }}" alt="" width="1100px" class="mb-8">
        <img src="{{ asset('assets/media/website/people.png') }}" alt="" width="900px" class="mb-8">
        <img src="{{ asset('assets/media/website/dashboard.jpeg') }}" alt="" width="1100px" class="mb-8">
    </div>
@endsection
