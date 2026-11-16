<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penghuni') {
    header("Location: login.php");
    exit;
}

$profil  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT penghuni.*, kamar.nomor_kamar, kamar.harga_sewa, kamar.fasilitas FROM penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar LIMIT 1"));
$riwayat = mysqli_query($conn, "SELECT * FROM pembayaran WHERE id_penghuni = " . (int)$profil['id_penghuni'] . " ORDER BY id_bayar DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Penghuni - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <h2>KOSKU</h2>
            <a href="penghuni-dashboard.php" class="active">Dashboard</a>
            <a href="penghuni-tagihan.php">Tagihan Saya</a>
            <a href="penghuni-riwayat.php">Riwayat Pembayaran</a>
            <a href="logout.php">Logout</a>
        </aside>

        <main class="main-content">
            <h1>Dashboard Penghuni - <?= htmlspecialchars($profil['nama']); ?></h1>
            <div class="cards">
                <div class="card">
                    <h3>Informasi Kamar</h3>
                    <p style="margin-top:8px;"><strong>Nama:</strong> <?= htmlspecialchars($profil['nama']); ?></p>
                    <p><strong>Nomor Kamar:</strong> <?= htmlspecialchars($profil['nomor_kamar']); ?></p>
                    <p><strong>Fasilitas:</strong> <?= htmlspecialchars($profil['fasilitas']); ?></p>
                    <p><strong>Harga Sewa:</strong> Rp <?= number_format($profil['harga_sewa'], 0, ',', '.'); ?> / bln</p>
                </div>
                <div class="card">
                    <h3>Info Rekening Pembayaran</h3>
                    <p style="margin-top:8px;"><strong>Bank BCA:</strong> 123-456-7890</p>
                    <p><strong>Bank BRI:</strong> 0987-6543-2100</p>
                    <small>A.n. Pengelola KOSKU</small>
                </div>
            </div>

            <div class="table-container">
                <h3>Ringkasan Pembayaran Terakhir</h3>
                <table>
                    <thead>
                        <tr><th>Bulan</th><th>Nominal Dibayar</th><th>Metode</th><th>Tanggal</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($b = mysqli_fetch_assoc($riwayat)): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['bulan_tagihan']); ?></td>
                            <td>Rp <?= number_format($b['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($b['metode']); ?></td>
                            <td><?= htmlspecialchars($b['tgl_bayar']); ?></td>
                            <td style="font-weight:bold; color:<?= $b['status'] === 'Lunas' ? '#27ae60' : '#e74c3c'; ?>;"><?= $b['status']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>