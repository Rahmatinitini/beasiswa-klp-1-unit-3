<?php
require_once __DIR__ . '/config/auth.php';
wajib_login();

$judul      = 'Rekap Penerima Beasiswa';
$deskripsi  = 'Laporan penerima berdasarkan program beasiswa, prodi, dan status seleksi.';
$activePage = 'rekap.php';

$pendaftarValid = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM pendaftar_beasiswa WHERE status_pendaftaran='Valid'"))['c'];
$totalLulus     = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM seleksi WHERE status_seleksi='Lulus'"))['c'];
$totalCadangan  = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM seleksi WHERE status_seleksi='Cadangan'"))['c'];
$totalTidakLulus= (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM seleksi WHERE status_seleksi='Tidak Lulus'"))['c'];

$daftarBeasiswaDropdown = mysqli_query($koneksi, "SELECT id_beasiswa, nama_beasiswa FROM beasiswa ORDER BY nama_beasiswa");
$prodiOptions = ['Pendidikan Teknologi Informasi', 'Pendidikan Matematika', 'Pendidikan Bahasa Inggris', 'Teknologi Informasi', 'Sistem Informasi'];
$statusOptions = ['Lulus', 'Cadangan', 'Tidak Lulus', 'Diproses'];

$filterBeasiswa = (int) ($_GET['beasiswa'] ?? 0);
$filterProdi    = trim($_GET['prodi'] ?? '');
$filterStatus   = trim($_GET['status'] ?? 'Lulus');

$whereProdi = [];
$paramsProdi = [];
$typesProdi = '';

if ($filterBeasiswa > 0) {
    $whereProdi[] = 'p.id_beasiswa = ?';
    $paramsProdi[] = $filterBeasiswa;
    $typesProdi .= 'i';
}
if ($filterProdi !== '' && $filterProdi !== 'Semua Prodi') {
    $whereProdi[] = 'p.prodi = ?';
    $paramsProdi[] = $filterProdi;
    $typesProdi .= 's';
}

$sqlProdi = "SELECT p.prodi,
                COUNT(DISTINCT p.id_pendaftar) AS jumlah_pendaftar,
                SUM(CASE WHEN s.status_seleksi='Lulus' THEN 1 ELSE 0 END) AS lulus,
                SUM(CASE WHEN s.status_seleksi='Cadangan' THEN 1 ELSE 0 END) AS cadangan,
                SUM(CASE WHEN s.status_seleksi='Tidak Lulus' THEN 1 ELSE 0 END) AS tidak_lulus
             FROM pendaftar_beasiswa p
             LEFT JOIN seleksi s ON s.id_pendaftar = p.id_pendaftar";
if ($whereProdi) {
    $sqlProdi .= ' WHERE ' . implode(' AND ', $whereProdi);
}
$sqlProdi .= ' GROUP BY p.prodi ORDER BY jumlah_pendaftar DESC';

$stmt = mysqli_prepare($koneksi, $sqlProdi);
if ($paramsProdi) {
    mysqli_stmt_bind_param($stmt, $typesProdi, ...$paramsProdi);
}
mysqli_stmt_execute($stmt);
$rekapProdi = mysqli_stmt_get_result($stmt);

$wherePenerima = [];
$paramsPenerima = [];
$typesPenerima = '';

if ($filterBeasiswa > 0) {
    $wherePenerima[] = 'p.id_beasiswa = ?';
    $paramsPenerima[] = $filterBeasiswa;
    $typesPenerima .= 'i';
}
if ($filterProdi !== '' && $filterProdi !== 'Semua Prodi') {
    $wherePenerima[] = 'p.prodi = ?';
    $paramsPenerima[] = $filterProdi;
    $typesPenerima .= 's';
}
if ($filterStatus !== '' && $filterStatus !== 'Semua Status') {
    $wherePenerima[] = 's.status_seleksi = ?';
    $paramsPenerima[] = $filterStatus;
    $typesPenerima .= 's';
}

$sqlPenerima = "SELECT p.nim, p.nama_mahasiswa, p.prodi, b.nama_beasiswa, s.nilai_akhir, s.status_seleksi
                FROM seleksi s
                JOIN pendaftar_beasiswa p ON p.id_pendaftar = s.id_pendaftar
                LEFT JOIN beasiswa b ON b.id_beasiswa = p.id_beasiswa";
if ($wherePenerima) {
    $sqlPenerima .= ' WHERE ' . implode(' AND ', $wherePenerima);
}
$sqlPenerima .= ' ORDER BY s.nilai_akhir DESC';

$stmt = mysqli_prepare($koneksi, $sqlPenerima);
if ($paramsPenerima) {
    mysqli_stmt_bind_param($stmt, $typesPenerima, ...$paramsPenerima);
}
mysqli_stmt_execute($stmt);
$daftarPenerima = mysqli_stmt_get_result($stmt);

include __DIR__ . '/partials/header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-ring" style="--pct: <?= min(100, $pendaftarValid) ?>"></div>
            <div class="stat-body">
                <div class="stat-label">Pendaftar Valid</div>
                <div class="stat-value"><?= $pendaftarValid ?></div>
                <span class="badge badge-soft-primary">Layak seleksi</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-ring" style="--pct: <?= min(100, $totalLulus) ?>"></div>
            <div class="stat-body">
                <div class="stat-label">Lulus</div>
                <div class="stat-value"><?= $totalLulus ?></div>
                <span class="badge badge-soft-success">Penerima</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-ring" style="--pct: <?= min(100, $totalCadangan) ?>"></div>
            <div class="stat-body">
                <div class="stat-label">Cadangan</div>
                <div class="stat-value"><?= $totalCadangan ?></div>
                <span class="badge badge-soft-warning">Menunggu</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-ring" style="--pct: <?= min(100, $totalTidakLulus) ?>"></div>
            <div class="stat-body">
                <div class="stat-label">Tidak Lulus</div>
                <div class="stat-value"><?= $totalTidakLulus ?></div>
                <span class="badge badge-soft-danger">Ditolak</span>
            </div>
        </div>
    </div>
</div>

<div class="section-card">
    <div class="section-title">Filter Rekap</div>

    <form method="get" action="rekap.php" class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Program Beasiswa</label>
            <select name="beasiswa" class="form-select">
                <option value="0">Semua Beasiswa</option>
                <?php while ($b = mysqli_fetch_assoc($daftarBeasiswaDropdown)): ?>
                    <option value="<?= (int)$b['id_beasiswa'] ?>" <?= $filterBeasiswa === (int)$b['id_beasiswa'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['nama_beasiswa']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Program Studi</label>
            <select name="prodi" class="form-select">
                <option>Semua Prodi</option>
                <?php foreach ($prodiOptions as $opt): ?>
                    <option <?= $filterProdi === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Status Seleksi</label>
            <select name="status" class="form-select">
                <option>Semua Status</option>
                <?php foreach ($statusOptions as $opt): ?>
                    <option <?= $filterStatus === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary-custom">Terapkan Filter</button>
            <a href="rekap.php" class="btn btn-light border rounded-3 ms-2">Reset Filter</a>
        </div>
    </form>
</div>

<div class="section-card">
    <div class="section-title">Rekap Berdasarkan Program Studi</div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Program Studi</th>
                    <th>Jumlah Pendaftar</th>
                    <th>Lulus</th>
                    <th>Cadangan</th>
                    <th>Tidak Lulus</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($rekapProdi) === 0): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data untuk filter ini.</td></tr>
                <?php else: $no = 1; while ($row = mysqli_fetch_assoc($rekapProdi)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['prodi']) ?></td>
                        <td class="num"><?= (int)$row['jumlah_pendaftar'] ?></td>
                        <td><span class="badge badge-soft-success"><?= (int)$row['lulus'] ?></span></td>
                        <td><span class="badge badge-soft-warning"><?= (int)$row['cadangan'] ?></span></td>
                        <td><span class="badge badge-soft-danger"><?= (int)$row['tidak_lulus'] ?></span></td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="section-card">
    <div class="section-title">Daftar Penerima Beasiswa</div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Prodi</th>
                    <th>Beasiswa</th>
                    <th>Nilai Akhir</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($daftarPenerima) === 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data yang cocok dengan filter ini.</td></tr>
                <?php else: $no = 1; while ($row = mysqli_fetch_assoc($daftarPenerima)):
                    $badgeStatus = $row['status_seleksi'] === 'Lulus' ? 'badge-soft-success'
                                 : ($row['status_seleksi'] === 'Tidak Lulus' ? 'badge-soft-danger'
                                 : ($row['status_seleksi'] === 'Cadangan' ? 'badge-soft-gold' : 'badge-soft-warning'));
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="mono"><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                        <td><?= htmlspecialchars($row['prodi']) ?></td>
                        <td><?= htmlspecialchars($row['nama_beasiswa'] ?? '-') ?></td>
                        <td class="num"><?= number_format((float)$row['nilai_akhir'], 2) ?></td>
                        <td><span class="badge <?= $badgeStatus ?>"><?= htmlspecialchars($row['status_seleksi']) ?></span></td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>