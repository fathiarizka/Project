<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$filter_bulan = isset($_GET['bulan']) ? mysqli_real_escape_string($conn, $_GET['bulan']) : '';

$where = $filter_bulan !== '' ? "WHERE pembayaran.bulan_tagihan = '$filter_bulan'" : "";
$rekap = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_bayar), 0) AS total_uang, COUNT(*) AS jumlah_transaksi, COALESCE(AVG(jumlah_bayar), 0) AS rata_rata FROM pembayaran $where"));

$riwayat = mysqli_query($conn, "SELECT pembayaran.*, penghuni.nama, kamar.nomor_kamar FROM pembayaran JOIN penghuni ON pembayaran.id_penghuni = penghuni.id_penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar $where ORDER BY pembayaran.tgl_bayar DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <h2>KOSKU</h2>
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="admin-kamar.php">Data Kamar</a>
            <a href="admin-penghuni.php">Data Penghuni</a>
            <a href="admin-pembayaran.php">Pembayaran</a>
            <a href="admin-laporan.php" class="active">Laporan</a>
            <a href="logout.php">Logout</a>
        </aside>

        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h1>Laporan Keuangan <?= $filter_bulan !== '' ? "- $filter_bulan" : "(Semua Periode)"; ?></h1>
                <button class="btn-primary" onclick="window.print()">Export / Cetak PDF</button>
            </div>

            <form method="GET" style="margin-bottom:20px; display:flex; gap:10px;">
                <select name="bulan" style="padding:8px 12px; border-radius:6px; border:1px solid #ccc;">
                    <option value="">-- Semua Bulan --</option>
                    <option value="Juli 2026" <?= $filter_bulan === 'Juli 2026' ? 'selected' : ''; ?>>Juli 2026</option>
                    <option value="Agustus 2026" <?= $filter_bulan === 'Agustus 2026' ? 'selected' : ''; ?>>Agustus 2026</option>
                    <option value="September 2026" <?= $filter_bulan === 'September 2026' ? 'selected' : ''; ?>>September 2026</option>
                    <option value="Oktober 2026" <?= $filter_bulan === 'Oktober 2026' ? 'selected' : ''; ?>>Oktober 2026</option>
                    <option value="November 2026" <?= $filter_bulan === 'November 2026' ? 'selected' : ''; ?>>November 2026</option>
                    <option value="Desember 2026" <?= $filter_bulan === 'Desember 2026' ? 'selected' : ''; ?>>Desember 2026</option>
                </select>
                <button type="submit" class="btn-primary">Filter Periode</button>
            </form>

            <div class="cards">
                <div class="card"><h3>Total Pendapatan</h3><p class="harga" style="color:#27ae60;">Rp <?= number_format($rekap['total_uang'], 0, ',', '.'); ?></p></div>
                <div class="card"><h3>Jumlah Transaksi</h3><p class="harga"><?= $rekap['jumlah_transaksi']; ?></p></div>
                <div class="card"><h3>Rata-Rata Pembayaran</h3><p class="harga">Rp <?= number_format($rekap['rata_rata'], 0, ',', '.'); ?></p></div>
            </div>

            <div class="table-container">
                <h3>Rincian Pemasukan</h3>
                <table>
                    <thead>
                        <tr><th>No</th><th>Tanggal</th><th>Penghuni</th><th>Kamar</th><th>Bulan Tagihan</th><th>Nominal</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($r = mysqli_fetch_assoc($riwayat)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($r['tgl_bayar']); ?></td>
                            <td><?= htmlspecialchars($r['nama']); ?></td>
                            <td><?= htmlspecialchars($r['nomor_kamar']); ?></td>
                            <td><?= htmlspecialchars($r['bulan_tagihan']); ?></td>
                            <td>Rp <?= number_format($r['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td><strong><?= $r['status']; ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>