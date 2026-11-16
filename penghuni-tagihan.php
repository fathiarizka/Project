<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penghuni') {
    header("Location: login.php");
    exit;
}

$profil  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT penghuni.*, kamar.nomor_kamar, kamar.harga_sewa FROM penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar LIMIT 1"));
$tagihan = mysqli_query($conn, "SELECT * FROM pembayaran WHERE id_penghuni = " . (int)$profil['id_penghuni'] . " ORDER BY id_bayar DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tagihan Saya - KOSKU</title>
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
            <a href="penghuni-tagihan.php" class="active">Tagihan Saya</a>
            <a href="penghuni-riwayat.php">Riwayat Pembayaran</a>
            <a href="logout.php">Logout</a>
        </aside>

        <main class="main-content">
            <h1>Tagihan Saya</h1>
            <p style="color:#7f8c8d; margin-top:5px;">Kamar <strong><?= htmlspecialchars($profil['nomor_kamar']); ?></strong> — Tarif Bulanan: <strong>Rp <?= number_format($profil['harga_sewa'], 0, ',', '.'); ?></strong></p>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Bulan Tagihan</th>
                            <th>Total Tagihan</th>
                            <th>Sudah Dibayar</th>
                            <th>Sisa Kekurangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($tagihan) > 0):
                            while ($row = mysqli_fetch_assoc($tagihan)): 
                                $kurang = max(0, $profil['harga_sewa'] - $row['jumlah_bayar']);
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['bulan_tagihan']); ?></strong></td>
                            <td>Rp <?= number_format($profil['harga_sewa'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($kurang, 0, ',', '.'); ?></td>
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
                            <td colspan="6" style="text-align:center;">Belum ada catatan tagihan untuk akun ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>