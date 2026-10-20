<form action="{{ $action }}" method="POST">
    @csrf
    @if ($ubah)
    @method('PUT')
    @endif

    @if ($admin)
    <div class="mb-3">
        <label for="id_peminjam" class="form-label">Peminjam</label>
        <select id="id_peminjam" name="id_peminjam" class="form-select @error('id_peminjam') is-invalid @enderror" required>
            <option value="">Pilih peminjam</option>
            @foreach ($peminjam as $pengguna)
            <option value="{{ $pengguna->id_user }}" @selected((string) old('id_peminjam', $peminjaman?->id_peminjam) === (string) $pengguna->id_user)>{{ $pengguna->nama_lengkap }} ({{ $pengguna->username }})</option>
            @endforeach
        </select>
        @error('id_peminjam')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-6 mb-3">
            <label for="tgl_pinjam" class="form-label">Tanggal Pinjam</label>
            <input id="tgl_pinjam" type="date" name="tgl_pinjam" value="{{ old('tgl_pinjam', $peminjaman?->tgl_pinjam ?? now()->toDateString()) }}" class="form-control @error('tgl_pinjam') is-invalid @enderror" required>
            @error('tgl_pinjam')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label for="tgl_rencana_kembali" class="form-label">Tanggal Rencana Kembali</label>
            <input id="tgl_rencana_kembali" type="date" name="tgl_rencana_kembali" value="{{ old('tgl_rencana_kembali', $peminjaman?->tgl_rencana_kembali) }}" class="form-control @error('tgl_rencana_kembali') is-invalid @enderror" required>
            @error('tgl_rencana_kembali')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    @if ($admin && $ubah)
    <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statusOptions as $nilai => $label)
            <option value="{{ $nilai }}" @selected(old('status', $peminjaman->status) === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Perubahan status disetujui menyesuaikan stok secara otomatis.</div>
    </div>
    @endif

    <div class="mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <label class="form-label mb-0">Daftar Alat</label>
            <button id="tambah-alat" type="button" class="btn btn-outline-primary btn-sm">Tambah alat</button>
        </div>
        @error('alat')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
        <div id="daftar-alat">
            @foreach ($barisAlat as $indeks => $baris)
            <div class="row g-2 align-items-start mb-2 baris-alat">
                <div class="col-12 col-md-7">
                    <select name="alat[{{ $indeks }}][id_alat]" class="form-select @error('alat.'.$indeks.'.id_alat') is-invalid @enderror" required>
                        <option value="">Pilih alat</option>
                        @foreach ($alats as $alat)
                        <option value="{{ $alat->id_alat }}" @selected((string) ($baris['id_alat'] ?? '') === (string) $alat->id_alat)>{{ $alat->nama_alat }} (stok: {{ $alat->stok }})</option>
                        @endforeach
                    </select>
                    @error('alat.'.$indeks.'.id_alat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-8 col-md-3">
                    <input type="number" name="alat[{{ $indeks }}][jumlah]" min="1" value="{{ $baris['jumlah'] ?? 1 }}" class="form-control @error('alat.'.$indeks.'.jumlah') is-invalid @enderror" placeholder="Jumlah" required>
                    @error('alat.'.$indeks.'.jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-4 col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 hapus-alat">Hapus</button>
                </div>
            </div>
            @endforeach
        </div>
        @error('stok')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    </div>

    <button class="btn btn-primary">{{ $ubah ? 'Simpan Perubahan' : 'Kirim Pengajuan' }}</button>
    <a href="{{ $admin ? route('admin.peminjaman.index') : route('peminjaman.saya') }}" class="btn btn-secondary">Batal</a>
</form>

<template id="template-alat">
    <div class="row g-2 align-items-start mb-2 baris-alat">
        <div class="col-12 col-md-7">
            <select name="alat[__INDEX__][id_alat]" class="form-select" required>
                <option value="">Pilih alat</option>
                @foreach ($alats as $alat)
                <option value="{{ $alat->id_alat }}">{{ $alat->nama_alat }} (stok: {{ $alat->stok }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-8 col-md-3">
            <input type="number" name="alat[__INDEX__][jumlah]" min="1" value="1" class="form-control" placeholder="Jumlah" required>
        </div>
        <div class="col-4 col-md-2">
            <button type="button" class="btn btn-outline-danger w-100 hapus-alat">Hapus</button>
        </div>
    </div>
</template>

<script>
    const daftarAlat = document.getElementById('daftar-alat');
    const templateAlat = document.getElementById('template-alat');
    let indeksAlat = {{ count($barisAlat) }};

    document.getElementById('tambah-alat').addEventListener('click', function () {
        const barisBaru = templateAlat.content.cloneNode(true);
        barisBaru.querySelectorAll('[name]').forEach(function (input) {
            input.name = input.name.replace('__INDEX__', indeksAlat);
        });
        daftarAlat.appendChild(barisBaru);
        // Indeks baru mencegah nama input bertabrakan dengan baris sebelumnya.
        indeksAlat++;
    });

    daftarAlat.addEventListener('click', function (event) {
        if (event.target.classList.contains('hapus-alat') && daftarAlat.children.length > 1) {
            event.target.closest('.baris-alat').remove();
        }
    });
</script>
