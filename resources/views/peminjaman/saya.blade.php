@extends('layouts.app')
@section('judul', 'Peminjaman Saya')
@section('isi')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h4 class="mb-0">Peminjaman Saya</h4>
        <a href="{{ route('peminjaman.ajukan') }}" class="btn btn-primary">Ajukan Peminjaman</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead><tr><th>Tanggal Pinjam</th><th>Rencana Kembali</th><th>Alat</th><th>Status</th><th>Denda</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($peminjamans as $peminjaman)
                    <tr>
                        <td>{{ $peminjaman->tgl_pinjam }}</td>
                        <td>{{ $peminjaman->tgl_rencana_kembali }}</td>
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
                        <td>
                            @if ($peminjaman->pengembalian)
                            Rp {{ number_format((float) $peminjaman->pengembalian->denda, 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if ($peminjaman->status === 'disetujui')
                            <form action="{{ route('pengembalian.kembalikan', $peminjaman->id_peminjaman) }}" method="POST">
                                @csrf
                                <button class="btn btn-success btn-sm" onclick="return confirm('Yakin ingin mengembalikan alat ini?')">Kembalikan Alat</button>
                            </form>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">Belum ada peminjaman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $peminjamans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
