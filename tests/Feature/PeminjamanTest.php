<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_peminjam_mengajukan_meminjam_dan_mengembalikan_alat(): void
    {
        config(['aplikasi.denda_per_hari' => 5000]);
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => 'Proyektor',
            'stok' => 3,
            'kondisi' => 'baik',
        ]);
        $peminjam = $this->buatPengguna('peminjam');
        $petugas = $this->buatPengguna('petugas');
        $admin = $this->buatPengguna('admin');

        $this->actingAs($peminjam)
            ->get(route('peminjaman.ajukan'))
            ->assertOk();

        $this->actingAs($peminjam)
            ->post(route('peminjaman.store'), [
                'tgl_pinjam' => '2026-10-08',
                'tgl_rencana_kembali' => '2026-10-10',
                'alat' => [
                    ['id_alat' => $alat->id_alat, 'jumlah' => 2],
                ],
            ])
            ->assertRedirect(route('peminjaman.saya'));
        $this->get(route('peminjaman.saya'))->assertOk();
        $this->actingAs($petugas)
            ->get(route('peminjaman.persetujuan'))
            ->assertOk();

        $this->assertDatabaseHas('peminjaman', [
            'id_peminjam' => $peminjam->id_user,
            'id_petugas' => null,
            'status' => 'menunggu',
        ]);
        $this->assertSame(3, $alat->fresh()->stok);
        $idPeminjaman = (int) $peminjam->peminjaman()->value('id_peminjaman');

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.create'))
            ->assertOk();
        $this->get(route('admin.peminjaman.edit', $idPeminjaman))->assertOk();

        $this->actingAs($petugas)
            ->post(route('peminjaman.setujui', $idPeminjaman))
            ->assertRedirect(route('peminjaman.persetujuan'));

        $this->assertSame(1, $alat->fresh()->stok);
        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $idPeminjaman,
            'status' => 'disetujui',
            'id_petugas' => $petugas->id_user,
        ]);
        $this->actingAs($petugas)
            ->get(route('pengembalian.pantau'))
            ->assertOk();

        $this->travelTo(now()->setDate(2026, 10, 12));
        $this->actingAs($peminjam)
            ->post(route('pengembalian.kembalikan', $idPeminjaman))
            ->assertRedirect(route('peminjaman.saya'))
            ->assertSessionHas('sukses', 'Alat berhasil dikembalikan. Denda: Rp 10.000.');

        $this->assertSame(3, $alat->fresh()->stok);
        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $idPeminjaman,
            'status' => 'dikembalikan',
        ]);
        $this->assertDatabaseHas('pengembalian', [
            'id_peminjaman' => $idPeminjaman,
            'hari_terlambat' => 2,
            'denda' => 10000,
        ]);
        $this->actingAs($petugas)
            ->get(route('pengembalian.pantau'))
            ->assertOk();

        $idPengembalian = (int) Pengembalian::where('id_peminjaman', $idPeminjaman)->value('id_pengembalian');
        $this->actingAs($admin)
            ->get(route('admin.peminjaman.index'))
            ->assertOk();
        $this->get(route('admin.peminjaman.show', $idPeminjaman))->assertOk();
        $this->get(route('admin.pengembalian.index'))->assertOk();
        $this->get(route('admin.pengembalian.show', $idPengembalian))->assertOk();
        $this->get(route('admin.pengembalian.edit', $idPengembalian))->assertOk();

        $this->put(route('admin.pengembalian.update', $idPengembalian), [
            'tgl_kembali' => '2026-10-11',
        ])->assertRedirect(route('admin.pengembalian.index'));
        $this->assertDatabaseHas('pengembalian', [
            'id_pengembalian' => $idPengembalian,
            'hari_terlambat' => 1,
            'denda' => 5000,
        ]);

        $this->delete(route('admin.pengembalian.destroy', $idPengembalian))
            ->assertRedirect(route('admin.pengembalian.index'));
        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $idPeminjaman,
            'status' => 'disetujui',
        ]);
        $this->assertSame(1, $alat->fresh()->stok);

        $this->delete(route('admin.peminjaman.destroy', $idPeminjaman))
            ->assertRedirect(route('admin.peminjaman.index'));
        $this->assertSame(3, $alat->fresh()->stok);

        $this->travelBack();
    }

    public function test_pengajuan_menolak_alat_yang_dipilih_ganda(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => 'Proyektor',
            'stok' => 3,
            'kondisi' => 'baik',
        ]);
        $peminjam = $this->buatPengguna('peminjam');

        $this->actingAs($peminjam)
            ->from(route('peminjaman.ajukan'))
            ->post(route('peminjaman.store'), [
                'tgl_pinjam' => '2026-10-08',
                'tgl_rencana_kembali' => '2026-10-10',
                'alat' => [
                    ['id_alat' => $alat->id_alat, 'jumlah' => 1],
                    ['id_alat' => $alat->id_alat, 'jumlah' => 1],
                ],
            ])
            ->assertSessionHasErrors('alat.1.id_alat');

        $this->assertDatabaseCount('peminjaman', 0);
        $this->assertSame(3, $alat->fresh()->stok);
    }

    public function test_admin_mengubah_status_dan_rincian_peminjaman_dengan_stok_yang_konsisten(): void
    {
        config(['aplikasi.denda_per_hari' => 5000]);
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => 'Proyektor',
            'stok' => 3,
            'kondisi' => 'baik',
        ]);
        $admin = $this->buatPengguna('admin');
        $peminjam = $this->buatPengguna('peminjam');
        $peminjaman = Peminjaman::create([
            'id_peminjam' => $peminjam->id_user,
            'id_petugas' => null,
            'tgl_pinjam' => '2026-10-08',
            'tgl_rencana_kembali' => '2026-10-10',
            'status' => 'menunggu',
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alat->id_alat,
            'jumlah' => 2,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.peminjaman.update', $peminjaman->id_peminjaman), [
                'id_peminjam' => $peminjam->id_user,
                'tgl_pinjam' => '2026-10-08',
                'tgl_rencana_kembali' => '2026-10-10',
                'status' => 'disetujui',
                'alat' => [['id_alat' => $alat->id_alat, 'jumlah' => 3]],
            ])
            ->assertRedirect(route('admin.peminjaman.index'));
        $this->assertSame(0, $alat->fresh()->stok);

        $this->put(route('admin.peminjaman.update', $peminjaman->id_peminjaman), [
            'id_peminjam' => $peminjam->id_user,
            'tgl_pinjam' => '2026-10-08',
            'tgl_rencana_kembali' => '2026-10-10',
            'status' => 'disetujui',
            'alat' => [['id_alat' => $alat->id_alat, 'jumlah' => 4]],
        ])->assertSessionHas('gagal');
        $this->assertSame(0, $alat->fresh()->stok);
        $this->assertDatabaseHas('detail_peminjaman', [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'jumlah' => 3,
        ]);

        $this->travelTo(now()->setDate(2026, 10, 12));
        $this->put(route('admin.peminjaman.update', $peminjaman->id_peminjaman), [
            'id_peminjam' => $peminjam->id_user,
            'tgl_pinjam' => '2026-10-08',
            'tgl_rencana_kembali' => '2026-10-10',
            'status' => 'dikembalikan',
            'alat' => [['id_alat' => $alat->id_alat, 'jumlah' => 3]],
        ])->assertRedirect(route('admin.peminjaman.index'));

        $this->assertSame(3, $alat->fresh()->stok);
        $this->assertDatabaseHas('pengembalian', [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'hari_terlambat' => 2,
            'denda' => 10000,
        ]);
        $this->travelBack();
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
