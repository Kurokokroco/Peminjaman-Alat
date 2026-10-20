@extends('layouts.app')
@section('judul', 'Pantau Pengembalian')
@section('isi')
<div class="card mb-4">
    <div class="card-header"><h4 class="mb-0">Alat yang Sedang Dipinjam</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Peminjam</th><th>Rencana Kembali</th><th>Alat</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($sedangDipinjam as $peminjaman)
                    <tr>
                        <td>{{ $peminjaman->peminjam->nama_lengkap }}</td>
                        <td>{{ $peminjaman->tgl_rencana_kembali }}</td>
                        <td>
                            <ul class="mb-0 ps-3">
                                @foreach ($peminjaman->detail as $detail)
                                <li>{{ $detail->alat->nama_alat }} ({{ $detail->jumlah }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <span class="badge text-bg-success">Sedang dipinjam</span>
                            @if ($peminjaman->terlambat_saat_ini > 0)
                            <div><span class="badge text-bg-danger mt-1">Terlambat {{ $peminjaman->terlambat_saat_ini }} hari</span></div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center">Tidak ada alat yang sedang dipinjam.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sedangDipinjam->links('pagination::bootstrap-5') }}
    </div>
</div>

<div class="card">
    <div class="card-header"><h4 class="mb-0">Riwayat Pengembalian</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Peminjam</th><th>Tanggal Kembali</th><th>Alat</th><th>Terlambat</th><th>Denda</th></tr></thead>
                <tbody>
                    @forelse ($riwayatPengembalian as $peminjaman)
                    <tr>
                        <td>{{ $peminjaman->peminjam->nama_lengkap }}</td>
                        <td>{{ $peminjaman->pengembalian->tgl_kembali }}</td>
                        <td>
                            <ul class="mb-0 ps-3">
                                @foreach ($peminjaman->detail as $detail)
                                <li>{{ $detail->alat->nama_alat }} ({{ $detail->jumlah }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $peminjaman->pengembalian->hari_terlambat }} hari</td>
                        <td>Rp {{ number_format((float) $peminjaman->pengembalian->denda, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">Belum ada riwayat pengembalian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $riwayatPengembalian->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
