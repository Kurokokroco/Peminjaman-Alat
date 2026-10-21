<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'nama_lengkap' => 'administrator',
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
        ]);
        $petugas = User::create([
            'nama_lengkap' => 'Petugas Gudang',
            'username' => 'petugas',
            'password' => 'petugas123',
            'role' => 'petugas',
        ]);
        $peminjam = User::create([
            'nama_lengkap' => 'Budi',
            'username' => 'budi',
            'password' => 'budi123',
            'role' => 'peminjam',
        ]);

        $kategori = collect(['Elektronik', 'Perkakas', 'Peralatan Ukur'])
            ->map(fn (string $nama) => Kategori::create(['nama_kategori' => $nama]));

        $dataAlat = [
            ['nama_alat' => 'Proyektor', 'id_kategori' => $kategori[0]->id_kategori],
            ['nama_alat' => 'Laptop', 'id_kategori' => $kategori[0]->id_kategori],
            ['nama_alat' => 'Speaker Aktif', 'id_kategori' => $kategori[0]->id_kategori],
            ['nama_alat' => 'Bor Listrik', 'id_kategori' => $kategori[1]->id_kategori],
            ['nama_alat' => 'Obeng Set', 'id_kategori' => $kategori[1]->id_kategori],
            ['nama_alat' => 'Tang Kombinasi', 'id_kategori' => $kategori[1]->id_kategori],
            ['nama_alat' => 'Multimeter', 'id_kategori' => $kategori[2]->id_kategori],
            ['nama_alat' => 'Jangka Sorong', 'id_kategori' => $kategori[2]->id_kategori],
        ];

        $alats = collect($dataAlat)->map(fn (array $data) => Alat::create([
            ...$data,
            'stok' => 10,
            'kondisi' => 'baik',
        ]));

        $peminjamanMenunggu = Peminjaman::create([
            'id_peminjam' => $peminjam->id_user,
            'id_petugas' => null,
            'tgl_pinjam' => Carbon::today()->toDateString(),
            'tgl_rencana_kembali' => Carbon::today()->addDays(3)->toDateString(),
            'status' => 'menunggu',
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $peminjamanMenunggu->id_peminjaman,
            'id_alat' => $alats[0]->id_alat,
            'jumlah' => 1,
        ]);

        $peminjamanDisetujui = Peminjaman::create([
            'id_peminjam' => $peminjam->id_user,
            'id_petugas' => $petugas->id_user,
            'tgl_pinjam' => Carbon::today()->subDays(5)->toDateString(),
            'tgl_rencana_kembali' => Carbon::today()->subDays(2)->toDateString(),
            'status' => 'disetujui',
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $peminjamanDisetujui->id_peminjaman,
            'id_alat' => $alats[1]->id_alat,
            'jumlah' => 1,
        ]);
        $alats[1]->decrement('stok', 1);
    }
}
