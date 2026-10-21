<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pengembalian extends Model
{
    protected $table = 'pengembalian';

    protected $primaryKey = 'id_pengembalian';

    protected $fillable = ['id_peminjaman', 'tgl_kembali', 'hari_terlambat', 'denda'];

    public static function hitungHariTerlambat(string $tglRencanaKembali, string $tglKembali): int
    {
        $rencana = Carbon::parse($tglRencanaKembali)->startOfDay();
        $kembali = Carbon::parse($tglKembali)->startOfDay();

        return $kembali->greaterThan($rencana)
            ? (int) $rencana->diffInDays($kembali)
            : 0;
    }

    public static function hitungDenda(string $tglRencanaKembali, string $tglKembali): int
    {
        $tarif = (int) config('aplikasi.denda_per_hari');

        if (DB::connection()->getDriverName() === 'mysql') {
            $hasil = DB::selectOne(
                'SELECT fn_hitung_denda(?, ?, ?) AS denda',
                [$tglRencanaKembali, $tglKembali, $tarif]
            );

            return (int) $hasil->denda;
        }

        return self::hitungHariTerlambat($tglRencanaKembali, $tglKembali) * $tarif;
    }

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }
}
