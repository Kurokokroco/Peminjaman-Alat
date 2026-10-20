@extends('layouts.app')
@section('judul', 'Data Peminjaman')
@section('isi')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h4 class="mb-0">Data Peminjaman</h4>
        <a href="{{ route('admin.peminjaman.create') }}" class="btn btn-primary">Tambah Peminjaman</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Peminjam</th><th>Tanggal</th><th>Alat</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($peminjamans as $peminjaman)
                    <tr>
                        <td>{{ $peminjaman->peminjam->nama_lengkap }}</td>
                        <td>{{ $peminjaman->tgl_pinjam }}<br><small>Kembali: {{ $peminjaman->tgl_rencana_kembali }}</small></td>
                        <td>
                            <ul class="mb-0 ps-3">
                                @foreach ($peminjaman->detail as $detail)
                                <li>{{ $detail->alat->nama_alat }} ({{ $detail->jumlah }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            @php
                                $warnaStatus = ['menunggu' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger', 'dikembalikan' => 'primary'];
                            @endphp
                            <span class="badge text-bg-{{ $warnaStatus[$peminjaman->status] ?? 'secondary' }}">{{ ucfirst($peminjaman->status) }}</span>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.peminjaman.show', $peminjaman->id_peminjaman) }}" class="btn btn-info btn-sm">Detail</a>
                            <a href="{{ route('admin.peminjaman.edit', $peminjaman->id_peminjaman) }}" class="btn btn-warning btn-sm">Ubah</a>
                            <form action="{{ route('admin.peminjaman.destroy', $peminjaman->id_peminjaman) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data peminjaman ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">Belum ada data peminjaman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $peminjamans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
