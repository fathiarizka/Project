<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penghuni') {
    header("Location: login.php");
    exit;
}

$profil  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT penghuni.*, kamar.nomor_kamar FROM penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar LIMIT 1"));
$riwayat = mysqli_query($conn, "SELECT * FROM pembayaran WHERE id_penghuni = " . (int)$profil['id_penghuni'] . " ORDER BY tgl_bayar DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pembayaran - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .badge-lunas { background:#d4edda; color:#155724; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; }
        .badge-belum { background:#f8d7da; color:#721c24; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; }
    </style>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <h2>KOSKU</h2>
            <a href="penghuni-dashboard.php">Dashboard</a>
            <a href="penghuni-tagihan.php">Tagihan Saya</a>
            <a href="penghuni-riwayat.php" class="active">Riwayat Pembayaran</a>
            <a href="logout.php">Logout</a>
        </aside>

        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h1>Riwayat Pembayaran</h1>
                <button class="btn-primary" onclick="window.print()">Cetak Riwayat</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Bayar</th>
                            <th>Bulan Tagihan</th>
                            <th>Metode Pembayaran</th>
                            <th>Nominal Dibayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($riwayat) > 0):
                            while ($row = mysqli_fetch_assoc($riwayat)): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['tgl_bayar']); ?></td>
                            <td><strong><?= htmlspecialchars($row['bulan_tagihan']); ?></strong></td>
                            <td><?= htmlspecialchars($row['metode']); ?></td>
                            <td>Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if ($row['status'] === 'Lunas'): ?>
                                    <span class="badge-lunas">Lunas</span>
                                <?php else: ?>
                                    <span class="badge-belum">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Belum ada riwayat pembayaran.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>