@extends('layouts.app')
@section('judul', 'Daftar Alat')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Daftar Alat</h4></div>
    <div class="card-body">
        <form method="GET" action="{{ route('alat.daftar') }}" class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <input type="search" name="cari" value="{{ request('cari') }}" class="form-control @error('cari') is-invalid @enderror" placeholder="Cari nama alat">
                @error('cari')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-auto"><button class="btn btn-primary">Cari</button></div>
            <div class="col-auto"><a href="{{ route('alat.daftar') }}" class="btn btn-secondary">Reset</a></div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Nama Alat</th><th>Kategori</th><th>Stok</th><th>Kondisi</th></tr></thead>
                <tbody>
                    @forelse ($alats as $alat)
                    <tr>
                        <td>{{ $alat->nama_alat }}</td>
                        <td>{{ $alat->kategori->nama_kategori }}</td>
                        <td>{{ $alat->stok }}</td>
                        <td>{{ $alat->kondisi }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center">Data alat belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $alats->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
