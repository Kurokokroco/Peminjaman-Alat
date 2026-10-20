@extends('layouts.app')
@section('judul', 'Data Alat')
@section('isi')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h4 class="mb-0">Data Alat</h4>
        <a href="{{ route('alat.create') }}" class="btn btn-primary">Tambah Alat</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('alat.index') }}" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <input type="search" name="cari" value="{{ request('cari') }}" class="form-control @error('cari') is-invalid @enderror" placeholder="Cari nama alat">
                @error('cari')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <select name="id_kategori" class="form-select @error('id_kategori') is-invalid @enderror">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoris as $kategori)
                    <option value="{{ $kategori->id_kategori }}" @selected((string) request('id_kategori') === (string) $kategori->id_kategori)>{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
                @error('id_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-auto"><button class="btn btn-primary">Cari</button></div>
            <div class="col-auto"><a href="{{ route('alat.index') }}" class="btn btn-secondary">Reset</a></div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Nama Alat</th><th>Kategori</th><th>Stok</th><th>Kondisi</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($alats as $alat)
                    <tr>
                        <td>{{ $alat->nama_alat }}</td>
                        <td>{{ $alat->kategori->nama_kategori }}</td>
                        <td>{{ $alat->stok }}</td>
                        <td>{{ $alat->kondisi }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('alat.edit', $alat->id_alat) }}" class="btn btn-warning btn-sm">Ubah</a>
                            <form action="{{ route('alat.destroy', $alat->id_alat) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus alat ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">Data alat belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $alats->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
