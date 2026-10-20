@extends('layouts.app')
@section('judul', 'Ubah Kategori')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Ubah Kategori</h4></div>
    <div class="card-body">
        <form action="{{ route('kategori.update', $kategori->id_kategori) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nama_kategori" class="form-label">Nama Kategori</label>
                <input id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" class="form-control @error('nama_kategori') is-invalid @enderror" required>
                @error('nama_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
