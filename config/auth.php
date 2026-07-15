<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/koneksi.php';

function wajib_login()
{
    if (empty($_SESSION['id_user'])) {
        header('Location: index.php');
        exit;
    }
}

function user_nama()
{
    return $_SESSION['nama_lengkap'] ?? 'Pengguna';
}

function user_role()
{
    return $_SESSION['role'] ?? 'operator';
}

function label_role($role)
{
    $map = [
        'admin'    => 'Administrator',
        'operator' => 'Operator',
        'pimpinan' => 'Pimpinan',
    ];
    return $map[$role] ?? ucfirst($role);
}

function flash_dan_redirect($tujuan, $tipe, $pesan)
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
    header('Location: ' . $tujuan);
    exit;
}

function ambil_flash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
