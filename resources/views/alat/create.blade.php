@extends('layouts.app')
@section('judul', 'Tambah Alat')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Tambah Alat</h4></div>
    <div class="card-body">
        <form action="{{ route('alat.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="id_kategori" class="form-label">Kategori</label>
                <select id="id_kategori" name="id_kategori" class="form-select @error('id_kategori') is-invalid @enderror" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($kategoris as $kategori)
                    <option value="{{ $kategori->id_kategori }}" @selected((string) old('id_kategori') === (string) $kategori->id_kategori)>{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
                @error('id_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="nama_alat" class="form-label">Nama Alat</label>
                <input id="nama_alat" name="nama_alat" value="{{ old('nama_alat') }}" class="form-control @error('nama_alat') is-invalid @enderror" required>
                @error('nama_alat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="stok" class="form-label">Stok</label>
                <input id="stok" type="number" min="0" name="stok" value="{{ old('stok', 0) }}" class="form-control @error('stok') is-invalid @enderror" required>
                @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="kondisi" class="form-label">Kondisi</label>
                <select id="kondisi" name="kondisi" class="form-select @error('kondisi') is-invalid @enderror" required>
                    @foreach (['baik' => 'Baik', 'rusak ringan' => 'Rusak Ringan', 'rusak berat' => 'Rusak Berat'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('kondisi', 'baik') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('kondisi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('alat.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
