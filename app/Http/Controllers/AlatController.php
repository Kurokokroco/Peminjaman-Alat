<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlatController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'cari' => 'nullable|string|max:100',
            'id_kategori' => 'nullable|integer|exists:kategori,id_kategori',
        ], [
            'cari.string' => 'Pencarian harus berupa teks.',
            'cari.max' => 'Pencarian maksimal 100 karakter.',
            'id_kategori.integer' => 'Kategori yang dipilih tidak valid.',
            'id_kategori.exists' => 'Kategori yang dipilih tidak ditemukan.',
        ]);

        $alats = Alat::with('kategori')
            ->when($request->filled('cari'), function ($query) use ($request) {
                $query->where('nama_alat', 'like', '%'.$request->cari.'%');
            })
            ->when($request->filled('id_kategori'), function ($query) use ($request) {
                $query->where('id_kategori', $request->id_kategori);
            })
            ->orderBy('nama_alat')
            ->paginate(10)
            ->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('alat.index', compact('alats', 'kategoris'));
    }

    public function daftar(Request $request)
    {
        $request->validate([
            'cari' => 'nullable|string|max:100',
        ], [
            'cari.string' => 'Pencarian harus berupa teks.',
            'cari.max' => 'Pencarian maksimal 100 karakter.',
        ]);

        $alats = Alat::with('kategori')
            ->when($request->filled('cari'), function ($query) use ($request) {
                $query->where('nama_alat', 'like', '%'.$request->cari.'%');
            })
            ->orderBy('nama_alat')
            ->paginate(10)
            ->withQueryString();

        return view('alat.daftar', compact('alats'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('alat.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_kategori' => 'required|integer|exists:kategori,id_kategori',
            'nama_alat' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'kondisi' => 'required|in:baik,rusak ringan,rusak berat',
        ], [
            'id_kategori.required' => 'Kategori wajib dipilih.',
            'id_kategori.integer' => 'Kategori yang dipilih tidak valid.',
            'id_kategori.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'nama_alat.required' => 'Nama alat wajib diisi.',
            'nama_alat.string' => 'Nama alat harus berupa teks.',
            'nama_alat.max' => 'Nama alat maksimal 255 karakter.',
            'stok.required' => 'Stok wajib diisi.',
            'stok.integer' => 'Stok harus berupa angka bulat.',
            'stok.min' => 'Stok tidak boleh kurang dari 0.',
            'kondisi.required' => 'Kondisi wajib dipilih.',
            'kondisi.in' => 'Kondisi yang dipilih tidak valid.',
        ]);

        DB::transaction(function () use ($data) {
            Alat::create($data);
            LogAktivitas::catat('menambahkan alat');
        });

        return redirect()->route('alat.index')->with('sukses', 'Alat berhasil ditambahkan.');
    }

    public function edit(Alat $alat)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('alat.edit', compact('alat', 'kategoris'));
    }

    public function update(Request $request, Alat $alat)
    {
        $data = $request->validate([
            'id_kategori' => 'required|integer|exists:kategori,id_kategori',
            'nama_alat' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'kondisi' => 'required|in:baik,rusak ringan,rusak berat',
        ], [
            'id_kategori.required' => 'Kategori wajib dipilih.',
            'id_kategori.integer' => 'Kategori yang dipilih tidak valid.',
            'id_kategori.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'nama_alat.required' => 'Nama alat wajib diisi.',
            'nama_alat.string' => 'Nama alat harus berupa teks.',
            'nama_alat.max' => 'Nama alat maksimal 255 karakter.',
            'stok.required' => 'Stok wajib diisi.',
            'stok.integer' => 'Stok harus berupa angka bulat.',
            'stok.min' => 'Stok tidak boleh kurang dari 0.',
            'kondisi.required' => 'Kondisi wajib dipilih.',
            'kondisi.in' => 'Kondisi yang dipilih tidak valid.',
        ]);

        DB::transaction(function () use ($data, $alat) {
            $alat->update($data);
            LogAktivitas::catat('mengubah alat');
        });

        return redirect()->route('alat.index')->with('sukses', 'Alat berhasil diubah.');
    }

    public function destroy(Alat $alat)
    {
        try {
            DB::transaction(function () use ($alat) {
                LogAktivitas::catat('menghapus alat');
                $alat->delete();
            });
        } catch (QueryException $exception) {
            if (! in_array($exception->errorInfo[0] ?? '', ['23000', '23503'], true)) {
                throw $exception;
            }

            return back()->with('gagal', 'Alat tidak dapat dihapus karena sudah tercatat dalam riwayat peminjaman.');
        }

        return redirect()->route('alat.index')->with('sukses', 'Alat berhasil dihapus.');
    }
}
