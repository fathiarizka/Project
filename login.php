<?php
session_start();
include 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 1. Cek login Admin (tabel users)
    $cek_admin = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='admin'");
    if (mysqli_num_rows($cek_admin) === 1) {
        $admin = mysqli_fetch_assoc($cek_admin);
        $_SESSION['id_user']  = $admin['id_user'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['role']     = 'admin';

        header("Location: admin-dashboard.php");
        exit;
    }

    // 2. Cek login Penghuni (tabel penghuni)
    $cek_penghuni = mysqli_query($conn, "SELECT * FROM penghuni WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($cek_penghuni) === 1) {
        $penghuni = mysqli_fetch_assoc($cek_penghuni);
        $_SESSION['id_penghuni'] = $penghuni['id_penghuni'];
        $_SESSION['username']    = $penghuni['username'];
        $_SESSION['nama']        = $penghuni['nama'];
        $_SESSION['role']        = 'penghuni';

        header("Location: penghuni-dashboard.php");
        exit;
    }

    $error = "Username atau password tidak sesuai!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KOSKU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <h2>Selamat Datang</h2>
            <p class="subtitle">Masuk sebagai Admin atau Penghuni KOSKU</p>

            <?php if ($error !== ""): ?>
                <div style="background:#fee2e2; color:#dc2626; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center; font-size:14px; font-weight:600;">
                    <?= $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username (admin / rina)" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                <button type="submit" name="login" class="btn-primary btn-full">Login</button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" style="color: #2563eb; text-decoration: none; font-size: 14px; font-weight:600;">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>