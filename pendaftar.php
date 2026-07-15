<?php
require_once __DIR__ . '/config/auth.php';
wajib_login();

$judul      = 'Input Pendaftar Beasiswa';
$deskripsi  = 'Validasi data pendaftar berdasarkan NIM, IPK, prodi, dan kelengkapan berkas.';
$activePage = 'pendaftar.php';

$berkasOptions = ['Ada', 'Tidak Ada'];
$statusOptions = ['Menunggu', 'Valid', 'Tidak Valid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_simpan'])) {
    $id            = $_POST['id_pendaftar'] ?? '';
    $idBeasiswa    = (int) ($_POST['id_beasiswa'] ?? 0);
    $nim           = trim($_POST['nim'] ?? '');
    $nama          = trim($_POST['nama_mahasiswa'] ?? '');
    $prodi         = trim($_POST['prodi'] ?? '');
    $semester      = (int) ($_POST['semester'] ?? 0);
    $ipk           = (float) ($_POST['ipk'] ?? 0);
    $noHp          = trim($_POST['no_hp'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $tglDaftar     = $_POST['tanggal_daftar'] ?? '';
    $alamat        = trim($_POST['alamat'] ?? '');
    $penghasilan   = $_POST['penghasilan_orang_tua'] !== '' ? (float) $_POST['penghasilan_orang_tua'] : null;
    $ktm           = $_POST['berkas_ktm'] ?? 'Tidak Ada';
    $khs           = $_POST['berkas_khs'] ?? 'Tidak Ada';
    $suratKet      = $_POST['berkas_surat_keterangan'] ?? 'Tidak Ada';
    $status        = $_POST['status_pendaftaran'] ?? 'Menunggu';

    if ($idBeasiswa < 1 || $nim === '' || $nama === '' || $prodi === '' || $semester < 1
        || $ipk < 0 || $ipk > 4 || $noHp === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $tglDaftar === '') {
        flash_dan_redirect('pendaftar.php', 'danger', 'Periksa kembali data yang diisi, ada kolom yang belum valid.');
    }

    if ($id !== '') {
        $stmt = mysqli_prepare($koneksi, "UPDATE pendaftar_beasiswa SET id_beasiswa=?, nim=?, nama_mahasiswa=?, prodi=?, semester=?,
                                           ipk=?, no_hp=?, email=?, alamat=?, penghasilan_orang_tua=?, berkas_ktm=?, berkas_khs=?,
                                           berkas_surat_keterangan=?, status_pendaftaran=?, tanggal_daftar=? WHERE id_pendaftar=?");
        mysqli_stmt_bind_param($stmt, 'isssidsssdsssssi', $idBeasiswa, $nim, $nama, $prodi, $semester, $ipk, $noHp, $email,
                                $alamat, $penghasilan, $ktm, $khs, $suratKet, $status, $tglDaftar, $id);
        $pesan = 'Data pendaftar berhasil diperbarui.';
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pendaftar_beasiswa
                                           (id_beasiswa, nim, nama_mahasiswa, prodi, semester, ipk, no_hp, email, alamat,
                                            penghasilan_orang_tua, berkas_ktm, berkas_khs, berkas_surat_keterangan, status_pendaftaran, tanggal_daftar)
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isssidsssdsssss', $idBeasiswa, $nim, $nama, $prodi, $semester, $ipk, $noHp, $email,
                                $alamat, $penghasilan, $ktm, $khs, $suratKet, $status, $tglDaftar);
        $pesan = 'Data pendaftar berhasil ditambahkan.';
    }

    if (mysqli_stmt_execute($stmt)) {
        flash_dan_redirect('pendaftar.php', 'success', $pesan);
    } else {
        $errMsg = (mysqli_errno($koneksi) === 1062) ? 'NIM tersebut sudah terdaftar pada program ini.' : mysqli_error($koneksi);
        flash_dan_redirect('pendaftar.php', 'danger', 'Gagal menyimpan data: ' . $errMsg);
    }
}

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM pendaftar_beasiswa WHERE id_pendaftar=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        flash_dan_redirect('pendaftar.php', 'success', 'Data pendaftar berhasil dihapus.');
    } else {
        flash_dan_redirect('pendaftar.php', 'danger', 'Gagal menghapus data (mungkin masih memiliki data seleksi terkait).');
    }
}

$daftarBeasiswaDropdown = mysqli_query($koneksi, "SELECT id_beasiswa, nama_beasiswa FROM beasiswa ORDER BY nama_beasiswa");

$dataEdit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pendaftar_beasiswa WHERE id_pendaftar=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $dataEdit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$filterProdi  = trim($_GET['prodi'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$cari         = trim($_GET['cari'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($filterProdi !== '' && $filterProdi !== 'Semua Prodi') {
    $where[] = 'p.prodi = ?';
    $params[] = $filterProdi;
    $types .= 's';
}
if ($filterStatus !== '' && $filterStatus !== 'Semua Status') {
    $where[] = 'p.status_pendaftaran = ?';
    $params[] = $filterStatus;
    $types .= 's';
}
if ($cari !== '') {
    $where[] = '(p.nim LIKE ? OR p.nama_mahasiswa LIKE ?)';
    $like = '%' . $cari . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$sql = "SELECT p.*, b.nama_beasiswa FROM pendaftar_beasiswa p
        LEFT JOIN beasiswa b ON b.id_beasiswa = p.id_beasiswa";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = mysqli_prepare($koneksi, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$daftarPendaftar = mysqli_stmt_get_result($stmt);

$prodiOptions = ['Pendidikan Teknologi Informasi', 'Pendidikan Matematika', 'Pendidikan Bahasa Inggris', 'Teknologi Informasi', 'Sistem Informasi'];

include __DIR__ . '/partials/header.php';
?>

<div class="section-card">
    <div class="section-title"><?= $dataEdit ? 'Ubah Data Pendaftar' : 'Form Pendaftar' ?></div>

    <form method="post" action="pendaftar.php">
        <input type="hidden" name="aksi_simpan" value="1">
        <?php if ($dataEdit): ?>
            <input type="hidden" name="id_pendaftar" value="<?= (int)$dataEdit['id_pendaftar'] ?>">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Program Beasiswa</label>
                <select name="id_beasiswa" class="form-select" required>
                    <option value="">Pilih beasiswa</option>
                    <?php mysqli_data_seek($daftarBeasiswaDropdown, 0); while ($b = mysqli_fetch_assoc($daftarBeasiswaDropdown)): ?>
                        <option value="<?= (int)$b['id_beasiswa'] ?>" <?= (($dataEdit['id_beasiswa'] ?? null) == $b['id_beasiswa']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nama_beasiswa']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">NIM</label>
                <input type="text" name="nim" class="form-control" placeholder="Contoh: 220212001" minlength="6" maxlength="30"
                       value="<?= htmlspecialchars($dataEdit['nim'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Semester</label>
                <input type="number" name="semester" class="form-control" min="1" max="14" placeholder="4"
                       value="<?= htmlspecialchars($dataEdit['semester'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Mahasiswa</label>
                <input type="text" name="nama_mahasiswa" class="form-control" placeholder="Nama lengkap mahasiswa"
                       value="<?= htmlspecialchars($dataEdit['nama_mahasiswa'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Program Studi</label>
                <select name="prodi" class="form-select" required>
                    <option value="">Pilih prodi</option>
                    <?php foreach ($prodiOptions as $opt): ?>
                        <option <?= (($dataEdit['prodi'] ?? '') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">IPK</label>
                <input type="number" name="ipk" class="form-control" min="0" max="4" step="0.01" placeholder="3.75"
                       value="<?= htmlspecialchars($dataEdit['ipk'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">No HP</label>
                <input type="tel" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx"
                       value="<?= htmlspecialchars($dataEdit['no_hp'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="mahasiswa@email.com"
                       value="<?= htmlspecialchars($dataEdit['email'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Daftar</label>
                <input type="date" name="tanggal_daftar" class="form-control"
                       value="<?= htmlspecialchars($dataEdit['tanggal_daftar'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Penghasilan Orang Tua (Rp)</label>
                <input type="number" name="penghasilan_orang_tua" class="form-control" min="0" step="1000" placeholder="Contoh: 2500000"
                       value="<?= htmlspecialchars($dataEdit['penghasilan_orang_tua'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Berkas KTM</label>
                <select name="berkas_ktm" class="form-select" required>
                    <?php foreach ($berkasOptions as $opt): ?>
                        <option <?= (($dataEdit['berkas_ktm'] ?? 'Ada') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Berkas KHS</label>
                <select name="berkas_khs" class="form-select" required>
                    <?php foreach ($berkasOptions as $opt): ?>
                        <option <?= (($dataEdit['berkas_khs'] ?? 'Ada') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Surat Keterangan</label>
                <select name="berkas_surat_keterangan" class="form-select" required>
                    <?php foreach ($berkasOptions as $opt): ?>
                        <option <?= (($dataEdit['berkas_surat_keterangan'] ?? 'Ada') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat mahasiswa"><?= htmlspecialchars($dataEdit['alamat'] ?? '') ?></textarea>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Status Pendaftaran</label>
                <select name="status_pendaftaran" class="form-select" required>
                    <?php foreach ($statusOptions as $opt): ?>
                        <option <?= (($dataEdit['status_pendaftaran'] ?? 'Menunggu') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary-custom"><?= $dataEdit ? 'Simpan Perubahan' : 'Simpan Pendaftar' ?></button>
            <?php if ($dataEdit): ?>
                <a href="pendaftar.php" class="btn btn-light border rounded-3 ms-2">Batal</a>
            <?php else: ?>
                <button type="reset" class="btn btn-light border rounded-3 ms-2">Reset</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="section-card">
    <div class="section-title">Filter Data Pendaftar</div>

    <form method="get" action="pendaftar.php" class="row g-3 mb-3">
        <div class="col-md-4">
            <select name="prodi" class="form-select">
                <option>Semua Prodi</option>
                <?php foreach ($prodiOptions as $opt): ?>
                    <option <?= $filterProdi === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <select name="status" class="form-select">
                <option>Semua Status</option>
                <?php foreach ($statusOptions as $opt): ?>
                    <option <?= $filterStatus === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4 d-flex gap-2">
            <input type="search" name="cari" class="form-control" placeholder="Cari NIM atau nama" value="<?= htmlspecialchars($cari) ?>">
            <button type="submit" class="btn btn-outline-gold px-3">Cari</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Prodi</th>
                    <th>Beasiswa</th>
                    <th>IPK</th>
                    <th>Berkas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($daftarPendaftar) === 0): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data pendaftar yang cocok.</td></tr>
                <?php else: $no = 1; while ($row = mysqli_fetch_assoc($daftarPendaftar)):
                    $lengkap = ($row['berkas_ktm'] === 'Ada' && $row['berkas_khs'] === 'Ada' && $row['berkas_surat_keterangan'] === 'Ada');
                    $badgeStatus = $row['status_pendaftaran'] === 'Valid' ? 'badge-soft-success'
                                 : ($row['status_pendaftaran'] === 'Tidak Valid' ? 'badge-soft-danger' : 'badge-soft-warning');
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="mono"><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                        <td><?= htmlspecialchars($row['prodi']) ?></td>
                        <td><?= htmlspecialchars($row['nama_beasiswa'] ?? '-') ?></td>
                        <td class="num"><?= number_format((float)$row['ipk'], 2) ?></td>
                        <td>
                            <span class="badge <?= $lengkap ? 'badge-soft-success' : 'badge-soft-warning' ?>">
                                <?= $lengkap ? 'Lengkap' : 'Belum lengkap' ?>
                            </span>
                        </td>
                        <td><span class="badge <?= $badgeStatus ?>"><?= htmlspecialchars($row['status_pendaftaran']) ?></span></td>
                        <td>
                            <a href="pendaftar.php?edit=<?= (int)$row['id_pendaftar'] ?>" class="btn btn-warning action-btn">Edit</a>
                            <a href="pendaftar.php?hapus=<?= (int)$row['id_pendaftar'] ?>" class="btn btn-danger action-btn"
                               onclick="return confirm('Hapus data pendaftar ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>