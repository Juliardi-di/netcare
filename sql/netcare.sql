-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Feb 2026 pada 15.54
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `netcare`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_instansi`
--

CREATE TABLE `admin_instansi` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telepon` varchar(50) NOT NULL,
  `alamat` text DEFAULT NULL,
  `images` varchar(255) DEFAULT 'default.png',
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin_instansi`
--

INSERT INTO `admin_instansi` (`id`, `username`, `nama`, `email`, `telepon`, `alamat`, `images`, `password`, `created_at`) VALUES
(1, 'admin1', 'Administrator', 'admin1@mail.com', '08123456789', 'Jl. Merdeka', 'default.png', '0192023a7bbd73250516f069df18b500', '2026-01-04 05:44:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `aturan_tim`
--

CREATE TABLE `aturan_tim` (
  `jenis_layanan` varchar(50) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aturan_tim`
--

INSERT INTO `aturan_tim` (`jenis_layanan`, `jumlah`) VALUES
('Dokumentasi', 3),
('Live Streaming', 10),
('Zoom Meeting', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumentasi`
--

CREATE TABLE `dokumentasi` (
  `id` int(11) NOT NULL,
  `pengajuan_id` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `tanggal_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokumentasi`
--

INSERT INTO `dokumentasi` (`id`, `pengajuan_id`, `judul`, `deskripsi`, `file_path`, `tanggal_upload`) VALUES
(127, 103, 'Pelaksanaan Gerakan Pangan Murah Serentak dalam Rangka Persiapan Menghadapi HBKN  Tahun Baru Imlek dan Puasa Ramadhan Tahun 2026 serta menjaga stabilitasi pasokan dan harga pangan di Kabupaten Lingga', 'Lapangan Hangtuah Daik Lingga', 'uploads/1770393984_04de878fd371.pdf', '2026-02-06 23:06:25'),
(131, 107, 'Rapat Harmonisasi', 'Ruang Rapat Diskominfo Lingga', 'uploads/1770398786_fd12530dd6ba.pdf', '2026-02-07 00:26:26'),
(133, 109, 'Musyawarah Perencanaan Pembangunan (MUSRENBANG) Tingkat Kabupaten Tahun 2026 dalam Rangka Penyusunan Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Lingga Tahun 2027', 'AULA KANTOR BUPATI LINGGA', 'uploads/1770401658_ffacaad5eae3.pdf', '2026-02-07 01:14:18'),
(136, 103, '', 'LAPANGAN HANGTUAH DAIK LINGGA', 'uploads/dokumentasi/1770642307_6753.jpeg', '2026-02-09 20:05:07'),
(137, 107, '', 'VIP', 'uploads/dokumentasi/1770655105_5779.jpeg', '2026-02-09 23:38:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `isi_laporan` text NOT NULL,
  `tanggal_laporan` date NOT NULL DEFAULT curdate(),
  `status` enum('diproses','selesai','ditolak') DEFAULT 'diproses',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `laporan`
--

INSERT INTO `laporan` (`id`, `user_id`, `isi_laporan`, `tanggal_laporan`, `status`, `created_at`) VALUES
(2, 2, 'Live streaming sukses dilakukan.', '2026-01-04', 'selesai', '2026-01-04 05:44:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_petugas`
--

CREATE TABLE `master_petugas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `instansi` varchar(100) DEFAULT 'Diskominfo',
  `aktif` tinyint(1) DEFAULT 1,
  `jenis_layanan` enum('zoom_meeting','live_streaming') NOT NULL,
  `last_assigned` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `master_petugas`
--

INSERT INTO `master_petugas` (`id`, `nama`, `jabatan`, `instansi`, `aktif`, `jenis_layanan`, `last_assigned`) VALUES
(1, 'Asrido Akbar', 'Koordinator Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(2, 'Hendra', 'Kameramen Handycam Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(3, 'Miki Wahyudi Alamsyah', 'Kameramen Sony  Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(4, 'Al Imran Mulyadi', 'Kameramen Handycam Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(5, 'Reno Widi', 'Jaringan  Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(6, 'Budi Santoso', 'Koordinator Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(7, 'M. Juliardi', 'Operator Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 15:14:41'),
(8, 'Tiwi Irwan Sari', 'Operator Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(9, 'Rani', 'Operator Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(10, 'Alex Petrus', 'Operator Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 15:14:41'),
(11, 'M. Zulhaditya Hafis', 'Kameramen Sony  Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 22:14:30'),
(12, 'Sayed Omas ', 'Jaringan  Live Streaming', 'Diskominfo', 1, 'live_streaming', '2026-02-09 15:14:41'),
(13, 'Miki Wahyudi Alamsyah', 'Operator Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-09 22:14:41'),
(14, 'Al Imran Mulyadi', 'Operator Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-09 22:14:41'),
(15, 'M. Juliardi', 'Operator Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-17 18:17:08'),
(16, 'Tiwi Irwan Sari', 'Operator Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-17 18:17:02'),
(17, 'Wendry Arya', 'Jaringan Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-17 18:17:08'),
(18, 'Reno Widi', 'Jaringan Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-17 18:17:02'),
(19, 'Hendra', 'Jaringan Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-17 18:16:59'),
(20, 'Rani', 'Operator Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-17 18:16:59'),
(21, 'Sayed Omas', 'Jaringan Zoom Meeting', 'Diskominfo', 1, 'zoom_meeting', '2026-02-07 01:28:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL DEFAULT curdate(),
  `status` enum('pending','diproses','selesai','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan`
--

CREATE TABLE `pengajuan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `instansi_pengaju_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `jenis_layanan` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `tanggal_pelaksanaan` date DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  `catatan_admin` text DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `tanggal_pengajuan` datetime DEFAULT current_timestamp(),
  `status_admin_utama` enum('menunggu','diteruskan') NOT NULL DEFAULT 'menunggu',
  `sudah_masuk_laporan` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengajuan`
--

INSERT INTO `pengajuan` (`id`, `user_id`, `instansi_pengaju_id`, `judul`, `jenis_layanan`, `deskripsi`, `tanggal_pelaksanaan`, `status`, `catatan_admin`, `deleted_at`, `tanggal_pengajuan`, `status_admin_utama`, `sudah_masuk_laporan`) VALUES
(103, 10, 0, 'Pelaksanaan Gerakan Pangan Murah Serentak dalam Rangka Persiapan Menghadapi HBKN  Tahun Baru Imlek dan Puasa Ramadhan Tahun 2026 serta menjaga stabilitasi pasokan dan harga pangan di Kabupaten Lingga', 'Zoom Meeting', 'Lapangan Hangtuah Daik Lingga', '2026-02-09', 'disetujui', 'Tugas siap dilaksanakan - Silakan koordinasikan dengan petugas', NULL, '2026-02-06 23:06:24', 'diteruskan', 0),
(107, 18, 0, 'Rapat Harmonisasi', 'Zoom Meeting', 'Ruang Rapat Diskominfo Lingga', '2026-02-03', 'disetujui', 'Silahkan Laksanakan Tugas, Tetap Sehat dan Tetap Semangat!!!', NULL, '2026-02-02 00:26:00', 'diteruskan', 0),
(109, 9, 0, 'Musyawarah Perencanaan Pembangunan (MUSRENBANG) Tingkat Kabupaten Tahun 2026 dalam Rangka Penyusunan Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Lingga Tahun 2027', 'Live Streaming', 'AULA KANTOR BUPATI LINGGA', '2026-02-11', 'disetujui', 'Silahkan Laksanakan Tugas, Tetap Sehat dan Tetap Semangat!!!', NULL, '2026-02-06 19:13:00', 'diteruskan', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_layanan`
--

CREATE TABLE `pengajuan_layanan` (
  `id` int(11) NOT NULL,
  `pengajuan_id` int(11) NOT NULL,
  `output` varchar(100) DEFAULT NULL,
  `utama` int(11) DEFAULT 0,
  `tambahan` int(11) DEFAULT 0,
  `jenis` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `keterangan2` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengajuan_layanan`
--

INSERT INTO `pengajuan_layanan` (`id`, `pengajuan_id`, `output`, `utama`, `tambahan`, `jenis`, `keterangan`, `keterangan2`, `created_at`) VALUES
(1, 107, '1', 999, 0, '', '', '0', '2026-02-09 08:19:18'),
(3, 103, '', 0, 0, '', '', '0', '2026-02-09 08:33:03'),
(4, 109, '', 0, 0, '', '', '0', '2026-02-10 14:14:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tim_petugas`
--

CREATE TABLE `tim_petugas` (
  `id` int(11) NOT NULL,
  `pengajuan_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `ditentukan_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tim_petugas`
--

INSERT INTO `tim_petugas` (`id`, `pengajuan_id`, `petugas_id`, `ditentukan_oleh`, `dibuat_pada`) VALUES
(3, 51, 13, 5, '2026-01-22 17:04:22'),
(4, 51, 14, 5, '2026-01-22 17:04:22'),
(5, 51, 15, 5, '2026-01-22 17:04:22'),
(6, 51, 16, 5, '2026-01-22 17:04:22'),
(7, 51, 17, 5, '2026-01-22 17:04:22'),
(8, 51, 18, 5, '2026-01-22 17:04:22'),
(9, 51, 19, 5, '2026-01-22 17:04:22'),
(10, 49, 13, 5, '2026-01-22 17:14:42'),
(11, 49, 15, 5, '2026-01-22 17:14:42'),
(13, 46, 15, 5, '2026-01-22 17:14:48'),
(14, 46, 19, 5, '2026-01-22 17:14:48'),
(16, 45, 19, 5, '2026-01-22 17:14:52'),
(17, 45, 13, 5, '2026-01-22 17:14:52'),
(19, 52, 14, 5, '2026-01-22 17:19:18'),
(20, 52, 13, 5, '2026-01-22 17:19:18'),
(22, 53, 17, 5, '2026-01-22 17:19:23'),
(23, 53, 13, 5, '2026-01-22 17:19:23'),
(25, 54, 14, 5, '2026-01-22 17:19:28'),
(26, 54, 19, 5, '2026-01-22 17:19:28'),
(28, 55, 17, 5, '2026-01-22 17:19:32'),
(29, 55, 16, 5, '2026-01-22 17:19:32'),
(31, 56, 18, 5, '2026-01-22 17:19:36'),
(32, 56, 19, 5, '2026-01-22 17:19:36'),
(34, 57, 6, 5, '2026-01-22 17:19:42'),
(35, 57, 7, 5, '2026-01-22 17:19:42'),
(37, 58, 4, 5, '2026-01-22 17:39:30'),
(38, 58, 9, 5, '2026-01-22 17:39:30'),
(39, 58, 11, 5, '2026-01-22 17:39:30'),
(40, 58, 5, 5, '2026-01-22 17:39:30'),
(41, 58, 7, 5, '2026-01-22 17:39:30'),
(42, 58, 1, 5, '2026-01-22 17:39:30'),
(43, 58, 2, 5, '2026-01-22 17:39:30'),
(44, 58, 6, 5, '2026-01-22 17:39:30'),
(45, 59, 3, 5, '2026-01-22 17:40:31'),
(46, 59, 10, 5, '2026-01-22 17:40:31'),
(47, 59, 12, 5, '2026-01-22 17:40:31'),
(48, 59, 8, 5, '2026-01-22 17:40:31'),
(49, 59, 1, 5, '2026-01-22 17:40:31'),
(50, 59, 6, 5, '2026-01-22 17:40:31'),
(51, 59, 4, 5, '2026-01-22 17:40:31'),
(52, 59, 2, 5, '2026-01-22 17:40:31'),
(53, 60, 5, 5, '2026-01-22 17:41:57'),
(54, 60, 11, 5, '2026-01-22 17:41:57'),
(55, 60, 9, 5, '2026-01-22 17:41:57'),
(56, 60, 7, 5, '2026-01-22 17:41:57'),
(57, 60, 6, 5, '2026-01-22 17:41:57'),
(58, 60, 2, 5, '2026-01-22 17:41:57'),
(59, 60, 4, 5, '2026-01-22 17:41:57'),
(60, 60, 1, 5, '2026-01-22 17:41:57'),
(61, 61, 3, 5, '2026-01-22 17:47:49'),
(62, 61, 7, 5, '2026-01-22 17:47:49'),
(63, 61, 9, 5, '2026-01-22 17:47:49'),
(64, 61, 4, 5, '2026-01-22 17:47:49'),
(65, 61, 12, 5, '2026-01-22 17:47:49'),
(66, 61, 1, 5, '2026-01-22 17:47:49'),
(67, 61, 6, 5, '2026-01-22 17:47:49'),
(68, 61, 2, 5, '2026-01-22 17:47:49'),
(69, 62, 4, 5, '2026-01-22 17:48:47'),
(70, 62, 12, 5, '2026-01-22 17:48:47'),
(71, 62, 9, 5, '2026-01-22 17:48:47'),
(72, 62, 11, 5, '2026-01-22 17:48:47'),
(73, 62, 1, 5, '2026-01-22 17:48:47'),
(74, 62, 8, 5, '2026-01-22 17:48:47'),
(75, 62, 2, 5, '2026-01-22 17:48:47'),
(76, 62, 6, 5, '2026-01-22 17:48:47'),
(77, 66, 10, 5, '2026-01-22 17:56:37'),
(78, 66, 5, 5, '2026-01-22 17:56:37'),
(79, 66, 3, 5, '2026-01-22 17:56:37'),
(80, 66, 7, 5, '2026-01-22 17:56:37'),
(81, 66, 6, 5, '2026-01-22 17:56:37'),
(82, 66, 1, 5, '2026-01-22 17:56:37'),
(83, 66, 2, 5, '2026-01-22 17:56:37'),
(84, 66, 4, 5, '2026-01-22 17:56:37'),
(85, 64, 11, 5, '2026-01-22 17:56:52'),
(86, 64, 8, 5, '2026-01-22 17:56:52'),
(87, 64, 9, 5, '2026-01-22 17:56:52'),
(88, 64, 12, 5, '2026-01-22 17:56:52'),
(89, 64, 4, 5, '2026-01-22 17:56:52'),
(90, 64, 6, 5, '2026-01-22 17:56:52'),
(91, 64, 1, 5, '2026-01-22 17:56:52'),
(92, 64, 2, 5, '2026-01-22 17:56:52'),
(93, 63, 19, 5, '2026-01-22 17:56:58'),
(94, 63, 16, 5, '2026-01-22 17:56:58'),
(96, 65, 18, 5, '2026-01-22 17:57:02'),
(97, 65, 16, 5, '2026-01-22 17:57:02'),
(98, 71, 1, 5, '2026-01-22 22:54:48'),
(99, 71, 6, 5, '2026-01-22 22:54:48'),
(100, 71, 7, 5, '2026-01-22 22:54:48'),
(101, 71, 10, 5, '2026-01-22 22:54:48'),
(102, 71, 5, 5, '2026-01-22 22:54:48'),
(103, 71, 3, 5, '2026-01-22 22:54:48'),
(104, 71, 11, 5, '2026-01-22 22:54:48'),
(105, 71, 2, 5, '2026-01-22 22:54:48'),
(106, 71, 4, 5, '2026-01-22 22:54:48'),
(107, 70, 1, 5, '2026-01-22 22:55:10'),
(108, 70, 6, 5, '2026-01-22 22:55:10'),
(109, 70, 8, 5, '2026-01-22 22:55:10'),
(110, 70, 9, 5, '2026-01-22 22:55:10'),
(111, 70, 12, 5, '2026-01-22 22:55:10'),
(112, 70, 11, 5, '2026-01-22 22:55:10'),
(113, 70, 3, 5, '2026-01-22 22:55:10'),
(114, 70, 4, 5, '2026-01-22 22:55:10'),
(115, 70, 2, 5, '2026-01-22 22:55:10'),
(116, 69, 1, 5, '2026-01-22 22:55:35'),
(117, 69, 6, 5, '2026-01-22 22:55:35'),
(118, 69, 7, 5, '2026-01-22 22:55:35'),
(119, 69, 10, 5, '2026-01-22 22:55:35'),
(120, 69, 5, 5, '2026-01-22 22:55:35'),
(121, 69, 11, 5, '2026-01-22 22:55:35'),
(122, 69, 3, 5, '2026-01-22 22:55:35'),
(123, 69, 4, 5, '2026-01-22 22:55:35'),
(124, 69, 2, 5, '2026-01-22 22:55:35'),
(125, 75, 6, 5, '2026-01-22 23:00:49'),
(126, 75, 1, 5, '2026-01-22 23:00:49'),
(127, 75, 12, 5, '2026-01-22 23:00:49'),
(128, 75, 11, 5, '2026-01-22 23:00:49'),
(129, 75, 3, 5, '2026-01-22 23:00:49'),
(130, 75, 4, 5, '2026-01-22 23:00:49'),
(131, 75, 2, 5, '2026-01-22 23:00:49'),
(132, 75, 9, 5, '2026-01-22 23:00:49'),
(133, 75, 8, 5, '2026-01-22 23:00:49'),
(134, 74, 1, 5, '2026-01-22 23:01:06'),
(135, 74, 6, 5, '2026-01-22 23:01:06'),
(136, 74, 5, 5, '2026-01-22 23:01:06'),
(137, 74, 3, 5, '2026-01-22 23:01:06'),
(138, 74, 11, 5, '2026-01-22 23:01:06'),
(139, 74, 4, 5, '2026-01-22 23:01:06'),
(140, 74, 2, 5, '2026-01-22 23:01:06'),
(141, 74, 10, 5, '2026-01-22 23:01:06'),
(142, 74, 8, 5, '2026-01-22 23:01:06'),
(143, 73, 6, 5, '2026-01-22 23:01:13'),
(144, 73, 1, 5, '2026-01-22 23:01:13'),
(145, 73, 12, 5, '2026-01-22 23:01:13'),
(146, 73, 11, 5, '2026-01-22 23:01:13'),
(147, 73, 3, 5, '2026-01-22 23:01:13'),
(148, 73, 4, 5, '2026-01-22 23:01:13'),
(149, 73, 2, 5, '2026-01-22 23:01:13'),
(150, 73, 10, 5, '2026-01-22 23:01:13'),
(151, 73, 7, 5, '2026-01-22 23:01:13'),
(152, 72, 6, 5, '2026-01-22 23:01:20'),
(153, 72, 1, 5, '2026-01-22 23:01:20'),
(154, 72, 5, 5, '2026-01-22 23:01:20'),
(155, 72, 3, 5, '2026-01-22 23:01:20'),
(156, 72, 11, 5, '2026-01-22 23:01:20'),
(157, 72, 4, 5, '2026-01-22 23:01:20'),
(158, 72, 2, 5, '2026-01-22 23:01:20'),
(159, 72, 7, 5, '2026-01-22 23:01:20'),
(160, 72, 8, 5, '2026-01-22 23:01:20'),
(161, 68, 13, 5, '2026-01-22 23:01:28'),
(162, 68, 18, 5, '2026-01-22 23:01:28'),
(164, 67, 17, 5, '2026-01-22 23:01:32'),
(165, 67, 19, 5, '2026-01-22 23:01:32'),
(167, 76, 20, 5, '2026-01-22 23:15:34'),
(168, 76, 21, 5, '2026-01-22 23:15:34'),
(169, 77, 16, 5, '2026-01-22 23:16:35'),
(170, 77, 19, 5, '2026-01-22 23:16:35'),
(171, 78, 15, 5, '2026-01-22 23:17:06'),
(172, 78, 18, 5, '2026-01-22 23:17:06'),
(173, 80, 13, 5, '2026-01-23 03:58:42'),
(174, 80, 17, 5, '2026-01-23 03:58:42'),
(175, 82, 14, 5, '2026-01-23 03:58:47'),
(176, 82, 21, 5, '2026-01-23 03:58:47'),
(177, 83, 6, 5, '2026-01-23 13:36:23'),
(178, 83, 1, 5, '2026-01-23 13:36:23'),
(179, 83, 12, 5, '2026-01-23 13:36:23'),
(180, 83, 11, 5, '2026-01-23 13:36:23'),
(181, 83, 3, 5, '2026-01-23 13:36:23'),
(182, 83, 4, 5, '2026-01-23 13:36:23'),
(183, 83, 2, 5, '2026-01-23 13:36:23'),
(184, 83, 10, 5, '2026-01-23 13:36:23'),
(185, 83, 8, 5, '2026-01-23 13:36:23'),
(186, 88, 20, 5, '2026-01-24 04:35:01'),
(187, 88, 19, 5, '2026-01-24 04:35:01'),
(188, 87, 16, 5, '2026-01-24 04:35:04'),
(189, 87, 18, 5, '2026-01-24 04:35:04'),
(190, 86, 6, 5, '2026-01-24 04:35:07'),
(191, 86, 1, 5, '2026-01-24 04:35:07'),
(192, 86, 5, 5, '2026-01-24 04:35:07'),
(193, 86, 11, 5, '2026-01-24 04:35:07'),
(194, 86, 3, 5, '2026-01-24 04:35:07'),
(195, 86, 2, 5, '2026-01-24 04:35:07'),
(196, 86, 4, 5, '2026-01-24 04:35:07'),
(197, 86, 8, 5, '2026-01-24 04:35:07'),
(198, 86, 9, 5, '2026-01-24 04:35:07'),
(199, 85, 15, 5, '2026-01-24 04:35:12'),
(200, 85, 17, 5, '2026-01-24 04:35:12'),
(201, 89, 13, 5, '2026-01-24 05:16:21'),
(202, 89, 21, 5, '2026-01-24 05:16:21'),
(203, 91, 14, 5, '2026-01-24 13:22:37'),
(204, 91, 19, 5, '2026-01-24 13:22:37'),
(205, 92, 20, 5, '2026-01-24 13:28:51'),
(206, 92, 18, 5, '2026-01-24 13:28:51'),
(207, 93, 16, 5, '2026-01-24 22:50:52'),
(208, 93, 17, 5, '2026-01-24 22:50:52'),
(209, 94, 1, 5, '2026-01-25 22:21:44'),
(210, 94, 6, 5, '2026-01-25 22:21:44'),
(211, 94, 12, 5, '2026-01-25 22:21:44'),
(212, 94, 3, 5, '2026-01-25 22:21:44'),
(213, 94, 11, 5, '2026-01-25 22:21:44'),
(214, 94, 2, 5, '2026-01-25 22:21:44'),
(215, 94, 4, 5, '2026-01-25 22:21:44'),
(216, 94, 7, 5, '2026-01-25 22:21:44'),
(217, 94, 9, 5, '2026-01-25 22:21:44'),
(218, 95, 15, 5, '2026-01-25 23:22:33'),
(219, 95, 21, 5, '2026-01-25 23:22:33'),
(220, 96, 13, 5, '2026-01-27 20:45:10'),
(221, 96, 19, 5, '2026-01-27 20:45:10'),
(222, 97, 14, 5, '2026-01-28 22:13:51'),
(223, 97, 18, 5, '2026-01-28 22:13:51'),
(224, 98, 20, 5, '2026-01-28 22:15:43'),
(225, 98, 17, 5, '2026-01-28 22:15:43'),
(226, 99, 16, 5, '2026-01-30 10:35:38'),
(227, 99, 21, 5, '2026-01-30 10:35:38'),
(228, 100, 15, 5, '2026-02-06 21:57:35'),
(229, 100, 19, 5, '2026-02-06 21:57:35'),
(230, 101, 1, 5, '2026-02-06 21:59:53'),
(231, 101, 6, 5, '2026-02-06 21:59:53'),
(232, 101, 5, 5, '2026-02-06 21:59:53'),
(233, 101, 11, 5, '2026-02-06 21:59:53'),
(234, 101, 3, 5, '2026-02-06 21:59:53'),
(235, 101, 4, 5, '2026-02-06 21:59:53'),
(236, 101, 2, 5, '2026-02-06 21:59:53'),
(237, 101, 10, 5, '2026-02-06 21:59:53'),
(238, 101, 9, 5, '2026-02-06 21:59:53'),
(243, 108, 1, 5, '2026-02-07 00:41:26'),
(244, 108, 6, 5, '2026-02-07 00:41:26'),
(245, 108, 12, 5, '2026-02-07 00:41:26'),
(246, 108, 3, 5, '2026-02-07 00:41:26'),
(247, 108, 11, 5, '2026-02-07 00:41:26'),
(248, 108, 4, 5, '2026-02-07 00:41:26'),
(249, 108, 2, 5, '2026-02-07 00:41:26'),
(250, 108, 8, 5, '2026-02-07 00:41:26'),
(251, 108, 9, 5, '2026-02-07 00:41:26'),
(261, 110, 20, 5, '2026-02-07 01:28:00'),
(262, 110, 21, 5, '2026-02-07 01:28:00'),
(263, 111, 1, 5, '2026-02-09 15:14:41'),
(264, 111, 6, 5, '2026-02-09 15:14:41'),
(265, 111, 12, 5, '2026-02-09 15:14:41'),
(266, 111, 11, 5, '2026-02-09 15:14:41'),
(267, 111, 3, 5, '2026-02-09 15:14:41'),
(268, 111, 4, 5, '2026-02-09 15:14:41'),
(269, 111, 2, 5, '2026-02-09 15:14:41'),
(270, 111, 7, 5, '2026-02-09 15:14:41'),
(271, 111, 10, 5, '2026-02-09 15:14:41'),
(272, 103, 16, 2, '2026-02-09 22:08:31'),
(273, 103, 15, 2, '2026-02-09 22:08:31'),
(274, 109, 1, 2, '2026-02-09 22:14:30'),
(275, 109, 6, 2, '2026-02-09 22:14:30'),
(276, 109, 5, 2, '2026-02-09 22:14:30'),
(277, 109, 11, 2, '2026-02-09 22:14:30'),
(278, 109, 3, 2, '2026-02-09 22:14:30'),
(279, 109, 2, 2, '2026-02-09 22:14:30'),
(280, 109, 4, 2, '2026-02-09 22:14:30'),
(281, 109, 9, 2, '2026-02-09 22:14:30'),
(282, 109, 8, 2, '2026-02-09 22:14:30'),
(283, 107, 13, 2, '2026-02-09 22:14:41'),
(284, 107, 14, 2, '2026-02-09 22:14:41'),
(285, 114, 20, 5, '2026-02-17 18:16:59'),
(286, 114, 19, 5, '2026-02-17 18:16:59'),
(287, 113, 16, 5, '2026-02-17 18:17:02'),
(288, 113, 18, 5, '2026-02-17 18:17:02'),
(289, 112, 15, 5, '2026-02-17 18:17:08'),
(290, 112, 17, 5, '2026-02-17 18:17:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas_petugas`
--

CREATE TABLE `tugas_petugas` (
  `id` int(11) NOT NULL,
  `pengajuan_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `status` enum('menunggu','selesai','ditolak') DEFAULT 'menunggu',
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `nama` varchar(150) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `images` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `nama_instansi` varchar(150) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_by` varchar(50) DEFAULT 'admin_instansi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `nama`, `telepon`, `alamat`, `images`, `created_at`, `nama_instansi`, `status`, `created_by`) VALUES
(2, 'mikiwahyudiasy1996@gmail.com', '$2a$12$17Q8oJLhBvoU6tVCVVzXf.KIoGuGVB5BCHdONiBk4Daaen37rCfK2', 'admin_utama', 'Miki Wahyudi Alamsyah, S.T', '081234567899', 'Jl. Sawin Gang Pak Long No.20', 'user_2_3296b460b1da87ab.png', '2026-01-04 05:44:01', NULL, 'aktif', 'admin_utama'),
(5, 'mikiwahyudi96@gmail.com', '$2a$12$3/apfqWCwaapnvtzbDEhZushB1bEWwyFIWkC5WBwXk68iZJQuUB7S', 'admin_atasan_langsung', 'Ady Setiawan, ST', '081234567899', 'Jl. Cening No 11', 'user_5_c6c4529489c30dcf.jpg', '2026-01-04 08:10:12', NULL, 'aktif', 'admin_utama'),
(9, 'barenlitbang@booking.com', '$2a$12$0hOf/QApm5jvldu8.cdmUuadByR6D0UFSi6MdzxiPdt.QmPEHtIqi', 'opd_pengaju_layanan', 'BARENLITBANG', NULL, 'jl. nusantara', 'default.png', '2026-01-07 16:52:01', 'BARENLITBANG', 'aktif', 'admin_utama'),
(10, 'dinaspertanian@gmail.com', '$2a$12$APaLWnTeQ4EttFAeq7Nu8e4ISgJxKPdDJrEa4nS1QBoS4kByys.mu', 'opd_pengaju_layanan', 'Abdul Amin', '09888888888', 'jl.cening', 'user_10_cea3c9c65435e515.jpg', '2026-01-09 16:32:54', 'Dinas Pertanian', 'aktif', 'admin_utama'),
(11, 'dinaskebudayaan@booking.com', '$2a$12$k9KVE.Ipj8g0xgpZIEdGa.ba4H/2hbTI9WzPt.07NVa7WWcMuGWLS', 'opd_pengaju_layanan', 'ABDUL', '08123456790', 'jl. pemuda', 'user_11_f89c5b8983bab671.jpg', '2026-01-07 14:55:41', 'DINAS KEBUDAYAAN', 'aktif', 'admin_utama'),
(12, 'disdikpora@gmail.com', '$2y$10$mMId5b2dHccqdCOzs8IK0u9Q8X2nvRQDLDf9TzmvWbM74FD/UrFX.', 'opd_pengaju_layanan', 'JANUARTA TA', '088888888888', 'SAWIN', 'user_12_ca4c107c8bb9922f.jpg', '2026-01-22 04:11:49', 'Dinas Pendidikan, Pemuda dan Olahraga', 'aktif', 'admin_utama'),
(13, 'setda@gmail.com', '$2y$10$JHvoSqoTvItXhaRqtGWj9epCZdrmSRA8bqOv6KfGZo1izLmC1QD1i', 'opd_pengaju_layanan', 'ABDUL', '088888888888', 'PONDOK GEDE', 'default.png', '2026-01-22 07:02:50', 'SEKRETARIAT DAERAH', 'aktif', 'admin_utama'),
(14, 'dinsos26@gmail.com', '$2y$10$i8eZUxasmWURL5f1xZHDoOWvm0aB4FpQlbo4FEVfTtEXcljuu183O', 'opd_pengaju_layanan', 'DINAS SOSIAL', NULL, NULL, 'default.png', '2026-01-25 21:11:12', 'DINAS SOSIAL', 'aktif', 'admin_utama'),
(15, 'yudidiskominfolingga@gmail.com', '$2y$10$l.1dKP5kgD1iaJnjpGjblO8AgQ1IOcCqOtBH1Z7249GG2iqCFsDz2', 'petugas_layanan', 'Miki Wahyudi Alamsyah', NULL, NULL, 'user_15_4622e6a63533e31f.png', '2026-02-03 16:06:15', NULL, 'aktif', 'admin_utama'),
(16, 'juliardiemailtest@gmail.com', '$2y$10$p/O4Q72qP.1WfF3jf7QbRO9X6PpCvAKBBymG6prqtCwau1fvvZKLa', 'petugas_layanan', 'M. Juliardi', NULL, NULL, 'default.png', '2026-02-03 18:07:46', NULL, 'aktif', 'admin_utama'),
(17, 'renotest1@gmail.com', '$2y$10$heyIxSyqqt7gUpkBuHdBO.7f.9sVbBMvpYVTC7QtV02iKYTit2YU6', 'petugas_layanan', 'Reno Widi', NULL, NULL, 'default.png', '2026-02-06 17:03:52', NULL, 'aktif', 'admin_utama'),
(18, 'diskominfo@gmail.com', '$2y$10$ukJvu1nCjwK8hDWpQi15WeGOW2phUpBnAPk9oQmcKpgW.QBRzpJeC', 'opd_pengaju_layanan', 'Dinas Komunikasi dan Informatika', NULL, NULL, 'default.png', '2026-02-06 17:09:21', 'Dinas Komunikasi dan Informatika', 'aktif', 'admin_utama');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_rekapitulasi`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_rekapitulasi` (
`user_id` int(11)
,`email` varchar(150)
,`role` varchar(50)
,`total_pengajuan` bigint(21)
,`total_pengaduan` bigint(21)
,`total_laporan` bigint(21)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_rekapitulasi`
--
DROP TABLE IF EXISTS `v_rekapitulasi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_rekapitulasi`  AS SELECT `u`.`id` AS `user_id`, `u`.`email` AS `email`, `u`.`role` AS `role`, (select count(0) from `pengajuan` `p` where `p`.`id` = `u`.`id`) AS `total_pengajuan`, (select count(0) from `pengaduan` `pg` where `pg`.`user_id` = `u`.`id`) AS `total_pengaduan`, (select count(0) from `laporan` `lp` where `lp`.`user_id` = `u`.`id`) AS `total_laporan` FROM `users` AS `u` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin_instansi`
--
ALTER TABLE `admin_instansi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `aturan_tim`
--
ALTER TABLE `aturan_tim`
  ADD PRIMARY KEY (`jenis_layanan`);

--
-- Indeks untuk tabel `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id` (`pengajuan_id`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `master_petugas`
--
ALTER TABLE `master_petugas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pengajuan_user` (`user_id`);

--
-- Indeks untuk tabel `pengajuan_layanan`
--
ALTER TABLE `pengajuan_layanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id_idx` (`pengajuan_id`);

--
-- Indeks untuk tabel `tim_petugas`
--
ALTER TABLE `tim_petugas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_pengajuan_petugas` (`pengajuan_id`,`petugas_id`);

--
-- Indeks untuk tabel `tugas_petugas`
--
ALTER TABLE `tugas_petugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id` (`pengajuan_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin_instansi`
--
ALTER TABLE `admin_instansi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `dokumentasi`
--
ALTER TABLE `dokumentasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `master_petugas`
--
ALTER TABLE `master_petugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengajuan`
--
ALTER TABLE `pengajuan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT untuk tabel `pengajuan_layanan`
--
ALTER TABLE `pengajuan_layanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `tim_petugas`
--
ALTER TABLE `tim_petugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=291;

--
-- AUTO_INCREMENT untuk tabel `tugas_petugas`
--
ALTER TABLE `tugas_petugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD CONSTRAINT `fk_dokumentasi_pengajuan` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD CONSTRAINT `fk_pengajuan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengajuan_layanan`
--
ALTER TABLE `pengajuan_layanan`
  ADD CONSTRAINT `fk_pengajuan` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan` (`id`);

--
-- Ketidakleluasaan untuk tabel `tugas_petugas`
--
ALTER TABLE `tugas_petugas`
  ADD CONSTRAINT `tugas_petugas_ibfk_1` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan` (`id`),
  ADD CONSTRAINT `tugas_petugas_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
