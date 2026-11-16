<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pesan = "";

// 1. Tambah Kamar Baru
if (isset($_POST['tambah_kamar'])) {
    $nomor_kamar = mysqli_real_escape_string($conn, $_POST['nomor_kamar']);
    $harga_sewa  = (int) $_POST['harga_sewa'];
    $fasilitas   = mysqli_real_escape_string($conn, $_POST['fasilitas']);

    $cek = mysqli_query($conn, "SELECT * FROM kamar WHERE nomor_kamar = '$nomor_kamar'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div style='background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:15px;'>Nomor kamar $nomor_kamar sudah terdaftar!</div>";
    } else {
        mysqli_query($conn, "INSERT INTO kamar (nomor_kamar, harga_sewa, fasilitas, status) VALUES ('$nomor_kamar', $harga_sewa, '$fasilitas', 'Kosong')");
        $pesan = "<div style='background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px;'>Kamar $nomor_kamar berhasil ditambahkan!</div>";
    }
}

// 2. Edit Kamar
if (isset($_POST['edit_kamar'])) {
    $id_kamar   = (int) $_POST['id_kamar'];
    $harga_sewa = (int) $_POST['harga_sewa'];
    $fasilitas  = mysqli_real_escape_string($conn, $_POST['fasilitas']);

    mysqli_query($conn, "UPDATE kamar SET harga_sewa = $harga_sewa, fasilitas = '$fasilitas' WHERE id_kamar = $id_kamar");
    $pesan = "<div style='background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px;'>Data kamar berhasil diperbarui!</div>";
}

// 3. Hapus Kamar
if (isset($_GET['hapus'])) {
    $id_kamar = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kamar WHERE id_kamar = $id_kamar");
    header("Location: admin-kamar.php");
    exit;
}

$data_kamar = mysqli_query($conn, "SELECT * FROM kamar ORDER BY nomor_kamar ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kamar - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
        .modal-content { background:white; padding:25px; border-radius:10px; width:100%; max-width:420px; }
        .badge-lunas { background:#d4edda; color:#155724; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; }
        .badge-belum { background:#fff3cd; color:#856404; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; }
        .btn-danger { background:#e74c3c; color:white; padding:5px 10px; border-radius:4px; text-decoration:none; font-size:12px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <h2>KOSKU</h2>
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="admin-kamar.php" class="active">Data Kamar</a>
            <a href="admin-penghuni.php">Data Penghuni</a>
            <a href="admin-pembayaran.php">Pembayaran</a>
            <a href="admin-laporan.php">Laporan</a>
            <a href="logout.php">Logout</a>
        </aside>

        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h1>Data Kamar</h1>
                <button class="btn-primary" onclick="document.getElementById('modalTambah').style.display='flex'">+ Tambah Kamar</button>
            </div>

            <?= $pesan; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Kamar</th>
                            <th>Harga Sewa</th>
                            <th>Fasilitas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($data_kamar)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nomor_kamar']); ?></strong></td>
                            <td>Rp <?= number_format($row['harga_sewa'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($row['fasilitas']); ?></td>
                            <td>
                                <?php if ($row['status'] === 'Terisi'): ?>
                                    <span class="badge-lunas">Terisi</span>
                                <?php else: ?>
                                    <span class="badge-belum">Kosong</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-primary" style="padding:5px 10px; font-size:12px;" 
                                    onclick="bukaEdit(<?= $row['id_kamar']; ?>, '<?= htmlspecialchars($row['nomor_kamar']); ?>', <?= $row['harga_sewa']; ?>, '<?= htmlspecialchars($row['fasilitas']); ?>')">
                                    Edit
                                </button>
                                <a href="admin-kamar.php?hapus=<?= $row['id_kamar']; ?>" class="btn-danger" onclick="return confirm('Yakin ingin menghapus kamar <?= $row['nomor_kamar']; ?>?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Tambah Kamar -->
    <div id="modalTambah" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom:15px;">Tambah Kamar Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nomor Kamar</label>
                    <input type="text" name="nomor_kamar" placeholder="Contoh: A04" required>
                </div>
                <div class="form-group">
                    <label>Harga Sewa (Rp / bulan)</label>
                    <input type="number" name="harga_sewa" placeholder="Contoh: 600000" required>
                </div>
                <div class="form-group">
                    <label>Fasilitas</label>
                    <input type="text" name="fasilitas" placeholder="Contoh: AC, WiFi, Kasur" required>
                </div>
                <div style="display:flex; gap:10px; margin-top:15px;">
                    <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" style="flex:1; padding:10px; border:none; border-radius:6px; cursor:pointer;">Batal</button>
                    <button type="submit" name="tambah_kamar" class="btn-primary" style="flex:2;">Simpan Kamar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Kamar -->
    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom:15px;">Edit Kamar <span id="judulEditKamar"></span></h2>
            <form method="POST">
                <input type="hidden" name="id_kamar" id="edit_id_kamar">
                <div class="form-group">
                    <label>Harga Sewa (Rp / bulan)</label>
                    <input type="number" name="harga_sewa" id="edit_harga" required>
                </div>
                <div class="form-group">
                    <label>Fasilitas</label>
                    <input type="text" name="fasilitas" id="edit_fasilitas" required>
                </div>
                <div style="display:flex; gap:10px; margin-top:15px;">
                    <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" style="flex:1; padding:10px; border:none; border-radius:6px; cursor:pointer;">Batal</button>
                    <button type="submit" name="edit_kamar" class="btn-primary" style="flex:2;">Update Kamar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaEdit(id, nomor, harga, fasilitas) {
            document.getElementById('edit_id_kamar').value = id;
            document.getElementById('judulEditKamar').innerText = nomor;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('edit_fasilitas').value = fasilitas;
            document.getElementById('modalEdit').style.display = 'flex';
        }
    </script>
</body>
</html>