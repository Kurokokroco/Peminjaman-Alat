<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaporanDanLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_dapat_memfilter_laporan_dan_mencatat_cetak(): void
    {
        $petugas = $this->buatPengguna('petugas');
        $peminjam = $this->buatPengguna('peminjam');
        $peminjaman = $this->buatPeminjaman($peminjam, 'dikembalikan', now()->toDateString());
        Pengembalian::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'tgl_kembali' => now()->toDateString(),
            'hari_terlambat' => 2,
            'denda' => 10000,
        ]);

        $this->actingAs($petugas)
            ->get(route('laporan.index', [
                'tanggal_dari' => now()->toDateString(),
                'tanggal_sampai' => now()->toDateString(),
                'status' => 'dikembalikan',
            ]))
            ->assertOk()
            ->assertSee('Laporan Peminjaman Alat')
            ->assertSee('Proyektor')
            ->assertSee('10.000');

        $this->post(route('laporan.cetak'))
            ->assertOk()
            ->assertJson(['sukses' => true]);

        $this->assertDatabaseHas('log_aktivitas', [
            'id_user' => $petugas->id_user,
            'aktivitas' => 'mencetak laporan peminjaman',
        ]);
    }

    public function test_admin_dapat_memfilter_log_berdasarkan_user_dan_rentang_tanggal(): void
    {
        $admin = $this->buatPengguna('admin');
        $peminjam = $this->buatPengguna('peminjam');
        LogAktivitas::create([
            'id_user' => $peminjam->id_user,
            'aktivitas' => 'mengajukan alat uji',
            'waktu' => now()->subDay(),
        ]);
        LogAktivitas::create([
            'id_user' => $admin->id_user,
            'aktivitas' => 'aktivitas admin uji',
            'waktu' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('log.index', [
                'id_user' => $peminjam->id_user,
                'tanggal_dari' => now()->subDay()->toDateString(),
                'tanggal_sampai' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('mengajukan alat uji')
            ->assertDontSee('aktivitas admin uji')
            ->assertSee($peminjam->nama_lengkap);
    }

    private function buatPeminjaman(User $peminjam, string $status, string $tanggal): Peminjaman
    {
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => 'Proyektor',
            'stok' => 5,
            'kondisi' => 'baik',
        ]);
        $peminjaman = Peminjaman::create([
            'id_peminjam' => $peminjam->id_user,
            'id_petugas' => null,
            'tgl_pinjam' => $tanggal,
            'tgl_rencana_kembali' => $tanggal,
            'status' => $status,
        ]);
        $peminjaman->detail()->create([
            'id_alat' => $alat->id_alat,
            'jumlah' => 1,
        ]);

        return $peminjaman;
    }

    private function buatPengguna(string $role): User
    {
        static $nomor = 0;
        $nomor++;

        return User::create([
            'nama_lengkap' => ucfirst($role),
            'username' => $role.$nomor,
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
