<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $pengguna = auth()->user();
        $jumlahAlat = Alat::count();

        if ($pengguna->role === 'admin') {
            $ringkasan = [
                ['label' => 'Jumlah Alat', 'jumlah' => $jumlahAlat, 'warna' => 'primary'],
                ['label' => 'Peminjaman Menunggu', 'jumlah' => Peminjaman::where('status', 'menunggu')->count(), 'warna' => 'warning'],
                ['label' => 'Jumlah Pengguna', 'jumlah' => User::count(), 'warna' => 'success'],
                ['label' => 'Jumlah Kategori', 'jumlah' => Kategori::count(), 'warna' => 'info'],
            ];
        } elseif ($pengguna->role === 'petugas') {
            $ringkasan = [
                ['label' => 'Jumlah Alat', 'jumlah' => $jumlahAlat, 'warna' => 'primary'],
                ['label' => 'Peminjaman Menunggu', 'jumlah' => Peminjaman::where('status', 'menunggu')->count(), 'warna' => 'warning'],
                ['label' => 'Disetujui', 'jumlah' => Peminjaman::where('status', 'disetujui')->count(), 'warna' => 'success'],
                ['label' => 'Sudah Dikembalikan', 'jumlah' => Peminjaman::where('status', 'dikembalikan')->count(), 'warna' => 'info'],
            ];
        } else {
            $ringkasan = [
                ['label' => 'Jumlah Alat', 'jumlah' => $jumlahAlat, 'warna' => 'primary'],
                ['label' => 'Peminjaman Menunggu', 'jumlah' => Peminjaman::where('id_peminjam', $pengguna->id_user)->where('status', 'menunggu')->count(), 'warna' => 'warning'],
                ['label' => 'Sedang Dipinjam', 'jumlah' => Peminjaman::where('id_peminjam', $pengguna->id_user)->where('status', 'disetujui')->count(), 'warna' => 'success'],
                ['label' => 'Sudah Dikembalikan', 'jumlah' => Peminjaman::where('id_peminjam', $pengguna->id_user)->where('status', 'dikembalikan')->count(), 'warna' => 'info'],
            ];
        }

        return view('dashboard', compact('pengguna', 'ringkasan'));
    }
}
