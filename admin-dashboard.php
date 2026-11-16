<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Hitung statistik otomatis dari database
$total_kamar    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM kamar"))['total'];
$kamar_terisi   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM kamar WHERE status = 'Terisi'"))['total'];
$kamar_kosong   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM kamar WHERE status = 'Kosong'"))['total'];
$total_penghuni = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM penghuni"))['total'];
$total_masuk    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_bayar), 0) AS total FROM pembayaran"))['total'];

$persen_terisi  = $total_kamar > 0 ? round(($kamar_terisi / $total_kamar) * 100) : 0;
$persen_kosong  = $total_kamar > 0 ? 100 - $persen_terisi : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <h2>KOSKU</h2>
            <a href="admin-dashboard.php" class="active">Dashboard</a>
            <a href="admin-kamar.php">Data Kamar</a>
            <a href="admin-penghuni.php">Data Penghuni</a>
            <a href="admin-pembayaran.php">Pembayaran</a>
            <a href="admin-laporan.php">Laporan</a>
            <a href="logout.php">Logout</a>
        </aside>

        <main class="main-content">
            <h1>Dashboard Admin</h1>
            <p style="color:#7f8c8d; margin-top:5px;">Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>!</p>

            <div class="cards">
                <div class="card"><h3>Total Kamar</h3><p class="harga"><?= $total_kamar; ?></p></div>
                <div class="card"><h3>Kamar Terisi</h3><p class="harga" style="color:#27ae60;"><?= $kamar_terisi; ?></p></div>
                <div class="card"><h3>Kamar Kosong</h3><p class="harga" style="color:#e67e22;"><?= $kamar_kosong; ?></p></div>
                <div class="card"><h3>Total Penghuni</h3><p class="harga"><?= $total_penghuni; ?></p></div>
            </div>

            <div class="cards">
                <div class="card">
                    <h3>Total Pemasukan Tercatat</h3>
                    <p class="harga" style="color:#27ae60;">Rp <?= number_format($total_masuk, 0, ',', '.'); ?></p>
                    <small>Akumulasi seluruh pembayaran yang masuk ke sistem</small>
                </div>
                <div class="card">
                    <h3>Okupansi Kamar</h3>
                    <p class="harga" style="color:#27ae60;"><?= $persen_terisi; ?>% Terisi</p>
                    <small><?= $persen_kosong; ?>% Kosong</small>
                </div>
            </div>
        </main>
    </div>
</body>
</html>