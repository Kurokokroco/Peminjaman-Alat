@extends('layouts.app')
@section('judul', 'Dashboard')
@section('isi')
<div class="mb-4">
    <h3>Selamat Datang, {{ $pengguna->nama_lengkap }}</h3>
    <p class="text-muted mb-0">Anda login sebagai <strong>{{ $pengguna->role }}</strong>.</p>
</div>
<div class="row g-3">
    @foreach ($ringkasan as $item)
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-{{ $item['warna'] }} h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">{{ $item['label'] }}</h6>
                <p class="display-6 mb-0">{{ $item['jumlah'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
