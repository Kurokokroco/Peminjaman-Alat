@extends('layouts.app')
@section('judul', 'Ajukan Peminjaman')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Ajukan Peminjaman</h4></div>
    <div class="card-body">
        @include('peminjaman._form', [
            'action' => route('peminjaman.store'),
            'ubah' => false,
            'admin' => false,
            'peminjaman' => null,
        ])
        <p class="text-muted small mt-3 mb-0">Stok belum berkurang sampai pengajuan disetujui petugas.</p>
    </div>
</div>
@endsection
