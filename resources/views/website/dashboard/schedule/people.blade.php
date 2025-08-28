@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Dashboard' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Dashboard' }}
@endsection


@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid text-center">
        <img src="{{ asset('assets/media/website/people.png') }}" alt="" width="900px">
    </div>
@endsection
