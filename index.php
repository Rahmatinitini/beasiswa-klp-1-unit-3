<?php
require_once __DIR__ . '/config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!empty($_SESSION['id_user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id_user, nama_lengkap, username, password_hash, role, status_akun
                                           FROM user_login WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $user  = mysqli_fetch_assoc($hasil);

        if (!$user) {
            $error = 'Username tidak ditemukan.';
        } elseif ($user['status_akun'] !== 'aktif') {
            $error = 'Akun ini sedang dinonaktifkan. Hubungi administrator.';
        } else {
            $hashValid = password_verify($password, $user['password_hash']);

            
            $legacyMatch = !$hashValid && hash_equals((string) $user['password_hash'], $password);

            if (!$hashValid && !$legacyMatch) {
                $error = 'Password yang Anda masukkan salah.';
            } else {
                if ($legacyMatch) {
                    $newHash = password_hash($password, PASSWORD_BCRYPT);
                    $upd = mysqli_prepare($koneksi, "UPDATE user_login SET password_hash=? WHERE id_user=?");
                    mysqli_stmt_bind_param($upd, 'si', $newHash, $user['id_user']);
                    mysqli_stmt_execute($upd);
                }

                session_regenerate_id(true);
                $_SESSION['id_user']      = $user['id_user'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['role']         = $user['role'];
                header('Location: dashboard.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Beasiswa Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-logo">SB</div>

        <h3 class="fw-bold mb-1">Login Admin</h3>
        <p class="text-muted mb-4">Sistem Informasi Data Beasiswa Mahasiswa</p>

        <?php if ($error): ?>
            <div class="alert-custom danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="index.php" method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ingat">
                    <label class="form-check-label" for="ingat">Ingat saya</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom w-100">
                Masuk Dashboard
            </button>
        </form>

        <div class="login-hint">
            Akun demo bawaan &mdash; Username: <b>admin</b> &middot; Password: <b>admin123</b>
        </div>
    </div>

</body>
</html>
