<?php
session_start();
include 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user']  = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role'];

        if ($data['role'] === 'admin') {
            header("Location: admin-dashboard.php");
            exit;
        } else {
            header("Location: penghuni-dashboard.php");
            exit;
        }
    } else {
        $error = "Username atau password tidak sesuai!";
    }
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
            <p class="subtitle">Masuk untuk melanjutkan ke Sistem KOSKU</p>

            <?php if ($error !== ""): ?>
                <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:15px; text-align:center; font-size:14px;">
                    <?= $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username (admin / penghuni)" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                <button type="submit" name="login" class="btn-primary btn-full">Login</button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" style="color: #3498db; text-decoration: none; font-size: 14px;">← Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>