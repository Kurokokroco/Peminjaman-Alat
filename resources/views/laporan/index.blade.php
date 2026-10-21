@extends('layouts.app')
@section('judul', 'Laporan Peminjaman')
@section('isi')
<style>
    @media print {
        body {
            background: #fff !important;
        }

        .navbar,
        .filter-form,
        .no-print,
        nav[aria-label="Pagination Navigation"] {
            display: none !important;
        }

        .container {
            max-width: 100% !important;
            padding: 0 !important;
        }

        .card {
            border: 0 !important;
        }

        .card-body {
            padding: 0 !important;
        }

        .judul-cetak {
            display: block !important;
        }

        table {
            font-size: 10pt;
        }
    }
</style>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 no-print">
        <h4 class="mb-0">Laporan Peminjaman</h4>
        <button id="tombol-cetak" type="button" class="btn btn-success">Cetak</button>
    </div>
    <div class="card-body">
        <div class="judul-cetak d-none d-print-block mb-3">
            <h2>Laporan Peminjaman Alat</h2>
            <p class="mb-1">Tanggal cetak: {{ now()->format('d-m-Y H:i') }}</p>
            <p>Dicetak oleh: {{ $pencetak->nama_lengkap }}</p>
        </div>

        <form action="{{ route('laporan.index') }}" method="GET" class="row g-2 mb-3 filter-form">
            <div class="col-12 col-md-3">
                <label for="tanggal_dari" class="form-label">Tanggal Pinjam Dari</label>
                <input id="tanggal_dari" type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control @error('tanggal_dari') is-invalid @enderror">
                @error('tanggal_dari')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label for="tanggal_sampai" class="form-label">Tanggal Pinjam Sampai</label>
                <input id="tanggal_sampai" type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control @error('tanggal_sampai') is-invalid @enderror">
                @error('tanggal_sampai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="">Semua status</option>
                    @foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'dikembalikan' => 'Dikembalikan'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex align-items-end gap-2">
                <button class="btn btn-primary">Tampilkan</button>
                <a href="{{ route('laporan.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama Peminjam</th>
                        <th>Alat Dipinjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Tgl Kembali</th>
                        <th>Hari Terlambat</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peminjamans as $peminjaman)
                    <tr>
                        <td>{{ $peminjaman->peminjam->nama_lengkap }}</td>
                        <td>
                            <ul class="mb-0 ps-3">
                                @foreach ($peminjaman->detail as $detail)
                                <li>{{ $detail->alat->nama_alat }} ({{ $detail->jumlah }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $peminjaman->tgl_pinjam }}</td>
                        <td>{{ $peminjaman->tgl_rencana_kembali }}</td>
                        <td>{{ $peminjaman->pengembalian?->tgl_kembali ?? '-' }}</td>
                        <td>{{ $peminjaman->pengembalian?->hari_terlambat ?? 0 }}</td>
                        <td>Rp {{ number_format((float) ($peminjaman->pengembalian?->denda ?? 0), 0, ',', '.') }}</td>
                        <td>{{ ucfirst($peminjaman->status) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center">Tidak ada data sesuai filter.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Total denda sesuai filter</th>
                        <th colspan="2">Rp {{ number_format((float) $totalDenda, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        {{ $peminjamans->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
    document.getElementById('tombol-cetak').addEventListener('click', async function () {
        const tombol = this;
        tombol.disabled = true;

        try {
            const respons = await fetch(@json(route('laporan.cetak')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                },
            });

            if (!respons.ok) {
                throw new Error('Log cetak laporan gagal disimpan.');
            }

            window.print();
        } catch (kesalahan) {
            alert(kesalahan.message);
        } finally {
            tombol.disabled = false;
        }
    });
</script>
@endsection
