<?php
$aktif = $activePage ?? basename($_SERVER['PHP_SELF']);
function kelas_aktif($nama, $aktif){
    return $nama === $aktif ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">SB</div>
        <div class="brand-title">Sistem Beasiswa<br>Mahasiswa</div>
    </div>

    <a href="dashboard.php" class="<?= kelas_aktif('dashboard.php', $aktif) ?>">Dashboard</a>

    <details open>
        <summary>Master Data</summary>
        <div class="submenu">
            <a href="beasiswa.php" class="<?= kelas_aktif('beasiswa.php', $aktif) ?>">Data Beasiswa</a>
            <a href="pendaftar.php" class="<?= kelas_aktif('pendaftar.php', $aktif) ?>">Data Pendaftar</a>
        </div>
    </details>

    <details <?= $aktif === 'seleksi.php' ? 'open' : '' ?>>
        <summary>Seleksi Beasiswa</summary>
        <div class="submenu">
            <a href="seleksi.php" class="<?= kelas_aktif('seleksi.php', $aktif) ?>">Status Seleksi</a>
        </div>
    </details>

    <details <?= $aktif === 'rekap.php' ? 'open' : '' ?>>
        <summary>Laporan</summary>
        <div class="submenu">
            <a href="rekap.php" class="<?= kelas_aktif('rekap.php', $aktif) ?>">Rekap Penerima</a>
        </div>
    </details>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?= strtoupper(substr(user_nama(), 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <div class="nama"><?= htmlspecialchars(user_nama()) ?></div>
                <div class="peran"><?= htmlspecialchars(label_role(user_role())) ?></div>
            </div>
        </div>
        <a href="logout.php" class="logout-link">Keluar</a>
    </div>
</aside>
