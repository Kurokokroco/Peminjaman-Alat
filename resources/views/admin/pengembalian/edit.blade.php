@extends('layouts.app')
@section('judul', 'Ubah Pengembalian')
@section('isi')
<div class="card">
    <div class="card-header"><h4 class="mb-0">Ubah Pengembalian</h4></div>
    <div class="card-body">
        <p>Peminjam: <strong>{{ $pengembalian->peminjaman->peminjam->nama_lengkap }}</strong></p>
        <p>Tanggal rencana kembali: <strong>{{ $pengembalian->peminjaman->tgl_rencana_kembali }}</strong></p>
        <form action="{{ route('admin.pengembalian.update', $pengembalian->id_pengembalian) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="tgl_kembali" class="form-label">Tanggal Kembali</label>
                <input id="tgl_kembali" type="date" name="tgl_kembali" value="{{ old('tgl_kembali', $pengembalian->tgl_kembali) }}" class="form-control @error('tgl_kembali') is-invalid @enderror" max="{{ now()->toDateString() }}" required>
                @error('tgl_kembali')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Hari keterlambatan dan denda akan dihitung ulang saat disimpan.</div>
            </div>
            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('admin.pengembalian.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
