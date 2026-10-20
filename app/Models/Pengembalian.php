<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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

    public static function hitungDenda(int $hariTerlambat): int
    {
        return $hariTerlambat * (int) config('aplikasi.denda_per_hari');
    }

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }
}
