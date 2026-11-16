<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pesan = "";

// 1. Tambah Penghuni + Otomatis Ubah Status Kamar jadi 'Terisi'
if (isset($_POST['tambah_penghuni'])) {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp     = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $tgl_masuk = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);
    $id_kamar  = (int) $_POST['id_kamar'];

    mysqli_query($conn, "INSERT INTO penghuni (nama, no_hp, tgl_masuk, id_kamar) VALUES ('$nama', '$no_hp', '$tgl_masuk', $id_kamar)");
    mysqli_query($conn, "UPDATE kamar SET status = 'Terisi' WHERE id_kamar = $id_kamar");

    $pesan = "<div style='background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px;'>Penghuni $nama berhasil ditambahkan & status kamar otomatis menjadi Terisi!</div>";
}

// 2. Hapus Penghuni + Otomatis Kembalikan Status Kamar jadi 'Kosong'
if (isset($_GET['hapus']) && isset($_GET['kamar'])) {
    $id_penghuni = (int) $_GET['hapus'];
    $id_kamar    = (int) $_GET['kamar'];

    mysqli_query($conn, "DELETE FROM penghuni WHERE id_penghuni = $id_penghuni");
    mysqli_query($conn, "UPDATE kamar SET status = 'Kosong' WHERE id_kamar = $id_kamar");

    header("Location: admin-penghuni.php");
    exit;
}

$data_penghuni = mysqli_query($conn, "SELECT penghuni.*, kamar.nomor_kamar, kamar.harga_sewa FROM penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar ORDER BY kamar.nomor_kamar ASC");
$kamar_kosong  = mysqli_query($conn, "SELECT * FROM kamar WHERE status = 'Kosong' ORDER BY nomor_kamar ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Penghuni - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
        .modal-content { background:white; padding:25px; border-radius:10px; width:100%; max-width:420px; }
        .btn-danger { background:#e74c3c; color:white; padding:5px 10px; border-radius:4px; text-decoration:none; font-size:12px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <h2>KOSKU</h2>
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="admin-kamar.php">Data Kamar</a>
            <a href="admin-penghuni.php" class="active">Data Penghuni</a>
            <a href="admin-pembayaran.php">Pembayaran</a>
            <a href="admin-laporan.php">Laporan</a>
            <a href="logout.php">Logout</a>
        </aside>

        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h1>Data Penghuni</h1>
                <button class="btn-primary" onclick="document.getElementById('modalTambah').style.display='flex'">+ Tambah Penghuni</button>
            </div>

            <?= $pesan; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Penghuni</th>
                            <th>Nomor Kamar</th>
                            <th>Tarif Sewa</th>
                            <th>No. HP</th>
                            <th>Tgl Masuk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($data_penghuni)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                            <td><?= htmlspecialchars($row['nomor_kamar']); ?></td>
                            <td>Rp <?= number_format($row['harga_sewa'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($row['no_hp']); ?></td>
                            <td><?= htmlspecialchars($row['tgl_masuk']); ?></td>
                            <td>
                                <a href="admin-penghuni.php?hapus=<?= $row['id_penghuni']; ?>&kamar=<?= $row['id_kamar']; ?>" class="btn-danger" onclick="return confirm('Hapus penghuni <?= $row['nama']; ?>? Status kamar akan otomatis kembali Kosong.')">Hapus / Keluar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Tambah Penghuni -->
    <div id="modalTambah" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom:15px;">Tambah Penghuni Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap Penghuni</label>
                    <input type="text" name="nama" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="form-group">
                    <label>Pilih Kamar (Hanya Kamar Kosong)</label>
                    <select name="id_kamar" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;" required>
                        <option value="">-- Pilih Kamar Kosong --</option>
                        <?php while ($km = mysqli_fetch_assoc($kamar_kosong)): ?>
                            <option value="<?= $km['id_kamar']; ?>">
                                Kamar <?= $km['nomor_kamar']; ?> (Rp <?= number_format($km['harga_sewa'], 0, ',', '.'); ?>/bln)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" placeholder="Contoh: 0812-3456-7890" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" required>
                </div>
                <div style="display:flex; gap:10px; margin-top:15px;">
                    <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" style="flex:1; padding:10px; border:none; border-radius:6px; cursor:pointer;">Batal</button>
                    <button type="submit" name="tambah_penghuni" class="btn-primary" style="flex:2;">Simpan Penghuni</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>