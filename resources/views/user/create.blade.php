@extends('layouts.app')
@section('judul', 'Tambah User')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Tambah User</h4></div>
    <div class="card-body">
        <form action="{{ route('user.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" class="form-control @error('nama_lengkap') is-invalid @enderror" required>
                @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">Pilih role</option>
                    @foreach (['admin' => 'Admin', 'petugas' => 'Petugas', 'peminjam' => 'Peminjam'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('role') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('user.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
