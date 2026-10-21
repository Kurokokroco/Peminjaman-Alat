<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $tarifDenda = (int) config('aplikasi.denda_per_hari');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_kembalikan_alat');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_laporan_peminjaman');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_cek_stok_alat');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_hitung_denda');

        DB::unprepared(<<<'SQL'
CREATE FUNCTION fn_hitung_denda(p_tgl_rencana DATE, p_tgl_kembali DATE, p_tarif INT)
RETURNS INT
DETERMINISTIC
NO SQL
BEGIN
    DECLARE v_hari_terlambat INT;

    SET v_hari_terlambat = DATEDIFF(p_tgl_kembali, p_tgl_rencana);

    IF v_hari_terlambat > 0 THEN
        RETURN v_hari_terlambat * p_tarif;
    END IF;

    RETURN 0;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_cek_stok_alat
BEFORE UPDATE ON alat
FOR EACH ROW
BEGIN
    IF NEW.stok < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stok alat tidak boleh kurang dari 0.';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE PROCEDURE sp_laporan_peminjaman(IN p_dari DATE, IN p_sampai DATE)
BEGIN
    SELECT
        p.id_peminjaman,
        u.nama_lengkap AS nama_peminjam,
        a.nama_alat,
        d.jumlah,
        p.tgl_pinjam,
        p.tgl_rencana_kembali,
        pg.tgl_kembali,
        pg.hari_terlambat,
        pg.denda,
        p.status
    FROM peminjaman AS p
    INNER JOIN users AS u ON u.id_user = p.id_peminjam
    INNER JOIN detail_peminjaman AS d ON d.id_peminjaman = p.id_peminjaman
    INNER JOIN alat AS a ON a.id_alat = d.id_alat
    LEFT JOIN pengembalian AS pg ON pg.id_peminjaman = p.id_peminjaman
    WHERE (p_dari IS NULL OR p.tgl_pinjam >= p_dari)
      AND (p_sampai IS NULL OR p.tgl_pinjam <= p_sampai)
    ORDER BY p.tgl_pinjam DESC, p.id_peminjaman DESC;
END
SQL);

        DB::unprepared(<<<SQL
CREATE PROCEDURE sp_kembalikan_alat(IN p_id_peminjaman INT)
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_tgl_rencana DATE;
    DECLARE v_tgl_kembali DATE;
    DECLARE v_hari_terlambat INT;
    DECLARE v_denda INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT status, tgl_rencana_kembali
    INTO v_status, v_tgl_rencana
    FROM peminjaman
    WHERE id_peminjaman = p_id_peminjaman
    FOR UPDATE;

    IF v_status IS NULL OR v_status <> 'disetujui' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Peminjaman tidak ditemukan atau tidak sedang dipinjam.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM detail_peminjaman
        WHERE id_peminjaman = p_id_peminjaman
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Peminjaman belum memiliki detail alat.';
    END IF;

    SET v_tgl_kembali = CURDATE();
    SET v_hari_terlambat = GREATEST(0, DATEDIFF(v_tgl_kembali, v_tgl_rencana));
    SET v_denda = fn_hitung_denda(v_tgl_rencana, v_tgl_kembali, {$tarifDenda});

    UPDATE alat AS a
    INNER JOIN (
        SELECT id_alat, SUM(jumlah) AS jumlah
        FROM detail_peminjaman
        WHERE id_peminjaman = p_id_peminjaman
        GROUP BY id_alat
    ) AS d ON d.id_alat = a.id_alat
    SET a.stok = a.stok + d.jumlah;

    INSERT INTO pengembalian (
        id_peminjaman,
        tgl_kembali,
        hari_terlambat,
        denda,
        created_at,
        updated_at
    ) VALUES (
        p_id_peminjaman,
        v_tgl_kembali,
        v_hari_terlambat,
        v_denda,
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    );

    UPDATE peminjaman
    SET status = 'dikembalikan', updated_at = CURRENT_TIMESTAMP
    WHERE id_peminjaman = p_id_peminjaman;

    COMMIT;
END
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_kembalikan_alat');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_laporan_peminjaman');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_cek_stok_alat');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_hitung_denda');
    }
};
