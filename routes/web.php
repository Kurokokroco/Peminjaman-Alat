<?php

use App\Http\Controllers\AlatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    redirect()->route('dashboard');
});
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'frmLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'prosesLogin'])->name('login.proses');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/alat/daftar', [AlatController::class, 'daftar'])->name('alat.daftar');

    Route::middleware('role:peminjam')->group(function () {
        Route::get('/peminjaman/ajukan', [PeminjamanController::class, 'ajukan'])->name('peminjaman.ajukan');
        Route::post('/peminjaman', [PeminjamanController::class, 'simpanPengajuan'])->name('peminjaman.store');
        Route::get('/peminjaman-saya', [PeminjamanController::class, 'saya'])->name('peminjaman.saya');
        Route::post('/peminjaman/{peminjaman}/kembalikan', [PengembalianController::class, 'kembalikan'])->name('pengembalian.kembalikan');
    });

    Route::middleware('role:petugas,admin')->prefix('petugas')->group(function () {
        Route::get('/persetujuan-peminjaman', [PeminjamanController::class, 'persetujuan'])->name('peminjaman.persetujuan');
        Route::post('/peminjaman/{peminjaman}/setujui', [PeminjamanController::class, 'setujui'])->name('peminjaman.setujui');
        Route::post('/peminjaman/{peminjaman}/tolak', [PeminjamanController::class, 'tolak'])->name('peminjaman.tolak');
        Route::get('/pantau-pengembalian', [PengembalianController::class, 'pantau'])->name('pengembalian.pantau');
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::resource('user', UserController::class)->except('show');
        Route::resource('kategori', KategoriController::class)->except('show');
        Route::resource('alat', AlatController::class)->except('show');
        Route::resource('peminjaman', PeminjamanController::class)->names([
            'index' => 'admin.peminjaman.index',
            'create' => 'admin.peminjaman.create',
            'store' => 'admin.peminjaman.store',
            'show' => 'admin.peminjaman.show',
            'edit' => 'admin.peminjaman.edit',
            'update' => 'admin.peminjaman.update',
            'destroy' => 'admin.peminjaman.destroy',
        ]);
        Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('admin.pengembalian.index');
        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class, 'show'])->name('admin.pengembalian.show');
        Route::get('/pengembalian/{pengembalian}/edit', [PengembalianController::class, 'edit'])->name('admin.pengembalian.edit');
        Route::put('/pengembalian/{pengembalian}', [PengembalianController::class, 'update'])->name('admin.pengembalian.update');
        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy'])->name('admin.pengembalian.destroy');
    });
});
