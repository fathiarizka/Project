<?php
include 'koneksi.php';
$kamar_kosong = mysqli_query($conn, "SELECT * FROM kamar WHERE status = 'Kosong' ORDER BY nomor_kamar ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOSKU - Sistem Informasi Pengelolaan Kos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">KOSKU</div>
        <div class="nav-links">
            <a href="#kamar">Available Rooms</a>
            <a href="#fasilitas">Facilities</a>
            <a href="#kontak">Contact</a>
            <a href="login.php" class="btn-primary">Login Admin / Penghuni</a>
        </div>
    </nav>

    <header style="text-align: center; padding: 70px 20px; background: #34495e; color: white;">
        <h1 style="font-size: 36px;">Selamat Datang di KOSKU</h1>
        <p style="margin: 15px 0 25px; font-size: 18px;">Hunian nyaman, bersih, dan aman dengan sistem pengelolaan modern.</p>
        <a href="#kamar" class="btn-primary">Lihat Kamar Tersedia</a>
    </header>

    <section id="kamar" class="section">
        <h2>Available Rooms (Kamar Tersedia)</h2>
        <div class="cards">
            <?php if (mysqli_num_rows($kamar_kosong) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($kamar_kosong)): ?>
                    <div class="card">
                        <h3>Kamar <?= htmlspecialchars($row['nomor_kamar']); ?></h3>
                        <div class="harga">Rp <?= number_format($row['harga_sewa'], 0, ',', '.'); ?> / bln</div>
                        <p>Fasilitas: <?= htmlspecialchars($row['fasilitas']); ?></p>
                        <span class="badge-lunas" style="display:inline-block; margin-top:10px;">Tersedia</span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <p>Maaf, saat ini semua kamar sudah terisi penuh.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="fasilitas" class="section">
        <h2>Facilities (Fasilitas Kos)</h2>
        <div class="cards">
            <div class="card">
                <h3>Parking Area</h3>
                <p>Area parkir motor dan mobil yang luas serta aman.</p>
            </div>
            <div class="card">
                <h3>Full AC</h3>
                <p>Setiap kamar dilengkapi pendingin ruangan (AC).</p>
            </div>
            <div class="card">
                <h3>High-Speed WiFi</h3>
                <p>Akses internet cepat 24 jam nonstop untuk penghuni.</p>
            </div>
        </div>
    </section>

    <footer id="kontak" style="background: #2c3e50; color: white; text-align: center; padding: 30px 20px; margin-top: 40px;">
        <h3>Hubungi Pemilik Kos</h3>
        <p style="margin-top: 8px;">WhatsApp / Telp: 0812-3456-7890 | Alamat: Jl. Pendidikan No. 10</p>
    </footer>
</body>
</html>