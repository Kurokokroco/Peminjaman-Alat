@extends('layouts.app')
@section('judul', 'Tambah Kategori')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Tambah Kategori</h4></div>
    <div class="card-body">
        <form action="{{ route('kategori.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nama_kategori" class="form-label">Nama Kategori</label>
                <input id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori') }}" class="form-control @error('nama_kategori') is-invalid @enderror" required>
                @error('nama_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
