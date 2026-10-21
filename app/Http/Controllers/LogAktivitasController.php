<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'id_user' => 'nullable|integer|exists:users,id_user',
            'tanggal_dari' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_dari',
        ], [
            'id_user.integer' => 'Pengguna yang dipilih tidak valid.',
            'id_user.exists' => 'Pengguna yang dipilih tidak ditemukan.',
            'tanggal_dari.date' => 'Tanggal mulai tidak valid.',
            'tanggal_sampai.date' => 'Tanggal akhir tidak valid.',
            'tanggal_sampai.after_or_equal' => 'Tanggal akhir harus sama atau sesudah tanggal mulai.',
        ]);

        $logs = LogAktivitas::with('user')
            ->when($request->filled('id_user'), function ($query) use ($data) {
                $query->where('id_user', $data['id_user']);
            })
            ->when($request->filled('tanggal_dari'), function ($query) use ($data) {
                $query->whereDate('waktu', '>=', $data['tanggal_dari']);
            })
            ->when($request->filled('tanggal_sampai'), function ($query) use ($data) {
                $query->whereDate('waktu', '<=', $data['tanggal_sampai']);
            })
            ->orderByDesc('waktu')
            ->paginate(20)
            ->withQueryString();
        $pengguna = User::orderBy('nama_lengkap')->get(['id_user', 'nama_lengkap', 'username']);

        return view('log_aktivitas.index', compact('logs', 'pengguna'));
    }
}
