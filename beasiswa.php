<?php
require_once __DIR__ . '/config/auth.php';
wajib_login();

$judul      = 'CRUD Data Beasiswa';
$deskripsi  = 'Kelola data program beasiswa mahasiswa.';
$activePage = 'beasiswa.php';

$jenisOptions  = ['Akademik', 'Non Akademik', 'Ekonomi', 'Prestasi', 'Tahfidz', 'Lainnya'];
$statusOptions = ['Dibuka', 'Ditutup', 'Selesai'];

/* ---------------------------------------------------------
   Simpan (tambah / ubah)
--------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_simpan'])) {
    $id             = $_POST['id_beasiswa'] ?? '';
    $nama           = trim($_POST['nama_beasiswa'] ?? '');
    $jenis          = $_POST['jenis_beasiswa'] ?? '';
    $kuota          = (int) ($_POST['kuota'] ?? 0);
    $penyelenggara  = trim($_POST['penyelenggara'] ?? '');
    $tglBuka        = $_POST['tanggal_buka'] ?? '';
    $tglTutup       = $_POST['tanggal_tutup'] ?? '';
    $persyaratan    = trim($_POST['persyaratan'] ?? '');
    $status         = $_POST['status_beasiswa'] ?? 'Dibuka';

    if ($nama === '' || !in_array($jenis, $jenisOptions) || $kuota < 1 || $penyelenggara === ''
        || $tglBuka === '' || $tglTutup === '' || !in_array($status, $statusOptions)) {
        flash_dan_redirect('beasiswa.php', 'danger', 'Semua kolom wajib diisi dengan benar.');
    }

    if (strtotime($tglTutup) < strtotime($tglBuka)) {
        flash_dan_redirect('beasiswa.php', 'danger', 'Tanggal tutup tidak boleh sebelum tanggal buka.');
    }

    if ($id !== '') {
        $stmt = mysqli_prepare($koneksi, "UPDATE beasiswa SET nama_beasiswa=?, jenis_beasiswa=?, penyelenggara=?,
                                           kuota=?, tanggal_buka=?, tanggal_tutup=?, persyaratan=?, status_beasiswa=?
                                           WHERE id_beasiswa=?");
        mysqli_stmt_bind_param($stmt, 'sssissssi', $nama, $jenis, $penyelenggara, $kuota, $tglBuka, $tglTutup, $persyaratan, $status, $id);
        $pesan = 'Data beasiswa berhasil diperbarui.';
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO beasiswa
                                           (nama_beasiswa, jenis_beasiswa, penyelenggara, kuota, tanggal_buka, tanggal_tutup, persyaratan, status_beasiswa)
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssissss', $nama, $jenis, $penyelenggara, $kuota, $tglBuka, $tglTutup, $persyaratan, $status);
        $pesan = 'Data beasiswa berhasil ditambahkan.';
    }

    if (mysqli_stmt_execute($stmt)) {
        flash_dan_redirect('beasiswa.php', 'success', $pesan);
    } else {
        flash_dan_redirect('beasiswa.php', 'danger', 'Gagal menyimpan data: ' . mysqli_error($koneksi));
    }
}

/* ---------------------------------------------------------
   Hapus
--------------------------------------------------------- */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM beasiswa WHERE id_beasiswa=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        flash_dan_redirect('beasiswa.php', 'success', 'Data beasiswa berhasil dihapus.');
    } else {
        flash_dan_redirect('beasiswa.php', 'danger', 'Gagal menghapus data (mungkin masih memiliki pendaftar terkait).');
    }
}

/* ---------------------------------------------------------
   Data untuk form edit (jika ada)
--------------------------------------------------------- */
$dataEdit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM beasiswa WHERE id_beasiswa=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $dataEdit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

/* ---------------------------------------------------------
   Pencarian & daftar
--------------------------------------------------------- */
$cari = trim($_GET['cari'] ?? '');
if ($cari !== '') {
    $like = '%' . $cari . '%';
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM beasiswa WHERE nama_beasiswa LIKE ? OR penyelenggara LIKE ? OR jenis_beasiswa LIKE ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $daftarBeasiswa = mysqli_stmt_get_result($stmt);
} else {
    $daftarBeasiswa = mysqli_query($koneksi, "SELECT * FROM beasiswa ORDER BY created_at DESC");
}

include __DIR__ . '/partials/header.php';
?>

<div class="section-card">
    <div class="section-title"><?= $dataEdit ? 'Ubah Data Beasiswa' : 'Form Tambah Beasiswa' ?></div>

    <form method="post" action="beasiswa.php">
        <input type="hidden" name="aksi_simpan" value="1">
        <?php if ($dataEdit): ?>
            <input type="hidden" name="id_beasiswa" value="<?= (int)$dataEdit['id_beasiswa'] ?>">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Beasiswa</label>
                <input type="text" name="nama_beasiswa" class="form-control" placeholder="Contoh: Beasiswa Prestasi Akademik 2026"
                       value="<?= htmlspecialchars($dataEdit['nama_beasiswa'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Jenis Beasiswa</label>
                <select name="jenis_beasiswa" class="form-select" required>
                    <option value="">Pilih jenis</option>
                    <?php foreach ($jenisOptions as $opt): ?>
                        <option <?= (($dataEdit['jenis_beasiswa'] ?? '') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Kuota</label>
                <input type="number" name="kuota" class="form-control" min="1" placeholder="25"
                       value="<?= htmlspecialchars($dataEdit['kuota'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Penyelenggara</label>
                <input type="text" name="penyelenggara" class="form-control" placeholder="Contoh: Universitas"
                       value="<?= htmlspecialchars($dataEdit['penyelenggara'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Buka</label>
                <input type="date" name="tanggal_buka" class="form-control"
                       value="<?= htmlspecialchars($dataEdit['tanggal_buka'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Tutup</label>
                <input type="date" name="tanggal_tutup" class="form-control"
                       value="<?= htmlspecialchars($dataEdit['tanggal_tutup'] ?? '') ?>" required>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Persyaratan</label>
                <textarea name="persyaratan" class="form-control" rows="3" placeholder="Tuliskan syarat beasiswa"><?= htmlspecialchars($dataEdit['persyaratan'] ?? '') ?></textarea>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Status Beasiswa</label>
                <select name="status_beasiswa" class="form-select" required>
                    <?php foreach ($statusOptions as $opt): ?>
                        <option <?= (($dataEdit['status_beasiswa'] ?? 'Dibuka') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary-custom"><?= $dataEdit ? 'Simpan Perubahan' : 'Simpan Data' ?></button>
            <?php if ($dataEdit): ?>
                <a href="beasiswa.php" class="btn btn-light border rounded-3 ms-2">Batal</a>
            <?php else: ?>
                <button type="reset" class="btn btn-light border rounded-3 ms-2">Reset</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="section-card">
    <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">
        <div class="section-title mb-0">Daftar Beasiswa</div>
        <form method="get" action="beasiswa.php">
            <input type="search" name="cari" class="form-control w-auto" placeholder="Cari beasiswa"
                   value="<?= htmlspecialchars($cari) ?>">
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Beasiswa</th>
                    <th>Jenis</th>
                    <th>Penyelenggara</th>
                    <th>Kuota</th>
                    <th>Status</th>
                    <th width="170">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($daftarBeasiswa) === 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data beasiswa yang cocok.</td></tr>
                <?php else: $no = 1; while ($row = mysqli_fetch_assoc($daftarBeasiswa)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_beasiswa']) ?></td>
                        <td><?= htmlspecialchars($row['jenis_beasiswa']) ?></td>
                        <td><?= htmlspecialchars($row['penyelenggara']) ?></td>
                        <td class="num"><?= (int)$row['kuota'] ?></td>
                        <td>
                            <?php
                            $badgeKelas = $row['status_beasiswa'] === 'Dibuka' ? 'badge-soft-success'
                                        : ($row['status_beasiswa'] === 'Selesai' ? 'badge-soft-primary' : 'badge-soft-danger');
                            ?>
                            <span class="badge <?= $badgeKelas ?>"><?= htmlspecialchars($row['status_beasiswa']) ?></span>
                        </td>
                        <td>
                            <a href="beasiswa.php?edit=<?= (int)$row['id_beasiswa'] ?>" class="btn btn-warning action-btn">Edit</a>
                            <a href="beasiswa.php?hapus=<?= (int)$row['id_beasiswa'] ?>" class="btn btn-danger action-btn"
                               onclick="return confirm('Hapus data beasiswa ini? Semua pendaftar terkait juga akan terhapus.');">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
