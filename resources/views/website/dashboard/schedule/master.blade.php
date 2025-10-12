@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Dashboard' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Dashboard' }}
@endsection


@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <img src="{{ asset('assets/media/website/master.jpeg') }}" alt="" width="1100px">
    </div>
@endsection
