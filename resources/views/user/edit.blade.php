@extends('layouts.app')
@section('judul', 'Ubah User')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Ubah User</h4></div>
    <div class="card-body">
        <form action="{{ route('user.update', $user->id_user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" class="form-control @error('nama_lengkap') is-invalid @enderror" required>
                @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" name="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-muted">(kosongkan jika tidak diubah)</span></label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                    @foreach (['admin' => 'Admin', 'petugas' => 'Petugas', 'peminjam' => 'Peminjam'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('role', $user->role) === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('user.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
