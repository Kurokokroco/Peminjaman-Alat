@extends('layouts.app')
@section('judul', 'Log Aktivitas')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Log Aktivitas</h4></div>
    <div class="card-body">
        <form action="{{ route('log.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <label for="id_user" class="form-label">Pengguna</label>
                <select id="id_user" name="id_user" class="form-select @error('id_user') is-invalid @enderror">
                    <option value="">Semua pengguna</option>
                    @foreach ($pengguna as $user)
                    <option value="{{ $user->id_user }}" @selected((string) request('id_user') === (string) $user->id_user)>{{ $user->nama_lengkap }} ({{ $user->username }})</option>
                    @endforeach
                </select>
                @error('id_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label for="tanggal_dari" class="form-label">Dari Tanggal</label>
                <input id="tanggal_dari" type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control @error('tanggal_dari') is-invalid @enderror">
                @error('tanggal_dari')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label for="tanggal_sampai" class="form-label">Sampai Tanggal</label>
                <input id="tanggal_sampai" type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control @error('tanggal_sampai') is-invalid @enderror">
                @error('tanggal_sampai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('log.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Nama User</th><th>Aktivitas</th><th>Waktu</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->user->nama_lengkap }}</td>
                        <td>{{ $log->aktivitas }}</td>
                        <td>{{ $log->waktu }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center">Belum ada aktivitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
