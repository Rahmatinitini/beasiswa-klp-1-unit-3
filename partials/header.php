<?php
/**
 * Membutuhkan variabel:
 * $judul       -> judul <title> & <h1> halaman
 * $deskripsi   -> sub judul di bawah h1
 * $activePage  -> nama file aktif untuk highlight menu (mis. 'beasiswa.php')
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($judul) ?> - Sistem Beasiswa Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="page-title">
                <h1><?= htmlspecialchars($judul) ?></h1>
                <p><?= htmlspecialchars($deskripsi) ?></p>
            </div>
            <div class="user-badge">
                <span class="avatar"><?= strtoupper(substr(user_nama(), 0, 1)) ?></span>
                <span><?= htmlspecialchars(user_nama()) ?> &middot; <?= htmlspecialchars(label_role(user_role())) ?></span>
            </div>
        </div>

        <?php $flash = ambil_flash(); if ($flash): ?>
            <div class="alert-custom <?= htmlspecialchars($flash['tipe']) ?>">
                <?= htmlspecialchars($flash['pesan']) ?>
            </div>
        <?php endif; ?>
