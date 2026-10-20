@extends('layouts.app')
@section('judul', 'Persetujuan Peminjaman')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Persetujuan Peminjaman</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Peminjam</th><th>Tanggal Pinjam</th><th>Rencana Kembali</th><th>Daftar Alat</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($peminjamans as $peminjaman)
                    <tr>
                        <td>{{ $peminjaman->peminjam->nama_lengkap }}</td>
                        <td>{{ $peminjaman->tgl_pinjam }}</td>
                        <td>{{ $peminjaman->tgl_rencana_kembali }}</td>
                        <td>
                            <ul class="mb-0 ps-3">
                                @foreach ($peminjaman->detail as $detail)
                                <li>{{ $detail->alat->nama_alat }}: {{ $detail->jumlah }} (stok sekarang: {{ $detail->alat->stok }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-nowrap">
                            <form action="{{ route('peminjaman.setujui', $peminjaman->id_peminjaman) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm" onclick="return confirm('Setujui peminjaman ini?')">Setujui</button>
                            </form>
                            <form action="{{ route('peminjaman.tolak', $peminjaman->id_peminjaman) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Tolak peminjaman ini?')">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">Tidak ada pengajuan yang menunggu persetujuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $peminjamans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
