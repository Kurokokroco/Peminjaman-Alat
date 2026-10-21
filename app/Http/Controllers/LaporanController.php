<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'tanggal_dari' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_dari',
            'status' => 'nullable|in:menunggu,disetujui,ditolak,dikembalikan',
        ], [
            'tanggal_dari.date' => 'Tanggal mulai tidak valid.',
            'tanggal_sampai.date' => 'Tanggal akhir tidak valid.',
            'tanggal_sampai.after_or_equal' => 'Tanggal akhir harus sama atau sesudah tanggal mulai.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $query = Peminjaman::with(['peminjam', 'detail.alat', 'pengembalian'])
            ->when($request->filled('tanggal_dari'), function ($query) use ($data) {
                $query->whereDate('tgl_pinjam', '>=', $data['tanggal_dari']);
            })
            ->when($request->filled('tanggal_sampai'), function ($query) use ($data) {
                $query->whereDate('tgl_pinjam', '<=', $data['tanggal_sampai']);
            })
            ->when($request->filled('status'), function ($query) use ($data) {
                $query->where('status', $data['status']);
            });

        $peminjamans = (clone $query)
            ->latest('tgl_pinjam')
            ->paginate(10)
            ->withQueryString();
        $totalDenda = Pengembalian::whereHas('peminjaman', function ($query) use ($data) {
            $query
                ->when(! empty($data['tanggal_dari']), function ($query) use ($data) {
                    $query->whereDate('tgl_pinjam', '>=', $data['tanggal_dari']);
                })
                ->when(! empty($data['tanggal_sampai']), function ($query) use ($data) {
                    $query->whereDate('tgl_pinjam', '<=', $data['tanggal_sampai']);
                })
                ->when(! empty($data['status']), function ($query) use ($data) {
                    $query->where('status', $data['status']);
                });
        })->sum('denda');
        $pencetak = auth()->user();

        return view('laporan.index', compact('peminjamans', 'totalDenda', 'pencetak'));
    }

    public function cetak()
    {
        LogAktivitas::catat('mencetak laporan peminjaman');

        return response()->json(['sukses' => true]);
    }
}
