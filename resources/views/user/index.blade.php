@extends('layouts.app')
@section('judul', 'Data User')
@section('isi')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h4 class="mb-0">Data User</h4>
        <a href="{{ route('user.create') }}" class="btn btn-primary">Tambah User</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('user.index') }}" class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <input type="search" name="cari" value="{{ request('cari') }}" class="form-control @error('cari') is-invalid @enderror" placeholder="Cari nama atau username">
                @error('cari')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-auto"><button class="btn btn-primary">Cari</button></div>
            <div class="col-auto"><a href="{{ route('user.index') }}" class="btn btn-secondary">Reset</a></div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Nama Lengkap</th><th>Username</th><th>Role</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->nama_lengkap }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->role }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('user.edit', $user->id_user) }}" class="btn btn-warning btn-sm">Ubah</a>
                            <form action="{{ route('user.destroy', $user->id_user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center">Data user belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
