@extends('layouts.app')
@section('judul', 'Tambah Peminjaman')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Tambah Peminjaman</h4></div>
    <div class="card-body">
        @include('peminjaman._form', [
            'action' => route('admin.peminjaman.store'),
            'ubah' => false,
            'admin' => true,
            'peminjaman' => null,
        ])
    </div>
</div>
@endsection
