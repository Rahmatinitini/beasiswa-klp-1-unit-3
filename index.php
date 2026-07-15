<?php
// Koneksi DB dimatikan sementara supaya login bypass tidak terganggu error database
// require_once __DIR__ . '/config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau sudah login, langsung ke dashboard
if (!empty($_SESSION['id_user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menangkap inputan dari form
    $nama = trim($_POST['username'] ?? ''); // Kolom atas dipakai untuk Nama
    $nim = trim($_POST['password'] ?? '');  // Kolom bawah dipakai untuk NIM

    if ($nama === '' || $nim === '') {
        $error = 'Nama dan NIM wajib diisi.';
    } else {
        // --- BYPASS LOGIC ---
        // Tanpa cek database, langsung kita buatkan sesi loginnya
        session_regenerate_id(true);
        
        $_SESSION['id_user']      = rand(1, 999); // Beri ID acak
        $_SESSION['nama_lengkap'] = $nama;        // Nama sesuai yang diketik di kolom pertama
        $_SESSION['username']     = $nim;         // Username diset menggunakan NIM yang diketik
        $_SESSION['role']         = 'admin';      // Langsung beri akses admin
        
        // Arahkan ke dashboard
        header('Location: dashboard.php');
        exit;
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
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-logo">SB</div>

        <h3 class="fw-bold mb-1">Login Mahasiswa</h3>
        <p class="text-muted mb-4">Sistem Informasi Data Beasiswa Mahasiswa</p>

        <?php if ($error): ?>
            <div class="alert-custom danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="index.php" method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan nama Anda"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">NIM (Sebagai Password)</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan NIM Anda" required>
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
    </div>
</body>
</html>
