<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::withCount('alat')
            ->orderBy('nama_kategori')
            ->paginate(10);

        return view('kategori.index', compact('kategoris'));
    }

    public function create()
    {
        return view('kategori.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.string' => 'Nama kategori harus berupa teks.',
            'nama_kategori.max' => 'Nama kategori maksimal 255 karakter.',
        ]);

        DB::transaction(function () use ($data) {
            Kategori::create($data);
            LogAktivitas::catat('menambahkan kategori');
        });

        return redirect()->route('kategori.index')->with('sukses', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Kategori $kategori)
    {
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori)
    {
        $data = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.string' => 'Nama kategori harus berupa teks.',
            'nama_kategori.max' => 'Nama kategori maksimal 255 karakter.',
        ]);

        DB::transaction(function () use ($data, $kategori) {
            $kategori->update($data);
            LogAktivitas::catat('mengubah kategori');
        });

        return redirect()->route('kategori.index')->with('sukses', 'Kategori berhasil diubah.');
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->alat()->exists()) {
            return back()->with('gagal', 'Kategori tidak dapat dihapus karena masih digunakan oleh alat.');
        }

        try {
            DB::transaction(function () use ($kategori) {
                LogAktivitas::catat('menghapus kategori');
                $kategori->delete();
            });
        } catch (QueryException $exception) {
            if (! in_array($exception->errorInfo[0] ?? '', ['23000', '23503'], true)) {
                throw $exception;
            }

            return back()->with('gagal', 'Kategori tidak dapat dihapus karena masih digunakan oleh alat.');
        }

        return redirect()->route('kategori.index')->with('sukses', 'Kategori berhasil dihapus.');
    }
}
