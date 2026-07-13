<?php
require_once __DIR__ . '/auth.php';
wajib_login();

$judul      = 'Status Seleksi';
$deskripsi  = 'Kelola tahapan seleksi administrasi, wawancara, final, dan status kelulusan.';
$activePage = 'seleksi.php';

$tahapOptions  = ['Administrasi', 'Wawancara', 'Final'];
$statusOptions = ['Diproses', 'Lulus', 'Tidak Lulus', 'Cadangan'];

/* ---------------------------------------------------------
   Simpan (tambah / ubah)
--------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_simpan'])) {
    $id            = $_POST['id_seleksi'] ?? '';
    $idPendaftar   = (int) ($_POST['id_pendaftar'] ?? 0);
    $tahap         = $_POST['tahap_seleksi'] ?? 'Administrasi';
    $tglSeleksi    = $_POST['tanggal_seleksi'] ?? '';
    $nilaiAdmin    = (float) ($_POST['nilai_administrasi'] ?? 0);
    $nilaiWawancara = (float) ($_POST['nilai_wawancara'] ?? 0);
    $nilaiAkhir    = (float) ($_POST['nilai_akhir'] ?? 0);
    $status        = $_POST['status_seleksi'] ?? 'Diproses';
    $catatan       = trim($_POST['catatan'] ?? '');

    if ($idPendaftar < 1 || !in_array($tahap, $tahapOptions) || $tglSeleksi === '' || !in_array($status, $statusOptions)) {
        flash_dan_redirect('seleksi.php', 'danger', 'Periksa kembali data yang diisi, ada kolom yang belum valid.');
    }

    if ($id !== '') {
        $stmt = mysqli_prepare($koneksi, "UPDATE seleksi SET id_pendaftar=?, tahap_seleksi=?, nilai_administrasi=?, nilai_wawancara=?,
                                           nilai_akhir=?, status_seleksi=?, catatan=?, tanggal_seleksi=? WHERE id_seleksi=?");
        mysqli_stmt_bind_param($stmt, 'isdddsssi', $idPendaftar, $tahap, $nilaiAdmin, $nilaiWawancara, $nilaiAkhir, $status, $catatan, $tglSeleksi, $id);
        $pesan = 'Data seleksi berhasil diperbarui.';
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO seleksi
                                           (id_pendaftar, tahap_seleksi, nilai_administrasi, nilai_wawancara, nilai_akhir, status_seleksi, catatan, tanggal_seleksi)
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isdddsss', $idPendaftar, $tahap, $nilaiAdmin, $nilaiWawancara, $nilaiAkhir, $status, $catatan, $tglSeleksi);
        $pesan = 'Data seleksi berhasil ditambahkan.';
    }

    if (mysqli_stmt_execute($stmt)) {
        flash_dan_redirect('seleksi.php', 'success', $pesan);
    } else {
        flash_dan_redirect('seleksi.php', 'danger', 'Gagal menyimpan data: ' . mysqli_error($koneksi));
    }
}

/* ---------------------------------------------------------
   Hapus
--------------------------------------------------------- */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM seleksi WHERE id_seleksi=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        flash_dan_redirect('seleksi.php', 'success', 'Data seleksi berhasil dihapus.');
    } else {
        flash_dan_redirect('seleksi.php', 'danger', 'Gagal menghapus data seleksi.');
    }
}

/* ---------------------------------------------------------
   Dropdown pendaftar & data edit
--------------------------------------------------------- */
$daftarPendaftarDropdown = mysqli_query($koneksi, "SELECT id_pendaftar, nim, nama_mahasiswa FROM pendaftar_beasiswa ORDER BY nama_mahasiswa");

$dataEdit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM seleksi WHERE id_seleksi=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $dataEdit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

/* ---------------------------------------------------------
   Filter & daftar
--------------------------------------------------------- */
$filterStatus = trim($_GET['status'] ?? '');
$cari         = trim($_GET['cari'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($filterStatus !== '' && $filterStatus !== 'Semua Status') {
    $where[] = 's.status_seleksi = ?';
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

$sql = "SELECT s.*, p.nim, p.nama_mahasiswa, b.nama_beasiswa
        FROM seleksi s
        JOIN pendaftar_beasiswa p ON p.id_pendaftar = s.id_pendaftar
        LEFT JOIN beasiswa b ON b.id_beasiswa = p.id_beasiswa";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY s.created_at DESC';

$stmt = mysqli_prepare($koneksi, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$daftarSeleksi = mysqli_stmt_get_result($stmt);

include __DIR__ . '/header.php';
?>

<div class="section-card">
    <div class="section-title"><?= $dataEdit ? 'Ubah Data Seleksi' : 'Form Input Seleksi' ?></div>

    <form method="post" action="seleksi.php" id="formSeleksi">
        <input type="hidden" name="aksi_simpan" value="1">
        <?php if ($dataEdit): ?>
            <input type="hidden" name="id_seleksi" value="<?= (int)$dataEdit['id_seleksi'] ?>">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Pendaftar</label>
                <select name="id_pendaftar" class="form-select" required>
                    <option value="">Pilih pendaftar</option>
                    <?php mysqli_data_seek($daftarPendaftarDropdown, 0); while ($p = mysqli_fetch_assoc($daftarPendaftarDropdown)): ?>
                        <option value="<?= (int)$p['id_pendaftar'] ?>" <?= (($dataEdit['id_pendaftar'] ?? null) == $p['id_pendaftar']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nim']) ?> - <?= htmlspecialchars($p['nama_mahasiswa']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Tahap Seleksi</label>
                <select name="tahap_seleksi" class="form-select" required>
                    <?php foreach ($tahapOptions as $opt): ?>
                        <option <?= (($dataEdit['tahap_seleksi'] ?? 'Administrasi') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Seleksi</label>
                <input type="date" name="tanggal_seleksi" class="form-control"
                       value="<?= htmlspecialchars($dataEdit['tanggal_seleksi'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Nilai Administrasi</label>
                <input type="number" name="nilai_administrasi" id="nilaiAdmin" class="form-control" min="0" max="100" step="0.01" placeholder="85.00"
                       value="<?= htmlspecialchars($dataEdit['nilai_administrasi'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Nilai Wawancara</label>
                <input type="number" name="nilai_wawancara" id="nilaiWawancara" class="form-control" min="0" max="100" step="0.01" placeholder="88.00"
                       value="<?= htmlspecialchars($dataEdit['nilai_wawancara'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Nilai Akhir</label>
                <input type="number" name="nilai_akhir" id="nilaiAkhir" class="form-control" min="0" max="100" step="0.01" placeholder="86.50"
                       value="<?= htmlspecialchars($dataEdit['nilai_akhir'] ?? '') ?>">
                <div class="form-text">Otomatis dihitung rata-rata, namun tetap bisa diubah manual.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Status Seleksi</label>
                <select name="status_seleksi" class="form-select" required>
                    <?php foreach ($statusOptions as $opt): ?>
                        <option <?= (($dataEdit['status_seleksi'] ?? 'Diproses') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Catatan Seleksi</label>
                <input type="text" name="catatan" class="form-control" placeholder="Contoh: Berkas lengkap dan memenuhi kriteria"
                       value="<?= htmlspecialchars($dataEdit['catatan'] ?? '') ?>">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary-custom"><?= $dataEdit ? 'Simpan Perubahan' : 'Simpan Seleksi' ?></button>
            <?php if ($dataEdit): ?>
                <a href="seleksi.php" class="btn btn-light border rounded-3 ms-2">Batal</a>
            <?php else: ?>
                <button type="reset" class="btn btn-light border rounded-3 ms-2">Reset</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="section-card">
    <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">
        <div class="section-title mb-0">Daftar Status Seleksi</div>

        <form method="get" action="seleksi.php" class="d-flex gap-2 flex-wrap">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option>Semua Status</option>
                <?php foreach ($statusOptions as $opt): ?>
                    <option <?= $filterStatus === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
            <input type="search" name="cari" class="form-control" placeholder="Cari nama atau NIM" value="<?= htmlspecialchars($cari) ?>">
            <button type="submit" class="btn btn-outline-gold px-3">Cari</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Beasiswa</th>
                    <th>Tahap</th>
                    <th>Nilai Akhir</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($daftarSeleksi) === 0): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data seleksi yang cocok.</td></tr>
                <?php else: $no = 1; while ($row = mysqli_fetch_assoc($daftarSeleksi)):
                    $badgeStatus = $row['status_seleksi'] === 'Lulus' ? 'badge-soft-success'
                                 : ($row['status_seleksi'] === 'Tidak Lulus' ? 'badge-soft-danger'
                                 : ($row['status_seleksi'] === 'Cadangan' ? 'badge-soft-gold' : 'badge-soft-warning'));
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="mono"><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                        <td><?= htmlspecialchars($row['nama_beasiswa'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['tahap_seleksi']) ?></td>
                        <td class="num"><?= number_format((float)$row['nilai_akhir'], 2) ?></td>
                        <td><span class="badge <?= $badgeStatus ?>"><?= htmlspecialchars($row['status_seleksi']) ?></span></td>
                        <td>
                            <a href="seleksi.php?edit=<?= (int)$row['id_seleksi'] ?>" class="btn btn-warning action-btn">Edit</a>
                            <a href="seleksi.php?hapus=<?= (int)$row['id_seleksi'] ?>" class="btn btn-danger action-btn"
                               onclick="return confirm('Hapus data seleksi ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Hitung otomatis nilai akhir sebagai rata-rata administrasi & wawancara
    const admin = document.getElementById('nilaiAdmin');
    const wawancara = document.getElementById('nilaiWawancara');
    const akhir = document.getElementById('nilaiAkhir');

    function hitungNilaiAkhir() {
        const a = parseFloat(admin.value) || 0;
        const w = parseFloat(wawancara.value) || 0;
        if (admin.value !== '' || wawancara.value !== '') {
            akhir.value = ((a + w) / 2).toFixed(2);
        }
    }
    admin.addEventListener('input', hitungNilaiAkhir);
    wawancara.addEventListener('input', hitungNilaiAkhir);
</script>

<?php include __DIR__ . '/footer.php'; ?>
