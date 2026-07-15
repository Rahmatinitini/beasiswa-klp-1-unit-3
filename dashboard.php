<?php
require_once __DIR__ . '/config/auth.php';
wajib_login();

$judul      = 'Dashboard Utama';
$deskripsi  = 'Ringkasan data beasiswa, pendaftar, proses seleksi, dan penerima.';
$activePage = 'dashboard.php';

/* ---------- Statistik ringkas ---------- */
$totalBeasiswa   = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM beasiswa"))['c'];
$beasiswaDibuka  = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM beasiswa WHERE status_beasiswa='Dibuka'"))['c'];

$totalPendaftar  = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM pendaftar_beasiswa"))['c'];
$pendaftarValid  = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM pendaftar_beasiswa WHERE status_pendaftaran='Valid'"))['c'];

$totalLulus      = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM seleksi WHERE status_seleksi='Lulus'"))['c'];
$totalTidakLulus = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM seleksi WHERE status_seleksi='Tidak Lulus'"))['c'];
$totalDiseleksi  = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM seleksi"))['c'];

$pctBeasiswaAktif = $totalBeasiswa > 0 ? round(($beasiswaDibuka / $totalBeasiswa) * 100) : 0;
$pctPendaftarValid = $totalPendaftar > 0 ? round(($pendaftarValid / $totalPendaftar) * 100) : 0;
$pctLulus = $totalDiseleksi > 0 ? round(($totalLulus / $totalDiseleksi) * 100) : 0;
$pctTidakLulus = $totalDiseleksi > 0 ? round(($totalTidakLulus / $totalDiseleksi) * 100) : 0;

/* ---------- Daftar beasiswa terbaru ---------- */
$beasiswaTerbaru = mysqli_query($koneksi, "SELECT * FROM beasiswa ORDER BY created_at DESC LIMIT 5");

/* ---------- Rekap singkat penerima per prodi ---------- */
$rekapProdi = mysqli_query($koneksi, "
    SELECT p.prodi, COUNT(*) AS jumlah
    FROM seleksi s
    JOIN pendaftar_beasiswa p ON p.id_pendaftar = s.id_pendaftar
    WHERE s.status_seleksi = 'Lulus'
    GROUP BY p.prodi
    ORDER BY jumlah DESC
    LIMIT 6
");

include __DIR__ . '/partials/header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-ring" style="--pct: <?= $pctBeasiswaAktif ?>"></div>
            <div class="stat-body">
                <div class="stat-label">Total Beasiswa</div>
                <div class="stat-value"><?= $totalBeasiswa ?></div>
                <span class="badge badge-soft-primary"><?= $beasiswaDibuka ?> Dibuka</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-ring" style="--pct: <?= $pctPendaftarValid ?>"></div>
            <div class="stat-body">
                <div class="stat-label">Total Pendaftar</div>
                <div class="stat-value"><?= $totalPendaftar ?></div>
                <span class="badge badge-soft-warning"><?= $pendaftarValid ?> Valid</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-ring" style="--pct: <?= $pctLulus ?>"></div>
            <div class="stat-body">
                <div class="stat-label">Lulus Seleksi</div>
                <div class="stat-value"><?= $totalLulus ?></div>
                <span class="badge badge-soft-success">Penerima</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-ring" style="--pct: <?= $pctTidakLulus ?>"></div>
            <div class="stat-body">
                <div class="stat-label">Tidak Lulus</div>
                <div class="stat-value"><?= $totalTidakLulus ?></div>
                <span class="badge badge-soft-danger">Ditolak</span>
            </div>
        </div>
    </div>
</div>

<div class="section-card">
    <div class="section-title">Daftar Beasiswa Terbaru</div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Beasiswa</th>
                    <th>Jenis</th>
                    <th>Kuota</th>
                    <th>Periode</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($beasiswaTerbaru) === 0): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data beasiswa.</td></tr>
                <?php else: $no = 1; while ($row = mysqli_fetch_assoc($beasiswaTerbaru)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_beasiswa']) ?></td>
                        <td><?= htmlspecialchars($row['jenis_beasiswa']) ?></td>
                        <td class="num"><?= (int)$row['kuota'] ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal_buka'])) ?> &ndash; <?= date('d M Y', strtotime($row['tanggal_tutup'])) ?></td>
                        <td>
                            <?php
                            $badgeKelas = $row['status_beasiswa'] === 'Dibuka' ? 'badge-soft-success'
                                        : ($row['status_beasiswa'] === 'Selesai' ? 'badge-soft-primary' : 'badge-soft-danger');
                            ?>
                            <span class="badge <?= $badgeKelas ?>"><?= htmlspecialchars($row['status_beasiswa']) ?></span>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="section-card">
    <div class="section-title">Rekap Singkat Penerima Berdasarkan Prodi</div>

    <div class="row g-3">
        <?php if (mysqli_num_rows($rekapProdi) === 0): ?>
            <div class="col-12">
                <div class="empty-state">
                    <div class="glyph">&mdash;</div>
                    Belum ada mahasiswa yang dinyatakan lulus seleksi.
                </div>
            </div>
        <?php else: while ($row = mysqli_fetch_assoc($rekapProdi)): ?>
            <div class="col-md-4">
                <div class="p-3 border rounded-4">
                    <div class="fw-bold"><?= htmlspecialchars($row['prodi']) ?></div>
                    <div class="text-muted"><?= (int)$row['jumlah'] ?> penerima</div>
                </div>
            </div>
        <?php endwhile; endif; ?>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
