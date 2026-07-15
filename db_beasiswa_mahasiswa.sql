-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 05:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_beasiswa_mahasiswa`
--

-- --------------------------------------------------------

--
-- Table structure for table `beasiswa`
--

CREATE TABLE `beasiswa` (
  `id_beasiswa` int(11) NOT NULL,
  `nama_beasiswa` varchar(150) NOT NULL,
  `jenis_beasiswa` enum('Akademik','Non Akademik','Ekonomi','Prestasi','Tahfidz','Lainnya') NOT NULL,
  `penyelenggara` varchar(150) NOT NULL,
  `kuota` int(11) NOT NULL,
  `tanggal_buka` date NOT NULL,
  `tanggal_tutup` date NOT NULL,
  `persyaratan` text DEFAULT NULL,
  `status_beasiswa` enum('Dibuka','Ditutup','Selesai') DEFAULT 'Dibuka',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `beasiswa`
--

INSERT INTO `beasiswa` (`id_beasiswa`, `nama_beasiswa`, `jenis_beasiswa`, `penyelenggara`, `kuota`, `tanggal_buka`, `tanggal_tutup`, `persyaratan`, `status_beasiswa`, `created_at`) VALUES
(1, 'Beasiswa Prestasi Akademik 2026', 'Akademik', 'Universitas', 25, '2026-07-01', '2026-07-30', 'IPK minimal 3.50, aktif kuliah, melampirkan KTM dan KHS.', 'Dibuka', '2026-07-01 02:27:17'),
(2, 'Beasiswa Ekonomi Mahasiswa', 'Ekonomi', 'Bagian Kemahasiswaan', 40, '2026-07-05', '2026-08-05', 'Melampirkan surat keterangan tidak mampu, KTM, dan KHS.', 'Dibuka', '2026-07-01 02:27:17'),
(3, 'Beasiswa Tahfidz Kampus', 'Tahfidz', 'Lembaga Dakwah Kampus', 15, '2026-07-10', '2026-07-20', 'Hafal minimal 5 juz, aktif kuliah, rekomendasi dari pembina tahfidz.', 'Dibuka', '2026-07-01 02:27:17');


-- --------------------------------------------------------

--
-- Table structure for table `pendaftar_beasiswa`
--

CREATE TABLE `pendaftar_beasiswa` (
  `id_pendaftar` int(11) NOT NULL,
  `id_beasiswa` int(11) NOT NULL,
  `nim` varchar(30) NOT NULL,
  `nama_mahasiswa` varchar(120) NOT NULL,
  `prodi` varchar(100) NOT NULL,
  `semester` int(11) NOT NULL,
  `ipk` decimal(3,2) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `penghasilan_orang_tua` decimal(12,2) DEFAULT NULL,
  `berkas_ktm` enum('Ada','Tidak Ada') DEFAULT 'Tidak Ada',
  `berkas_khs` enum('Ada','Tidak Ada') DEFAULT 'Tidak Ada',
  `berkas_surat_keterangan` enum('Ada','Tidak Ada') DEFAULT 'Tidak Ada',
  `status_pendaftaran` enum('Menunggu','Valid','Tidak Valid') DEFAULT 'Menunggu',
  `tanggal_daftar` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftar_beasiswa`
--

INSERT INTO `pendaftar_beasiswa` (`id_pendaftar`, `id_beasiswa`, `nim`, `nama_mahasiswa`, `prodi`, `semester`, `ipk`, `no_hp`, `email`, `alamat`, `penghasilan_orang_tua`, `berkas_ktm`, `berkas_khs`, `berkas_surat_keterangan`, `status_pendaftaran`, `tanggal_daftar`, `created_at`) VALUES
(1, 1, '220212001', 'Ahmad Fauzi', 'Pendidikan Teknologi Informasi', 6, 3.82, '081234560001', 'ahmad.fauzi@email.com', 'Banda Aceh', 3200000.00, 'Ada', 'Ada', 'Ada', 'Valid', '2026-07-02', '2026-07-02 01:10:00'),
(2, 2, '220212002', 'Siti Rahmah', 'Pendidikan Matematika', 4, 3.45, '081234560002', 'siti.rahmah@email.com', 'Sigli', 1800000.00, 'Ada', 'Tidak Ada', 'Ada', 'Menunggu', '2026-07-06', '2026-07-06 01:10:00'),
(3, 1, '220212003', 'Muhammad Iqbal', 'Sistem Informasi', 8, 3.91, '081234560003', 'iqbal@email.com', 'Lhokseumawe', 2500000.00, 'Ada', 'Ada', 'Ada', 'Valid', '2026-07-03', '2026-07-03 01:10:00'),
(4, 2, '220212010', 'Nurul Aini', 'Pendidikan Matematika', 6, 3.76, '081234560004', 'nurul.aini@email.com', 'Banda Aceh', 1500000.00, 'Ada', 'Ada', 'Ada', 'Valid', '2026-07-06', '2026-07-06 02:00:00'),
(5, 3, '220212015', 'Fajar Ramadhan', 'Pendidikan Bahasa Inggris', 4, 3.60, '081234560005', 'fajar.ramadhan@email.com', 'Meulaboh', 2000000.00, 'Ada', 'Ada', 'Tidak Ada', 'Menunggu', '2026-07-11', '2026-07-11 01:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `seleksi`
--

CREATE TABLE `seleksi` (
  `id_seleksi` int(11) NOT NULL,
  `id_pendaftar` int(11) NOT NULL,
  `tahap_seleksi` enum('Administrasi','Wawancara','Final') DEFAULT 'Administrasi',
  `nilai_administrasi` decimal(5,2) DEFAULT 0.00,
  `nilai_wawancara` decimal(5,2) DEFAULT 0.00,
  `nilai_akhir` decimal(5,2) DEFAULT 0.00,
  `status_seleksi` enum('Diproses','Lulus','Tidak Lulus','Cadangan') DEFAULT 'Diproses',
  `catatan` text DEFAULT NULL,
  `tanggal_seleksi` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seleksi`
--

INSERT INTO `seleksi` (`id_seleksi`, `id_pendaftar`, `tahap_seleksi`, `nilai_administrasi`, `nilai_wawancara`, `nilai_akhir`, `status_seleksi`, `catatan`, `tanggal_seleksi`, `created_at`) VALUES
(1, 1, 'Final', 90.00, 87.00, 88.50, 'Lulus', 'Berkas lengkap dan memenuhi kriteria akademik.', '2026-07-04', '2026-07-04 03:00:00'),
(2, 2, 'Administrasi', 74.00, 0.00, 74.00, 'Diproses', 'Menunggu kelengkapan berkas KHS.', '2026-07-07', '2026-07-07 03:00:00'),
(3, 3, 'Final', 92.00, 89.00, 90.50, 'Lulus', 'Nilai wawancara sangat baik.', '2026-07-05', '2026-07-05 03:00:00'),
(4, 4, 'Final', 88.00, 83.50, 85.75, 'Lulus', 'Direkomendasikan oleh dosen wali.', '2026-07-07', '2026-07-07 03:00:00'),
(5, 5, 'Wawancara', 80.00, 70.00, 75.00, 'Cadangan', 'Perlu verifikasi ulang hafalan juz.', '2026-07-12', '2026-07-12 03:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `user_login`
--

CREATE TABLE `user_login` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','operator','pimpinan') DEFAULT 'operator',
  `status_akun` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_login`
--

INSERT INTO `user_login` (`id_user`, `nama_lengkap`, `username`, `email`, `password_hash`, `role`, `status_akun`, `created_at`) VALUES
(1, 'Administrator Beasiswa', 'admin', 'admin@kampus.ac.id', 'admin123', 'admin', 'aktif', '2026-07-01 02:27:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `beasiswa`
--
ALTER TABLE `beasiswa`
  ADD PRIMARY KEY (`id_beasiswa`);

--
-- Indexes for table `pendaftar_beasiswa`
--
ALTER TABLE `pendaftar_beasiswa`
  ADD PRIMARY KEY (`id_pendaftar`),
  ADD KEY `fk_pendaftar_beasiswa` (`id_beasiswa`);

--
-- Indexes for table `seleksi`
--
ALTER TABLE `seleksi`
  ADD PRIMARY KEY (`id_seleksi`),
  ADD KEY `fk_seleksi_pendaftar` (`id_pendaftar`);

--
-- Indexes for table `user_login`
--
ALTER TABLE `user_login`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `beasiswa`
--
ALTER TABLE `beasiswa`
  MODIFY `id_beasiswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pendaftar_beasiswa`
--
ALTER TABLE `pendaftar_beasiswa`
  MODIFY `id_pendaftar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `seleksi`
--
ALTER TABLE `seleksi`
  MODIFY `id_seleksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_login`
--
ALTER TABLE `user_login`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pendaftar_beasiswa`
--
ALTER TABLE `pendaftar_beasiswa`
  ADD CONSTRAINT `fk_pendaftar_beasiswa` FOREIGN KEY (`id_beasiswa`) REFERENCES `beasiswa` (`id_beasiswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `seleksi`
--
ALTER TABLE `seleksi`
  ADD CONSTRAINT `fk_seleksi_pendaftar` FOREIGN KEY (`id_pendaftar`) REFERENCES `pendaftar_beasiswa` (`id_pendaftar`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
