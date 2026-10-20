@extends('layouts.app')
@section('judul', 'Kategori')
@section('isi')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h4 class="mb-0">Data Kategori</h4>
        <a href="{{ route('kategori.create') }}" class="btn btn-primary">Tambah Kategori</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Nama Kategori</th><th>Jumlah Alat</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($kategoris as $kategori)
                    <tr>
                        <td>{{ $kategori->nama_kategori }}</td>
                        <td>{{ $kategori->alat_count }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('kategori.edit', $kategori->id_kategori) }}" class="btn btn-warning btn-sm">Ubah</a>
                            <form action="{{ route('kategori.destroy', $kategori->id_kategori) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center">Data kategori belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $kategoris->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
