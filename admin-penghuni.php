<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pesan = "";

// 1. Proses Tambah Penghuni + Otomatis Ubah Status Kamar jadi 'Terisi'
if (isset($_POST['tambah_penghuni'])) {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp     = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $tgl_masuk = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);
    $id_kamar  = (int) $_POST['id_kamar'];

    mysqli_query($conn, "INSERT INTO penghuni (nama, no_hp, tgl_masuk, id_kamar) VALUES ('$nama', '$no_hp', '$tgl_masuk', $id_kamar)");
    mysqli_query($conn, "UPDATE kamar SET status = 'Terisi' WHERE id_kamar = $id_kamar");

    $pesan = "<div style='background:#dcfce7; color:#15803d; padding:12px 16px; border-radius:10px; margin-bottom:18px; font-weight:600;'>Penghuni $nama berhasil ditambahkan & status kamar otomatis menjadi Terisi!</div>";
}

// 2. Proses Edit Data Penghuni + Sinkronisasi Status Kamar jika Pindah Kamar
if (isset($_POST['edit_penghuni'])) {
    $id_penghuni   = (int) $_POST['id_penghuni'];
    $nama          = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp         = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $tgl_masuk     = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);
    $id_kamar_lama = (int) $_POST['id_kamar_lama'];
    $id_kamar_baru = (int) $_POST['id_kamar_baru'];

    mysqli_query($conn, "UPDATE penghuni SET nama = '$nama', no_hp = '$no_hp', tgl_masuk = '$tgl_masuk', id_kamar = $id_kamar_baru WHERE id_penghuni = $id_penghuni");

    // Jika penghuni pindah kamar, kamar lama jadi 'Kosong' dan kamar baru jadi 'Terisi'
    if ($id_kamar_lama !== $id_kamar_baru) {
        mysqli_query($conn, "UPDATE kamar SET status = 'Kosong' WHERE id_kamar = $id_kamar_lama");
        mysqli_query($conn, "UPDATE kamar SET status = 'Terisi' WHERE id_kamar = $id_kamar_baru");
    }

    $pesan = "<div style='background:#dcfce7; color:#15803d; padding:12px 16px; border-radius:10px; margin-bottom:18px; font-weight:600;'>Data penghuni $nama berhasil diperbarui!</div>";
}

// 3. Proses Hapus Penghuni + Otomatis Kembalikan Status Kamar jadi 'Kosong'
if (isset($_GET['hapus']) && isset($_GET['kamar'])) {
    $id_penghuni = (int) $_GET['hapus'];
    $id_kamar    = (int) $_GET['kamar'];

    mysqli_query($conn, "DELETE FROM penghuni WHERE id_penghuni = $id_penghuni");
    mysqli_query($conn, "UPDATE kamar SET status = 'Kosong' WHERE id_kamar = $id_kamar");

    header("Location: admin-penghuni.php");
    exit;
}

// Ambil data penghuni digabung dengan info kamar
$data_penghuni = mysqli_query($conn, "SELECT penghuni.*, kamar.nomor_kamar, kamar.harga_sewa FROM penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar ORDER BY kamar.nomor_kamar ASC");

// Ambil daftar kamar kosong untuk modal Tambah dan modal Edit
$kamar_kosong_tambah = mysqli_query($conn, "SELECT * FROM kamar WHERE status = 'Kosong' ORDER BY nomor_kamar ASC");
$kamar_kosong_edit   = mysqli_query($conn, "SELECT * FROM kamar WHERE status = 'Kosong' ORDER BY nomor_kamar ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Penghuni - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.55); justify-content:center; align-items:center; }
        .modal-content { background:white; padding:28px; border-radius:16px; width:100%; max-width:440px; }
        .btn-danger { text-decoration:none; font-size:12.5px; display:inline-block; }
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
                            <td style="display:flex; gap:6px; align-items:center;">
                                <button class="btn-primary" style="padding:6px 12px; font-size:12.5px;"
                                    onclick="bukaEditPenghuni(
                                        <?= $row['id_penghuni']; ?>,
                                        '<?= htmlspecialchars($row['nama'], ENT_QUOTES); ?>',
                                        '<?= htmlspecialchars($row['no_hp'], ENT_QUOTES); ?>',
                                        '<?= htmlspecialchars($row['tgl_masuk'], ENT_QUOTES); ?>',
                                        <?= $row['id_kamar']; ?>,
                                        '<?= htmlspecialchars($row['nomor_kamar'], ENT_QUOTES); ?>',
                                        <?= $row['harga_sewa']; ?>
                                    )">
                                    Edit
                                </button>
                                <a href="admin-penghuni.php?hapus=<?= $row['id_penghuni']; ?>&kamar=<?= $row['id_kamar']; ?>" class="btn-danger" onclick="return confirm('Hapus penghuni <?= htmlspecialchars($row['nama'], ENT_QUOTES); ?>? Status kamar akan otomatis kembali Kosong.')">Hapus / Keluar</a>
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
            <h2 style="margin-bottom:18px; font-size:20px;">Tambah Penghuni Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap Penghuni</label>
                    <input type="text" name="nama" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="form-group">
                    <label>Pilih Kamar (Hanya Kamar Kosong)</label>
                    <select name="id_kamar" required>
                        <option value="">-- Pilih Kamar Kosong --</option>
                        <?php while ($km = mysqli_fetch_assoc($kamar_kosong_tambah)): ?>
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
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" style="flex:1; padding:10px; border:1px solid #cbd5e1; background:#f8fafc; border-radius:10px; cursor:pointer; font-weight:600;">Batal</button>
                    <button type="submit" name="tambah_penghuni" class="btn-primary" style="flex:2;">Simpan Penghuni</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Penghuni -->
    <div id="modalEditPenghuni" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom:18px; font-size:20px;">Edit Data Penghuni</h2>
            <form method="POST">
                <input type="hidden" name="id_penghuni" id="edit_id_penghuni">
                <input type="hidden" name="id_kamar_lama" id="edit_id_kamar_lama">

                <div class="form-group">
                    <label>Nama Lengkap Penghuni</label>
                    <input type="text" name="nama" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>Kamar Ditempati (Kamar Saat Ini / Kamar Kosong)</label>
                    <select name="id_kamar_baru" id="edit_id_kamar_baru" required>
                        <option value="" id="opsiKamarSaatIni"></option>
                        <?php while ($km2 = mysqli_fetch_assoc($kamar_kosong_edit)): ?>
                            <option value="<?= $km2['id_kamar']; ?>">
                                Pindah ke Kamar <?= $km2['nomor_kamar']; ?> (Rp <?= number_format($km2['harga_sewa'], 0, ',', '.'); ?>/bln)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" id="edit_no_hp" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" id="edit_tgl_masuk" required>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="button" onclick="document.getElementById('modalEditPenghuni').style.display='none'" style="flex:1; padding:10px; border:1px solid #cbd5e1; background:#f8fafc; border-radius:10px; cursor:pointer; font-weight:600;">Batal</button>
                    <button type="submit" name="edit_penghuni" class="btn-primary" style="flex:2;">Update Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaEditPenghuni(id, nama, noHp, tglMasuk, idKamar, nomorKamar, hargaSewa) {
            document.getElementById('edit_id_penghuni').value = id;
            document.getElementById('edit_id_kamar_lama').value = idKamar;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_no_hp').value = noHp;
            document.getElementById('edit_tgl_masuk').value = tglMasuk;

            const opsiAwal = document.getElementById('opsiKamarSaatIni');
            opsiAwal.value = idKamar;
            opsiAwal.text = 'Tetap di Kamar ' + nomorKamar + ' (Rp ' + hargaSewa.toLocaleString('id-ID') + '/bln)';
            document.getElementById('edit_id_kamar_baru').value = idKamar;

            document.getElementById('modalEditPenghuni').style.display = 'flex';
        }
    </script>
</body>
</html>