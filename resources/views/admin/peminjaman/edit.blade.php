@extends('layouts.app')
@section('judul', 'Ubah Peminjaman')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Ubah Peminjaman</h4></div>
    <div class="card-body">
        @include('peminjaman._form', [
            'action' => route('admin.peminjaman.update', $peminjaman->id_peminjaman),
            'ubah' => true,
            'admin' => true,
        ])
    </div>
</div>
@endsection
