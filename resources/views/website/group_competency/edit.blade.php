@extends('layouts.root.main')

@section('title', $title)
@section('breadcrumbs', $title)

@section('main')
<div class="container">
    <h1 class="mb-4">{{ $title }}</h1>
    <form action="{{ route('group_competency.update', $group_competency->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nama Group Competency</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $group_competency->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ $group_competency->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
