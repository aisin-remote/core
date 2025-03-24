@extends('layouts.root.main')

@section('title', $title)

@section('breadcrumbs', $title)

@section('main')
<div class="container">
    <h1 class="mb-4">{{ $title }}</h1>
    <form action="{{ route('group_competency.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nama Group Competency</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama group" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Masukkan deskripsi"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
