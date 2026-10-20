@extends('layouts.app')
@section('judul', 'Detail Pengembalian')
@section('isi')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h4 class="mb-0">Detail Pengembalian</h4>
        <a href="{{ route('admin.pengembalian.edit', $pengembalian->id_pengembalian) }}" class="btn btn-warning">Ubah</a>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-4">Peminjam</dt><dd class="col-sm-8">{{ $pengembalian->peminjaman->peminjam->nama_lengkap }}</dd>
            <dt class="col-sm-4">Tanggal Kembali</dt><dd class="col-sm-8">{{ $pengembalian->tgl_kembali }}</dd>
            <dt class="col-sm-4">Rencana Kembali</dt><dd class="col-sm-8">{{ $pengembalian->peminjaman->tgl_rencana_kembali }}</dd>
            <dt class="col-sm-4">Hari Terlambat</dt><dd class="col-sm-8">{{ $pengembalian->hari_terlambat }} hari</dd>
            <dt class="col-sm-4">Denda</dt><dd class="col-sm-8">Rp {{ number_format((float) $pengembalian->denda, 0, ',', '.') }}</dd>
        </dl>
        <h5>Alat yang Dikembalikan</h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>Nama Alat</th><th>Jumlah</th></tr></thead>
                <tbody>
                    @foreach ($pengembalian->peminjaman->detail as $detail)
                    <tr><td>{{ $detail->alat->nama_alat }}</td><td>{{ $detail->jumlah }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <a href="{{ route('admin.pengembalian.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
