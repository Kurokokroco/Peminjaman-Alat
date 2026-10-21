<?php

namespace Tests\Unit;

use App\Models\Pengembalian;
use Tests\TestCase;

class PerhitunganDendaTest extends TestCase
{
    public function test_menghitung_hari_terlambat_dan_tarif_denda(): void
    {
        config(['aplikasi.denda_per_hari' => 5000]);

        $hariTerlambat = Pengembalian::hitungHariTerlambat('2026-10-10', '2026-10-12');

        $this->assertSame(2, $hariTerlambat);
        $this->assertSame(10000, Pengembalian::hitungDenda('2026-10-10', '2026-10-12'));
    }

    public function test_tidak_mengenakan_denda_jika_kembali_tepat_waktu_atau_lebih_awal(): void
    {
        $this->assertSame(0, Pengembalian::hitungHariTerlambat('2026-10-10', '2026-10-10'));
        $this->assertSame(0, Pengembalian::hitungHariTerlambat('2026-10-10', '2026-10-09'));
    }
}
