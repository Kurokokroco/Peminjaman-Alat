<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    public function ajukan()
    {
        $alats = Alat::where('stok', '>', 0)
            ->orderBy('nama_alat')
            ->get();
        $barisAlat = old('alat', [['id_alat' => '', 'jumlah' => 1]]);

        return view('peminjaman.ajukan', compact('alats', 'barisAlat'));
    }

    public function simpanPengajuan(Request $request)
    {
        $data = $this->validasiForm($request);
        $jumlahAlat = $this->jumlahAlat($data['alat']);

        DB::transaction(function () use ($data, $jumlahAlat) {
            $this->pastikanStokCukup($jumlahAlat);

            $peminjaman = Peminjaman::create([
                'id_peminjam' => auth()->id(),
                'id_petugas' => null,
                'tgl_pinjam' => $data['tgl_pinjam'],
                'tgl_rencana_kembali' => $data['tgl_rencana_kembali'],
                'status' => 'menunggu',
            ]);

            $this->simpanDetail($peminjaman, $jumlahAlat);
            LogAktivitas::catat('mengajukan peminjaman alat');
        });

        return redirect()->route('peminjaman.saya')->with('sukses', 'Pengajuan peminjaman berhasil dikirim.');
    }

    public function saya()
    {
        $peminjamans = Peminjaman::with(['detail.alat', 'pengembalian'])
            ->where('id_peminjam', auth()->id())
            ->latest('created_at')
            ->paginate(10);

        return view('peminjaman.saya', compact('peminjamans'));
    }

    public function persetujuan()
    {
        $peminjamans = Peminjaman::with(['peminjam', 'detail.alat'])
            ->where('status', 'menunggu')
            ->orderBy('tgl_pinjam')
            ->paginate(10);

        return view('peminjaman.persetujuan', compact('peminjamans'));
    }

    public function setujui(Peminjaman $peminjaman)
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                $peminjaman = Peminjaman::whereKey($peminjaman->id_peminjaman)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($peminjaman->status !== 'menunggu') {
                    throw ValidationException::withMessages([
                        'status' => 'Pengajuan ini sudah diproses.',
                    ]);
                }

                $jumlahAlat = $this->jumlahAlat(
                    $peminjaman->detail()->get(['id_alat', 'jumlah'])->toArray()
                );
                $alats = $this->kunciAlat(array_keys($jumlahAlat));

                foreach ($jumlahAlat as $idAlat => $jumlah) {
                    $alat = $alats->get($idAlat);
                    if (! $alat || $alat->stok < $jumlah) {
                        throw ValidationException::withMessages([
                            'stok' => 'Stok alat tidak cukup untuk menyetujui pengajuan ini.',
                        ]);
                    }
                }

                foreach ($jumlahAlat as $idAlat => $jumlah) {
                    $alats->get($idAlat)->decrement('stok', $jumlah);
                }

                $peminjaman->update([
                    'status' => 'disetujui',
                    'id_petugas' => auth()->id(),
                ]);
                LogAktivitas::catat('menyetujui pengajuan peminjaman');
            });
        } catch (ValidationException $exception) {
            return back()->with('gagal', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('peminjaman.persetujuan')->with('sukses', 'Peminjaman berhasil disetujui.');
    }

    public function tolak(Peminjaman $peminjaman)
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                $peminjaman = Peminjaman::whereKey($peminjaman->id_peminjaman)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($peminjaman->status !== 'menunggu') {
                    throw ValidationException::withMessages([
                        'status' => 'Pengajuan ini sudah diproses.',
                    ]);
                }

                $peminjaman->update([
                    'status' => 'ditolak',
                    'id_petugas' => auth()->id(),
                ]);
                LogAktivitas::catat('menolak pengajuan peminjaman');
            });
        } catch (ValidationException $exception) {
            return back()->with('gagal', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('peminjaman.persetujuan')->with('sukses', 'Pengajuan peminjaman berhasil ditolak.');
    }

    public function index()
    {
        $peminjamans = Peminjaman::with(['peminjam', 'petugas', 'detail.alat', 'pengembalian'])
            ->latest('created_at')
            ->paginate(10);

        return view('admin.peminjaman.index', compact('peminjamans'));
    }

    public function create()
    {
        $peminjam = User::where('role', 'peminjam')->orderBy('nama_lengkap')->get();
        $alats = Alat::orderBy('nama_alat')->get();
        $barisAlat = old('alat', [['id_alat' => '', 'jumlah' => 1]]);

        return view('admin.peminjaman.create', compact('peminjam', 'alats', 'barisAlat'));
    }

    public function store(Request $request)
    {
        $data = $this->validasiAdmin($request, false);
        $jumlahAlat = $this->jumlahAlat($data['alat']);

        DB::transaction(function () use ($data, $jumlahAlat) {
            $this->pastikanStokCukup($jumlahAlat);

            $peminjaman = Peminjaman::create([
                'id_peminjam' => $data['id_peminjam'],
                'id_petugas' => null,
                'tgl_pinjam' => $data['tgl_pinjam'],
                'tgl_rencana_kembali' => $data['tgl_rencana_kembali'],
                'status' => 'menunggu',
            ]);
            $this->simpanDetail($peminjaman, $jumlahAlat);
            LogAktivitas::catat('menambahkan data peminjaman');
        });

        return redirect()->route('admin.peminjaman.index')->with('sukses', 'Data peminjaman berhasil ditambahkan.');
    }

    public function show(Peminjaman $peminjaman)
    {
        $peminjaman->load(['peminjam', 'petugas', 'detail.alat', 'pengembalian']);

        return view('admin.peminjaman.show', compact('peminjaman'));
    }

    public function edit(Peminjaman $peminjaman)
    {
        $peminjaman->load('detail');
        $peminjam = User::where('role', 'peminjam')->orderBy('nama_lengkap')->get();
        $alats = Alat::orderBy('nama_alat')->get();
        $statusOptions = $this->statusYangDiizinkan($peminjaman->status);
        $barisAlat = old('alat', $peminjaman->detail
            ->map(fn (DetailPeminjaman $detail) => [
                'id_alat' => $detail->id_alat,
                'jumlah' => $detail->jumlah,
            ])
            ->all());

        return view('admin.peminjaman.edit', compact('peminjaman', 'peminjam', 'alats', 'barisAlat', 'statusOptions'));
    }

    public function update(Request $request, Peminjaman $peminjaman)
    {
        $data = $this->validasiAdmin($request, true, $peminjaman);
        $jumlahBaru = $this->jumlahAlat($data['alat']);

        try {
            DB::transaction(function () use ($data, $jumlahBaru, $peminjaman) {
                $peminjaman = Peminjaman::whereKey($peminjaman->id_peminjaman)
                    ->lockForUpdate()
                    ->firstOrFail();
                $detailLama = $peminjaman->detail()->get(['id_alat', 'jumlah']);
                $jumlahLama = $this->jumlahAlat($detailLama->toArray());
                $statusLama = $peminjaman->status;
                $statusBaru = $data['status'];

                if (! array_key_exists($statusBaru, $this->statusYangDiizinkan($statusLama))) {
                    throw ValidationException::withMessages([
                        'status' => 'Perubahan status tersebut tidak sesuai alur peminjaman.',
                    ]);
                }

                if ($statusLama === 'disetujui' && $statusBaru === 'dikembalikan' && $jumlahLama !== $jumlahBaru) {
                    throw ValidationException::withMessages([
                        'status' => 'Simpan perubahan daftar alat terlebih dahulu sebelum mengubah status menjadi dikembalikan.',
                    ]);
                }

                $this->sesuaikanStok($jumlahLama, $jumlahBaru, $statusLama, $statusBaru);

                $peminjaman->update([
                    'id_peminjam' => $data['id_peminjam'],
                    'tgl_pinjam' => $data['tgl_pinjam'],
                    'tgl_rencana_kembali' => $data['tgl_rencana_kembali'],
                    'status' => $statusBaru,
                    'id_petugas' => in_array($statusBaru, ['disetujui', 'ditolak'], true)
                        ? (auth()->id() ?? $peminjaman->id_petugas)
                        : $peminjaman->id_petugas,
                ]);

                $peminjaman->detail()->delete();
                $this->simpanDetail($peminjaman, $jumlahBaru);
                $this->sesuaikanPengembalian($peminjaman, $statusLama, $statusBaru);

                LogAktivitas::catat('mengubah data peminjaman');
            });
        } catch (ValidationException $exception) {
            return back()->withInput()->with('gagal', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('admin.peminjaman.index')->with('sukses', 'Data peminjaman berhasil diubah.');
    }

    public function destroy(Peminjaman $peminjaman)
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                $peminjaman = Peminjaman::whereKey($peminjaman->id_peminjaman)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($peminjaman->status === 'disetujui') {
                    $jumlahAlat = $this->jumlahAlat(
                        $peminjaman->detail()->get(['id_alat', 'jumlah'])->toArray()
                    );
                    $alats = $this->kunciAlat(array_keys($jumlahAlat));

                    foreach ($jumlahAlat as $idAlat => $jumlah) {
                        $alats->get($idAlat)?->increment('stok', $jumlah);
                    }
                }

                LogAktivitas::catat('menghapus data peminjaman');
                $peminjaman->delete();
            });
        } catch (ValidationException $exception) {
            return back()->with('gagal', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('admin.peminjaman.index')->with('sukses', 'Data peminjaman berhasil dihapus.');
    }

    private function validasiForm(Request $request): array
    {
        return $request->validate([
            'tgl_pinjam' => 'required|date',
            'tgl_rencana_kembali' => 'required|date|after_or_equal:tgl_pinjam',
            'alat' => 'required|array|min:1',
            'alat.*.id_alat' => 'required|integer|distinct|exists:alat,id_alat',
            'alat.*.jumlah' => 'required|integer|min:1',
        ], [
            'tgl_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tgl_pinjam.date' => 'Tanggal pinjam tidak valid.',
            'tgl_rencana_kembali.required' => 'Tanggal rencana kembali wajib diisi.',
            'tgl_rencana_kembali.date' => 'Tanggal rencana kembali tidak valid.',
            'tgl_rencana_kembali.after_or_equal' => 'Tanggal kembali harus sama atau sesudah tanggal pinjam.',
            'alat.required' => 'Tambahkan minimal satu alat.',
            'alat.min' => 'Tambahkan minimal satu alat.',
            'alat.*.id_alat.required' => 'Alat wajib dipilih.',
            'alat.*.id_alat.distinct' => 'Alat yang sama tidak boleh dipilih lebih dari satu kali.',
            'alat.*.id_alat.exists' => 'Alat yang dipilih tidak ditemukan.',
            'alat.*.jumlah.required' => 'Jumlah alat wajib diisi.',
            'alat.*.jumlah.integer' => 'Jumlah alat harus berupa angka bulat.',
            'alat.*.jumlah.min' => 'Jumlah alat minimal 1.',
        ]);
    }

    private function validasiAdmin(Request $request, bool $ubah, ?Peminjaman $peminjaman = null): array
    {
        $aturanStatus = $ubah && $peminjaman
            ? ['required', Rule::in(array_keys($this->statusYangDiizinkan($peminjaman->status)))]
            : ['prohibited'];

        $data = $request->validate([
            'id_peminjam' => [
                'required',
                'integer',
                Rule::exists('users', 'id_user')->where('role', 'peminjam'),
            ],
            'tgl_pinjam' => 'required|date',
            'tgl_rencana_kembali' => 'required|date|after_or_equal:tgl_pinjam',
            'status' => $aturanStatus,
            'alat' => 'required|array|min:1',
            'alat.*.id_alat' => 'required|integer|distinct|exists:alat,id_alat',
            'alat.*.jumlah' => 'required|integer|min:1',
        ], [
            'id_peminjam.required' => 'Peminjam wajib dipilih.',
            'id_peminjam.exists' => 'Pengguna yang dipilih bukan peminjam.',
            'tgl_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tgl_pinjam.date' => 'Tanggal pinjam tidak valid.',
            'tgl_rencana_kembali.required' => 'Tanggal rencana kembali wajib diisi.',
            'tgl_rencana_kembali.date' => 'Tanggal rencana kembali tidak valid.',
            'tgl_rencana_kembali.after_or_equal' => 'Tanggal kembali harus sama atau sesudah tanggal pinjam.',
            'status.in' => 'Status peminjaman tidak valid.',
            'status.prohibited' => 'Status peminjaman baru harus menunggu persetujuan.',
            'alat.required' => 'Tambahkan minimal satu alat.',
            'alat.min' => 'Tambahkan minimal satu alat.',
            'alat.*.id_alat.required' => 'Alat wajib dipilih.',
            'alat.*.id_alat.distinct' => 'Alat yang sama tidak boleh dipilih lebih dari satu kali.',
            'alat.*.id_alat.exists' => 'Alat yang dipilih tidak ditemukan.',
            'alat.*.jumlah.required' => 'Jumlah alat wajib diisi.',
            'alat.*.jumlah.integer' => 'Jumlah alat harus berupa angka bulat.',
            'alat.*.jumlah.min' => 'Jumlah alat minimal 1.',
        ]);

        if (! $ubah) {
            $data['status'] = 'menunggu';
        }

        return $data;
    }

    private function statusYangDiizinkan(string $status): array
    {
        return match ($status) {
            'menunggu' => [
                'menunggu' => 'Menunggu',
                'disetujui' => 'Disetujui / sedang dipinjam',
                'ditolak' => 'Ditolak',
            ],
            'disetujui' => [
                'disetujui' => 'Disetujui / sedang dipinjam',
                'dikembalikan' => 'Dikembalikan',
            ],
            'ditolak' => ['ditolak' => 'Ditolak'],
            'dikembalikan' => ['dikembalikan' => 'Dikembalikan'],
            default => [$status => ucfirst($status)],
        };
    }

    private function jumlahAlat(array $baris): array
    {
        if ($baris === []) {
            throw ValidationException::withMessages([
                'alat' => 'Peminjaman harus memiliki minimal satu alat.',
            ]);
        }

        $jumlah = [];

        foreach ($baris as $barisAlat) {
            $idAlat = (int) (is_array($barisAlat) ? $barisAlat['id_alat'] : $barisAlat->id_alat);
            $jumlah[$idAlat] = ($jumlah[$idAlat] ?? 0)
                + (int) (is_array($barisAlat) ? $barisAlat['jumlah'] : $barisAlat->jumlah);
        }

        return $jumlah;
    }

    private function pastikanStokCukup(array $jumlahAlat): void
    {
        $alats = $this->kunciAlat(array_keys($jumlahAlat));

        foreach ($jumlahAlat as $idAlat => $jumlah) {
            if (! $alats->has($idAlat) || $alats->get($idAlat)->stok < $jumlah) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok alat tidak cukup untuk jumlah yang diajukan.',
                ]);
            }
        }
    }

    private function kunciAlat(array $idAlat)
    {
        return Alat::whereIn('id_alat', $idAlat)
            ->orderBy('id_alat')
            ->lockForUpdate()
            ->get()
            ->keyBy('id_alat');
    }

    private function simpanDetail(Peminjaman $peminjaman, array $jumlahAlat): void
    {
        foreach ($jumlahAlat as $idAlat => $jumlah) {
            $peminjaman->detail()->create([
                'id_alat' => $idAlat,
                'jumlah' => $jumlah,
            ]);
        }
    }

    private function sesuaikanStok(
        array $jumlahLama,
        array $jumlahBaru,
        string $statusLama,
        string $statusBaru
    ): void {
        $perubahanStok = [];
        $sedangDipinjamLama = $statusLama === 'disetujui';
        $sedangDipinjamBaru = $statusBaru === 'disetujui';

        // Stok hanya berubah saat status menandakan alat sedang dipinjam.
        if ($sedangDipinjamLama) {
            foreach ($jumlahLama as $idAlat => $jumlah) {
                $perubahanStok[$idAlat] = ($perubahanStok[$idAlat] ?? 0) - $jumlah;
            }
        }

        if ($sedangDipinjamBaru) {
            foreach ($jumlahBaru as $idAlat => $jumlah) {
                $perubahanStok[$idAlat] = ($perubahanStok[$idAlat] ?? 0) + $jumlah;
            }
        }

        if ($perubahanStok === []) {
            return;
        }

        $alats = $this->kunciAlat(array_keys($perubahanStok));
        foreach ($perubahanStok as $idAlat => $jumlah) {
            $alat = $alats->get($idAlat);
            if (! $alat || $alat->stok - $jumlah < 0) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok alat tidak cukup untuk perubahan peminjaman.',
                ]);
            }
        }

        foreach ($perubahanStok as $idAlat => $jumlah) {
            $alats->get($idAlat)->increment('stok', -$jumlah);
        }
    }

    private function sesuaikanPengembalian(Peminjaman $peminjaman, string $statusLama, string $statusBaru): void
    {
        $pengembalian = $peminjaman->pengembalian()->lockForUpdate()->first();

        if ($statusBaru === 'dikembalikan') {
            $tglKembali = $pengembalian?->tgl_kembali ?? Carbon::today()->toDateString();
            $hariTerlambat = Pengembalian::hitungHariTerlambat($peminjaman->tgl_rencana_kembali, $tglKembali);
            $peminjaman->pengembalian()->updateOrCreate(
                ['id_peminjaman' => $peminjaman->id_peminjaman],
                [
                    'tgl_kembali' => $tglKembali,
                    'hari_terlambat' => $hariTerlambat,
                    'denda' => Pengembalian::hitungDenda($hariTerlambat),
                ]
            );

            return;
        }

        if ($statusLama === 'dikembalikan') {
            $pengembalian?->delete();
        }
    }
}
