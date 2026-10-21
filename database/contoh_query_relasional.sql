-- INNER JOIN: menampilkan peminjaman, nama peminjam, dan alat yang diminta.
SELECT
    p.id_peminjaman,
    u.nama_lengkap AS nama_peminjam,
    a.nama_alat,
    d.jumlah,
    p.status
FROM peminjaman AS p
INNER JOIN users AS u ON u.id_user = p.id_peminjam
INNER JOIN detail_peminjaman AS d ON d.id_peminjaman = p.id_peminjaman
INNER JOIN alat AS a ON a.id_alat = d.id_alat;

-- LEFT JOIN: menampilkan semua peminjaman termasuk yang belum dikembalikan.
SELECT
    p.id_peminjaman,
    u.nama_lengkap AS nama_peminjam,
    p.status,
    pg.tgl_kembali,
    pg.denda
FROM peminjaman AS p
LEFT JOIN users AS u ON u.id_user = p.id_peminjam
LEFT JOIN pengembalian AS pg ON pg.id_peminjaman = p.id_peminjaman;

-- GROUP BY dan HAVING: mencari alat yang total peminjamannya lebih dari satu.
SELECT
    a.id_alat,
    a.nama_alat,
    SUM(d.jumlah) AS total_dipinjam
FROM alat AS a
INNER JOIN detail_peminjaman AS d ON d.id_alat = a.id_alat
GROUP BY a.id_alat, a.nama_alat
HAVING SUM(d.jumlah) > 1;

-- Subquery: mencari peminjam yang pernah memiliki peminjaman terlambat.
SELECT u.id_user, u.nama_lengkap
FROM users AS u
WHERE EXISTS (
    SELECT 1
    FROM peminjaman AS p
    INNER JOIN pengembalian AS pg ON pg.id_peminjaman = p.id_peminjaman
    WHERE p.id_peminjam = u.id_user
      AND pg.hari_terlambat > 0
);

-- Contoh rollback: perubahan stok dibatalkan sehingga nilai awal tetap.
START TRANSACTION;
UPDATE alat SET stok = stok + 1 WHERE id_alat = 1;
ROLLBACK;

-- Contoh commit: perubahan stok disimpan ke database.
START TRANSACTION;
UPDATE alat SET stok = stok + 1 WHERE id_alat = 1;
COMMIT;
