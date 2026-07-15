<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_beasiswa_mahasiswa';

mysqli_report(MYSQLI_REPORT_OFF);
$koneksi = @mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$koneksi) {
    http_response_code(500);
    die('
    <div style="font-family: Arial, sans-serif; max-width:640px; margin:60px auto; padding:24px 28px;
                border:1px solid #e4b8b3; background:#fdf1f0; border-radius:10px; color:#7a2b23;">
        <h2 style="margin-top:0;">Koneksi Database Gagal</h2>
        <p>Aplikasi tidak dapat terhubung ke database <code>' . htmlspecialchars($DB_NAME) . '</code>.</p>
        <p><b>Detail:</b> ' . htmlspecialchars(mysqli_connect_error()) . '</p>
        <ol>
            <li>Pastikan service MySQL/MariaDB pada XAMPP atau Laragon sudah berjalan.</li>
            <li>Pastikan database <code>' . htmlspecialchars($DB_NAME) . '</code> sudah diimport melalui phpMyAdmin
                dari file <code>db_beasiswa_mahasiswa.sql</code>.</li>
            <li>Periksa kembali variabel koneksi di <code>config/koneksi.php</code>.</li>
        </ol>
    </div>');
}

mysqli_set_charset($koneksi, 'utf8mb4');
