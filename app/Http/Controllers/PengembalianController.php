<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengembalianController extends Controller
{
    public function kembalikan(Peminjaman $peminjaman)
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                $peminjaman = Peminjaman::whereKey($peminjaman->id_peminjaman)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    (int) $peminjaman->id_peminjam !== (int) auth()->id()
                    || $peminjaman->status !== 'disetujui'
                    || $peminjaman->pengembalian()->exists()
                ) {
                    throw ValidationException::withMessages([
                        'peminjaman' => 'Peminjaman ini tidak dapat dikembalikan.',
                    ]);
                }

                $detail = $peminjaman->detail()->get(['id_alat', 'jumlah']);
                if ($detail->isEmpty()) {
                    throw ValidationException::withMessages([
                        'peminjaman' => 'Peminjaman ini belum memiliki detail alat.',
                    ]);
                }

                $idAlat = $detail->pluck('id_alat')->all();
                $alats = Alat::whereIn('id_alat', $idAlat)
                    ->orderBy('id_alat')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_alat');

                foreach ($detail as $baris) {
                    if (! $alats->has($baris->id_alat)) {
                        throw ValidationException::withMessages([
                            'peminjaman' => 'Data alat pada peminjaman ini tidak ditemukan.',
                        ]);
                    }
                }

                $tglKembali = Carbon::today()->toDateString();
                $hariTerlambat = Pengembalian::hitungHariTerlambat(
                    $peminjaman->tgl_rencana_kembali,
                    $tglKembali
                );

                foreach ($detail as $baris) {
                    $alats->get($baris->id_alat)->increment('stok', $baris->jumlah);
                }

                $peminjaman->pengembalian()->create([
                    'tgl_kembali' => $tglKembali,
                    'hari_terlambat' => $hariTerlambat,
                    'denda' => Pengembalian::hitungDenda($peminjaman->tgl_rencana_kembali, $tglKembali),
                ]);
                $peminjaman->update(['status' => 'dikembalikan']);
                LogAktivitas::catat('mengembalikan alat');
            });
        } catch (ValidationException $exception) {
            return back()->with('gagal', collect($exception->errors())->flatten()->first());
        }

        $pengembalian = $peminjaman->fresh('pengembalian')->pengembalian;

        return redirect()->route('peminjaman.saya')->with(
            'sukses',
            'Alat berhasil dikembalikan. Denda: Rp '.number_format((float) $pengembalian->denda, 0, ',', '.').'.'
        );
    }

    public function pantau()
    {
        $sedangDipinjam = Peminjaman::with(['peminjam', 'detail.alat'])
            ->where('status', 'disetujui')
            ->orderBy('tgl_rencana_kembali')
            ->paginate(10, ['*'], 'aktif');

        foreach ($sedangDipinjam as $peminjaman) {
            $peminjaman->terlambat_saat_ini = Pengembalian::hitungHariTerlambat(
                $peminjaman->tgl_rencana_kembali,
                Carbon::today()->toDateString()
            );
        }

        $riwayatPengembalian = Peminjaman::with(['peminjam', 'detail.alat', 'pengembalian'])
            ->where('status', 'dikembalikan')
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'riwayat');

        return view('pengembalian.pantau', compact('sedangDipinjam', 'riwayatPengembalian'));
    }

    public function index()
    {
        $pengembalians = Pengembalian::with(['peminjaman.peminjam', 'peminjaman.detail.alat'])
            ->latest('tgl_kembali')
            ->paginate(10);

        return view('admin.pengembalian.index', compact('pengembalians'));
    }

    public function show(Pengembalian $pengembalian)
    {
        $pengembalian->load(['peminjaman.peminjam', 'peminjaman.petugas', 'peminjaman.detail.alat']);

        return view('admin.pengembalian.show', compact('pengembalian'));
    }

    public function edit(Pengembalian $pengembalian)
    {
        $pengembalian->load('peminjaman');

        return view('admin.pengembalian.edit', compact('pengembalian'));
    }

    public function update(Request $request, Pengembalian $pengembalian)
    {
        $data = $request->validate([
            'tgl_kembali' => 'required|date|before_or_equal:today',
        ], [
            'tgl_kembali.required' => 'Tanggal kembali wajib diisi.',
            'tgl_kembali.date' => 'Tanggal kembali tidak valid.',
            'tgl_kembali.before_or_equal' => 'Tanggal kembali tidak boleh melewati hari ini.',
        ]);

        try {
            DB::transaction(function () use ($data, $pengembalian) {
                $pengembalian = Pengembalian::whereKey($pengembalian->id_pengembalian)
                    ->lockForUpdate()
                    ->firstOrFail();
                $peminjaman = Peminjaman::whereKey($pengembalian->id_peminjaman)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($peminjaman->status !== 'dikembalikan') {
                    throw ValidationException::withMessages([
                        'pengembalian' => 'Pengembalian hanya dapat diubah jika peminjaman sudah dikembalikan.',
                    ]);
                }

                $hariTerlambat = Pengembalian::hitungHariTerlambat(
                    $peminjaman->tgl_rencana_kembali,
                    $data['tgl_kembali']
                );
                $pengembalian->update([
                    'tgl_kembali' => $data['tgl_kembali'],
                    'hari_terlambat' => $hariTerlambat,
                    'denda' => Pengembalian::hitungDenda(
                        $peminjaman->tgl_rencana_kembali,
                        $data['tgl_kembali']
                    ),
                ]);
                LogAktivitas::catat('mengubah data pengembalian');
            });
        } catch (ValidationException $exception) {
            return back()->with('gagal', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('admin.pengembalian.index')->with('sukses', 'Data pengembalian berhasil diubah dan denda dihitung ulang.');
    }

    public function destroy(Pengembalian $pengembalian)
    {
        try {
            DB::transaction(function () use ($pengembalian) {
                $pengembalian = Pengembalian::whereKey($pengembalian->id_pengembalian)
                    ->lockForUpdate()
                    ->firstOrFail();
                $peminjaman = Peminjaman::whereKey($pengembalian->id_peminjaman)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($peminjaman->status !== 'dikembalikan') {
                    throw ValidationException::withMessages([
                        'pengembalian' => 'Data pengembalian ini tidak dapat dihapus.',
                    ]);
                }

                $detail = $peminjaman->detail()->get(['id_alat', 'jumlah']);
                $alats = Alat::whereIn('id_alat', $detail->pluck('id_alat'))
                    ->orderBy('id_alat')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_alat');

                foreach ($detail as $baris) {
                    $alat = $alats->get($baris->id_alat);
                    if (! $alat || $alat->stok < $baris->jumlah) {
                        throw ValidationException::withMessages([
                            'stok' => 'Pengembalian tidak dapat dihapus karena stok alat tidak mencukupi untuk mengaktifkan kembali peminjaman.',
                        ]);
                    }
                }

                foreach ($detail as $baris) {
                    $alats->get($baris->id_alat)->decrement('stok', $baris->jumlah);
                }

                $peminjaman->update(['status' => 'disetujui']);
                $pengembalian->delete();
                LogAktivitas::catat('menghapus data pengembalian');
            });
        } catch (ValidationException $exception) {
            return back()->with('gagal', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('admin.pengembalian.index')->with(
            'sukses',
            'Data pengembalian dihapus dan peminjaman diaktifkan kembali.'
        );
    }
}
