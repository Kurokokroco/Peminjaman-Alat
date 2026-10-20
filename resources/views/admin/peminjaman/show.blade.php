@extends('layouts.app')
@section('judul', 'Detail Peminjaman')
@section('isi')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h4 class="mb-0">Detail Peminjaman</h4>
        <a href="{{ route('admin.peminjaman.edit', $peminjaman->id_peminjaman) }}" class="btn btn-warning">Ubah</a>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-4">Peminjam</dt><dd class="col-sm-8">{{ $peminjaman->peminjam->nama_lengkap }}</dd>
            <dt class="col-sm-4">Petugas</dt><dd class="col-sm-8">{{ $peminjaman->petugas?->nama_lengkap ?? '-' }}</dd>
            <dt class="col-sm-4">Tanggal Pinjam</dt><dd class="col-sm-8">{{ $peminjaman->tgl_pinjam }}</dd>
            <dt class="col-sm-4">Rencana Kembali</dt><dd class="col-sm-8">{{ $peminjaman->tgl_rencana_kembali }}</dd>
            <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ ucfirst($peminjaman->status) }}</dd>
        </dl>
        <h5>Daftar Alat</h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>Nama Alat</th><th>Jumlah</th></tr></thead>
                <tbody>
                    @foreach ($peminjaman->detail as $detail)
                    <tr><td>{{ $detail->alat->nama_alat }}</td><td>{{ $detail->jumlah }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($peminjaman->pengembalian)
        <p class="mb-0">Dikembalikan {{ $peminjaman->pengembalian->tgl_kembali }}. Denda: Rp {{ number_format((float) $peminjaman->pengembalian->denda, 0, ',', '.') }}.</p>
        @endif
        <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
