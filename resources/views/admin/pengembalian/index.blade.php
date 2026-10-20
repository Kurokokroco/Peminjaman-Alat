@extends('layouts.app')
@section('judul', 'Data Pengembalian')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Data Pengembalian</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Peminjam</th><th>Tanggal Kembali</th><th>Terlambat</th><th>Denda</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($pengembalians as $pengembalian)
                    <tr>
                        <td>{{ $pengembalian->peminjaman->peminjam->nama_lengkap }}</td>
                        <td>{{ $pengembalian->tgl_kembali }}</td>
                        <td>{{ $pengembalian->hari_terlambat }} hari</td>
                        <td>Rp {{ number_format((float) $pengembalian->denda, 0, ',', '.') }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.pengembalian.show', $pengembalian->id_pengembalian) }}" class="btn btn-info btn-sm">Detail</a>
                            <a href="{{ route('admin.pengembalian.edit', $pengembalian->id_pengembalian) }}" class="btn btn-warning btn-sm">Ubah</a>
                            <form action="{{ route('admin.pengembalian.destroy', $pengembalian->id_pengembalian) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus pengembalian dan aktifkan kembali peminjaman?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">Belum ada data pengembalian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pengembalians->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
