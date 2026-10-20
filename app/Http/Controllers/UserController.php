<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'cari' => 'nullable|string|max:100',
        ], [
            'cari.string' => 'Pencarian harus berupa teks.',
            'cari.max' => 'Pencarian maksimal 100 karakter.',
        ]);

        $users = User::query()
            ->when($request->filled('cari'), function ($query) use ($request) {
                $cari = $request->cari;
                $query->where(function ($query) use ($cari) {
                    $query->where('nama_lengkap', 'like', "%{$cari}%")
                        ->orWhere('username', 'like', "%{$cari}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        return view('user.index', compact('users'));
    }

    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,petugas,peminjam',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.string' => 'Nama lengkap harus berupa teks.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username tersebut sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',
        ]);

        DB::transaction(function () use ($data) {
            User::create($data);
            LogAktivitas::catat('menambahkan pengguna');
        });

        return redirect()->route('user.index')->with('sukses', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id_user, 'id_user'),
            ],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,petugas,peminjam',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.string' => 'Nama lengkap harus berupa teks.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username tersebut sudah digunakan.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::transaction(function () use ($data, $user) {
            $user->update($data);
            LogAktivitas::catat('mengubah data pengguna');
        });

        return redirect()->route('user.index')->with('sukses', 'Pengguna berhasil diubah.');
    }

    public function destroy(User $user)
    {
        if ($user->id_user === auth()->id()) {
            return back()->with('gagal', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        try {
            DB::transaction(function () use ($user) {
                LogAktivitas::catat('menghapus pengguna');
                $user->delete();
            });
        } catch (QueryException $exception) {
            if (! in_array($exception->errorInfo[0] ?? '', ['23000', '23503'], true)) {
                throw $exception;
            }

            return back()->with('gagal', 'Pengguna tidak dapat dihapus karena masih memiliki riwayat transaksi atau aktivitas.');
        }

        return redirect()->route('user.index')->with('sukses', 'Pengguna berhasil dihapus.');
    }
}
