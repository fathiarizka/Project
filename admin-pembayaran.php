<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pesan = "";

// Proses Simpan Pembayaran Semiotomatis
if (isset($_POST['simpan_bayar'])) {
    $id_penghuni   = (int) $_POST['id_penghuni'];
    $bulan_tagihan = mysqli_real_escape_string($conn, $_POST['bulan_tagihan']);
    $tagihan_angka = (int) $_POST['tagihan_angka'];
    $jumlah_bayar  = (int) $_POST['jumlah_bayar'];
    $tgl_bayar     = mysqli_real_escape_string($conn, $_POST['tgl_bayar']);
    $metode        = mysqli_real_escape_string($conn, $_POST['metode']);

    // Algoritma penentu status otomatis
    $status = ($jumlah_bayar >= $tagihan_angka) ? 'Lunas' : 'Belum Lunas';

    // Cek anti-duplikasi bulan yang sama
    $cek = mysqli_query($conn, "SELECT * FROM pembayaran WHERE id_penghuni = $id_penghuni AND bulan_tagihan = '$bulan_tagihan'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div style='background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:15px;'>Gagal! Pembayaran penghuni ini untuk bulan $bulan_tagihan sudah tercatat sebelumnya.</div>";
    } else {
        mysqli_query($conn, "INSERT INTO pembayaran (id_penghuni, bulan_tagihan, jumlah_bayar, tgl_bayar, metode, status) VALUES ($id_penghuni, '$bulan_tagihan', $jumlah_bayar, '$tgl_bayar', '$metode', '$status')");
        $pesan = "<div style='background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px;'>Pembayaran berhasil disimpan dengan status: <strong>$status</strong>!</div>";
    }
}

// Ambil daftar penghuni & kamar untuk dropdown otomatis
$list_penghuni = mysqli_query($conn, "SELECT penghuni.id_penghuni, penghuni.nama, kamar.nomor_kamar, kamar.harga_sewa FROM penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar ORDER BY penghuni.nama ASC");

// Ambil riwayat seluruh pembayaran
$data_bayar = mysqli_query($conn, "SELECT pembayaran.*, penghuni.nama, kamar.nomor_kamar, kamar.harga_sewa FROM pembayaran JOIN penghuni ON pembayaran.id_penghuni = penghuni.id_penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar ORDER BY pembayaran.id_bayar DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pembayaran - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
        .modal-content { background:white; padding:25px; border-radius:10px; width:100%; max-width:480px; }
        .input-otomatis { background-color:#eef2f5; color:#2c3e50; font-weight:bold; }
        .badge-lunas { background:#d4edda; color:#155724; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; }
        .badge-belum { background:#f8d7da; color:#721c24; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; }
        .status-preview { padding:10px; border-radius:6px; text-align:center; font-weight:bold; margin-top:10px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <h2>KOSKU</h2>
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="admin-kamar.php">Data Kamar</a>
            <a href="admin-penghuni.php">Data Penghuni</a>
            <a href="admin-pembayaran.php" class="active">Pembayaran</a>
            <a href="admin-laporan.php">Laporan</a>
            <a href="logout.php">Logout</a>
        </aside>

        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h1>Data Pembayaran</h1>
                <button class="btn-primary" onclick="document.getElementById('modalBayar').style.display='flex'">+ Catat Pembayaran</button>
            </div>

            <?= $pesan; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kamar</th>
                            <th>Bulan</th>
                            <th>Tagihan</th>
                            <th>Dibayar</th>
                            <th>Metode</th>
                            <th>Tgl Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($data_bayar)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                            <td><?= htmlspecialchars($row['nomor_kamar']); ?></td>
                            <td><?= htmlspecialchars($row['bulan_tagihan']); ?></td>
                            <td>Rp <?= number_format($row['harga_sewa'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($row['metode']); ?></td>
                            <td><?= htmlspecialchars($row['tgl_bayar']); ?></td>
                            <td>
                                <?php if ($row['status'] === 'Lunas'): ?>
                                    <span class="badge-lunas">Lunas</span>
                                <?php else: ?>
                                    <span class="badge-belum">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Form Pembayaran Semiotomatis -->
    <div id="modalBayar" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom:15px;">Formulir Pembayaran Kos</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Penghuni (Pilih)</label>
                    <select name="id_penghuni" id="pilihPenghuni" onchange="isiDataOtomatis()" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;" required>
                        <option value="">-- Pilih Penghuni --</option>
                        <?php while ($p = mysqli_fetch_assoc($list_penghuni)): ?>
                            <option value="<?= $p['id_penghuni']; ?>" data-kamar="<?= htmlspecialchars($p['nomor_kamar']); ?>" data-tagihan="<?= $p['harga_sewa']; ?>">
                                <?= htmlspecialchars($p['nama']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label>Nomor Kamar (Otomatis)</label>
                        <input type="text" id="noKamar" class="input-otomatis" readonly placeholder="-">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Tagihan Bulanan (Otomatis)</label>
                        <input type="text" id="tagihanTampil" class="input-otomatis" readonly placeholder="Rp 0">
                        <input type="hidden" name="tagihan_angka" id="tagihanAngka" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Bulan Tagihan</label>
                    <select name="bulan_tagihan" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;" required>
                        <option value="Juli 2026">Juli 2026</option>
                        <option value="Agustus 2026">Agustus 2026</option>
                        <option value="September 2026">September 2026</option>
                        <option value="Oktober 2026">Oktober 2026</option>
                        <option value="November 2026">November 2026</option>
                        <option value="Desember 2026">Desember 2026</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jumlah Pembayaran (Input Admin)</label>
                    <input type="number" name="jumlah_bayar" id="jumlahBayar" oninput="cekStatusOtomatis()" placeholder="Masukkan nominal bayar" required>
                </div>

                <div style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label>Tanggal Pembayaran</label>
                        <input type="date" name="tgl_bayar" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Metode Pembayaran</label>
                        <select name="metode" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;" required>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Tunai">Tunai</option>
                            <option value="E-Wallet / QRIS">E-Wallet / QRIS</option>
                        </select>
                    </div>
                </div>

                <div id="boxStatus" class="status-preview" style="background:#f1f2f6; color:#555;">
                    Status Pembayaran: Menunggu Input...
                </div>

                <div style="display:flex; gap:10px; margin-top:15px;">
                    <button type="button" onclick="document.getElementById('modalBayar').style.display='none'" style="flex:1; padding:10px; border:none; border-radius:6px; cursor:pointer;">Batal</button>
                    <button type="submit" name="simpan_bayar" class="btn-primary" style="flex:2;">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function isiDataOtomatis() {
            const select = document.getElementById('pilihPenghuni');
            const opsi = select.options[select.selectedIndex];
            const kamar = opsi.getAttribute('data-kamar') || '-';
            const tagihan = parseInt(opsi.getAttribute('data-tagihan')) || 0;

            document.getElementById('noKamar').value = kamar;
            document.getElementById('tagihanAngka').value = tagihan;
            document.getElementById('tagihanTampil').value = tagihan > 0 ? 'Rp ' + tagihan.toLocaleString('id-ID') : 'Rp 0';
            cekStatusOtomatis();
        }

        function cekStatusOtomatis() {
            const tagihan = parseInt(document.getElementById('tagihanAngka').value) || 0;
            const bayar = parseInt(document.getElementById('jumlahBayar').value) || 0;
            const box = document.getElementById('boxStatus');

            if (tagihan === 0 || bayar === 0) {
                box.style.background = '#f1f2f6'; box.style.color = '#555';
                box.innerText = 'Status Pembayaran: Menunggu Input...';
            } else if (bayar >= tagihan) {
                box.style.background = '#d4edda'; box.style.color = '#155724';
                box.innerText = 'Status Pembayaran: LUNAS';
            } else {
                const kurang = tagihan - bayar;
                box.style.background = '#f8d7da'; box.style.color = '#721c24';
                box.innerText = 'Status Pembayaran: BELUM LUNAS (Kurang Rp ' + kurang.toLocaleString('id-ID') + ')';
            }
        }
    </script>
</body>
</html>