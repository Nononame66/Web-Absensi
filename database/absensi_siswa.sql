-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 06:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_siswa`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `status` enum('Hadir','Sakit','Izin','Terlambat','Alpha') DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `bukti_file` varchar(255) DEFAULT NULL,
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `network_info` text DEFAULT NULL,
  `location_data` text DEFAULT NULL COMMENT 'JSON data containing latitude, longitude, accuracy, and timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_type` enum('admin','siswa') NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('login','logout','create','update','delete','approval','absensi') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_type`, `user_id`, `activity_type`, `description`, `created_at`) VALUES
(7, 'admin', 1, 'delete', 'Admin menghapus data siswa: Ahmad Fadillah (2024001)', '2025-03-03 08:32:13'),
(8, 'admin', 1, 'delete', 'Admin menghapus data siswa: Putri Rahayu (2023001)', '2025-03-03 08:32:30'),
(9, 'admin', 1, 'create', 'Admin menambahkan siswa baru: Ahmad Fadilah (2023001)', '2025-03-03 08:33:00'),
(10, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Hana Safira pada tanggal 03/03/2025', '2025-03-03 08:33:19'),
(11, 'admin', 1, 'logout', 'Admin logged out from the system', '2025-03-03 10:04:41'),
(12, 'admin', 1, 'login', 'Admin logged into the system', '2025-03-03 10:04:43'),
(13, 'admin', 1, 'logout', 'Admin logged out from the system', '2025-03-03 10:04:48'),
(14, 'admin', 1, 'login', 'Admin logged into the system', '2025-03-03 10:04:49'),
(15, 'admin', 1, 'logout', 'Admin logged out from the system', '2025-03-03 10:14:10'),
(16, 'admin', 1, 'login', 'Admin logged into the system', '2025-03-03 10:14:11'),
(17, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2025-03-03 10:17:06'),
(18, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-03 10:17:08'),
(19, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 10:30:08'),
(20, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 10:30:45'),
(21, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 10:30:48'),
(22, 'admin', 1, 'update', 'Admin mengubah password', '2025-03-03 10:43:30'),
(23, 'admin', 1, 'update', 'Admin mengubah password', '2025-03-03 10:43:45'),
(24, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 10:43:57'),
(25, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 10:45:45'),
(26, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 10:50:28'),
(27, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 10:50:35'),
(28, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 11:09:08'),
(29, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 11:09:15'),
(30, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 11:09:29'),
(31, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 11:09:38'),
(32, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2025-03-03 11:19:29'),
(33, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-03 11:19:31'),
(34, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-03 16:02:50'),
(35, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Kevin Wijaya pada tanggal 03/03/2025', '2025-03-03 16:15:51'),
(36, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2025-03-03 16:56:39'),
(37, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-03 16:57:31'),
(38, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2025-03-03 17:05:39'),
(39, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-04 07:32:47'),
(40, 'siswa', 2, 'logout', 'Siswa Budi Santoso logout dari sistem', '2025-03-04 07:43:47'),
(41, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-04 07:44:00'),
(42, 'siswa', 2, 'logout', 'Siswa Budi Santoso logout dari sistem', '2025-03-04 07:44:04'),
(43, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-04 07:44:34'),
(44, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-04 08:13:37'),
(45, 'siswa', 2, 'create', 'Siswa Budi Santoso mengisi absensi sebagai Hadir', '2025-03-04 08:18:28'),
(46, 'siswa', 2, 'create', 'Siswa Budi Santoso mengisi absensi sebagai Sakit', '2025-03-04 08:19:23'),
(47, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 12:33:46'),
(48, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengisi absensi sebagai Izin', '2025-03-04 12:33:58'),
(49, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 12:34:03'),
(50, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengisi absensi sebagai Hadir', '2025-03-04 12:34:17'),
(51, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengisi absensi sebagai Sakit', '2025-03-04 12:34:43'),
(52, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 12:34:59'),
(53, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Sakit', '2025-03-04 12:47:46'),
(54, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 12:47:53'),
(55, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 12:47:57'),
(56, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 12:48:01'),
(57, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Izin', '2025-03-04 12:48:04'),
(58, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 12:48:16'),
(59, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 12:48:18'),
(60, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 12:48:27'),
(61, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Sakit', '2025-03-04 12:48:40'),
(62, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Sakit dari siswa Budi Santoso', '2025-03-04 12:48:56'),
(63, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 13:10:20'),
(64, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 13:10:26'),
(65, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 13:21:54'),
(66, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Hadir dari siswa Budi Santoso', '2025-03-04 13:22:07'),
(67, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 13:37:14'),
(68, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 13:37:30'),
(69, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 13:37:49'),
(70, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Hadir dari siswa Budi Santoso', '2025-03-04 13:37:58'),
(71, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 13:46:13'),
(72, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 13:46:16'),
(73, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Sakit', '2025-03-04 13:52:41'),
(74, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 13:52:53'),
(75, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 14:10:30'),
(76, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-04 14:10:55'),
(77, 'siswa', 2, 'update', 'Siswa mengubah profil', '2025-03-04 14:31:30'),
(78, 'siswa', 2, 'update', 'Siswa mengubah profil', '2025-03-04 14:31:47'),
(79, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-04 14:33:23'),
(80, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Hadir dari siswa Budi Santoso', '2025-03-04 14:33:30'),
(81, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Budi Santoso pada tanggal 04/03/2025', '2025-03-04 14:35:21'),
(82, 'siswa', 2, 'update', 'Siswa mengubah profil', '2025-03-04 14:36:44'),
(83, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Budi Santoso pada tanggal 04/03/2025', '2025-03-04 15:44:10'),
(84, 'admin', 1, 'update', 'Admin mengedit absensi Budi Santoso tanggal 04/03/2025', '2025-03-04 15:45:00'),
(85, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Hadir dari siswa Budi Santoso', '2025-03-04 15:45:13'),
(86, 'admin', 1, 'update', 'Admin mengedit absensi Budi Santoso tanggal 04/03/2025', '2025-03-04 16:09:53'),
(87, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Ahmad Fadilah pada tanggal 04/03/2025', '2025-03-04 16:10:02'),
(88, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-05 08:21:39'),
(89, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-05 08:21:59'),
(90, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Ahmad Fadilah pada tanggal 05/03/2025', '2025-03-05 08:38:31'),
(91, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Cindy Amelia pada tanggal 05/03/2025', '2025-03-05 08:39:06'),
(92, 'admin', 1, 'update', 'Admin mengedit absensi Ahmad Fadilah tanggal 04/03/2025', '2025-03-05 08:39:38'),
(93, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Ahmad Fadilah pada tanggal 05/03/2025', '2025-03-05 08:40:00'),
(94, 'admin', 1, 'update', 'Admin mengedit absensi Ahmad Fadilah tanggal 04/03/2025', '2025-03-05 08:40:15'),
(95, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Ahmad Fadilah pada tanggal 04/03/2025', '2025-03-05 08:40:36'),
(96, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Hana Safira pada tanggal 05/03/2025', '2025-03-05 08:41:16'),
(97, 'admin', 1, 'update', 'Admin mengedit absensi Hana Safira tanggal 05/03/2025', '2025-03-05 08:42:10'),
(98, 'admin', 1, 'update', 'Admin mengedit absensi Hana Safira tanggal 05/03/2025', '2025-03-05 08:42:24'),
(99, 'admin', 1, 'update', 'Admin mengedit absensi Cindy Amelia tanggal 05/03/2025', '2025-03-05 08:42:33'),
(100, 'admin', 1, 'update', 'Admin mengedit absensi Cindy Amelia tanggal 05/03/2025', '2025-03-05 08:43:53'),
(101, 'admin', 1, 'update', 'Admin mengedit absensi Cindy Amelia tanggal 05/03/2025', '2025-03-05 08:44:08'),
(102, 'admin', 1, 'update', 'Admin mengedit absensi Cindy Amelia tanggal 05/03/2025', '2025-03-05 08:44:29'),
(103, 'siswa', 2, 'logout', 'Siswa Budi Santoso logout dari sistem', '2025-03-05 10:00:57'),
(104, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-05 10:14:50'),
(105, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 10:15:13'),
(106, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Hadir dari siswa Budi Santoso', '2025-03-05 10:15:31'),
(107, 'admin', 1, 'update', 'Admin mengedit absensi Budi Santoso tanggal 04/03/2025', '2025-03-05 10:16:02'),
(108, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Izin', '2025-03-05 10:16:23'),
(109, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Sakit dari siswa Budi Santoso', '2025-03-05 10:16:29'),
(110, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Izin dari siswa Budi Santoso', '2025-03-05 10:16:30'),
(111, 'siswa', 2, 'logout', 'Siswa Budi Santoso logout dari sistem', '2025-03-05 10:35:48'),
(112, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-05 10:36:11'),
(113, 'siswa', 2, 'logout', 'Siswa Budi Santoso logout dari sistem', '2025-03-05 11:14:35'),
(114, 'siswa', 2, 'login', 'Siswa Budi Santoso melakukan login', '2025-03-05 11:14:55'),
(115, 'siswa', 2, 'logout', 'Siswa Budi Santoso logout dari sistem', '2025-03-05 11:43:06'),
(116, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-05 11:43:53'),
(117, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2025-03-05 11:44:17'),
(118, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-05 11:44:20'),
(119, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Budi Santoso pada tanggal 05/03/2025', '2025-03-05 11:47:01'),
(120, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Nina Amalia pada tanggal 05/03/2025', '2025-03-05 11:48:28'),
(121, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 12:12:06'),
(122, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-05 12:20:52'),
(123, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 13:27:57'),
(124, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-05 13:28:03'),
(125, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 13:44:40'),
(126, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Hadir dari siswa Budi Santoso', '2025-03-05 13:44:51'),
(127, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 13:54:33'),
(128, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-05 13:54:38'),
(129, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 13:55:23'),
(130, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Hadir dari siswa Budi Santoso', '2025-03-05 13:55:30'),
(131, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 14:15:01'),
(132, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-05 14:15:10'),
(133, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 14:15:20'),
(134, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Hadir dari siswa Budi Santoso', '2025-03-05 14:15:26'),
(135, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-05 14:15:45'),
(136, 'siswa', 2, 'delete', 'Siswa Budi Santoso membatalkan pengajuan absensi', '2025-03-05 14:15:48'),
(137, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-06 00:41:10'),
(138, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-06 00:41:20'),
(139, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-06 00:41:30'),
(140, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Hadir dari siswa Budi Santoso', '2025-03-06 00:41:41'),
(141, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-06 00:58:19'),
(142, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Hadir dari siswa Budi Santoso', '2025-03-06 00:58:30'),
(143, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Hadir dari siswa Budi Santoso', '2025-03-06 01:04:38'),
(144, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Sakit dari siswa Cindy Amelia', '2025-03-06 01:04:44'),
(145, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Hadir', '2025-03-06 01:04:56'),
(146, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Hadir dari siswa Budi Santoso', '2025-03-06 01:05:07'),
(147, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Budi Santoso pada tanggal 06/03/2025', '2025-03-06 01:18:30'),
(148, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-06 12:09:25'),
(149, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-07 15:13:59'),
(150, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-07 15:14:33'),
(151, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-08 08:22:44'),
(152, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Indra Kusuma pada tanggal 08/03/2025', '2025-03-08 08:33:57'),
(153, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Indra Kusuma pada tanggal 08/03/2025', '2025-03-08 08:52:51'),
(154, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-08 16:44:42'),
(155, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2025-03-09 05:35:50'),
(156, 'siswa', 2, 'login', 'Siswa Budi Santoso login ke sistem', '2025-03-09 06:43:10'),
(157, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan Izin dari siswa Fani Azahra', '2025-03-09 07:39:09'),
(158, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Luna Sari pada tanggal 09/03/2025', '2025-03-09 07:39:57'),
(159, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-09 07:41:39'),
(160, 'admin', 1, 'update', 'Admin mengubah profil', '2025-03-09 07:41:48'),
(161, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Sakit', '2025-03-09 07:43:02'),
(162, 'admin', 1, 'approval', 'Admin Administrator menolak pengajuan Sakit dari siswa Budi Santoso', '2025-03-09 07:43:43'),
(163, 'siswa', 2, 'absensi', 'Siswa Budi Santoso mengajukan absensi sebagai Sakit', '2025-03-09 07:44:00'),
(164, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-03-31 06:27:36'),
(165, 'admin', 1, 'create', 'Admin menambahkan absensi Hadir untuk Ahmad Fadilah pada tanggal 31/03/2026', '2026-03-31 06:27:58'),
(166, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-03-31 06:30:38'),
(167, 'admin', 1, 'delete', 'Admin menghapus data siswa: Ahmad Fadilah (2023001)', '2026-03-31 06:31:00'),
(168, 'admin', 1, 'delete', 'Admin menghapus data siswa: Qori Hidayat (2023002)', '2026-03-31 06:31:02'),
(169, 'admin', 1, 'delete', 'Admin menghapus data siswa: Rama Putra (2023003)', '2026-03-31 06:31:05'),
(170, 'admin', 1, 'delete', 'Admin menghapus data siswa: Budi Santoso (2024002)', '2026-03-31 06:31:08'),
(171, 'admin', 1, 'delete', 'Admin menghapus data siswa: Cindy Amelia (2024003)', '2026-03-31 06:31:11'),
(172, 'admin', 1, 'delete', 'Admin menghapus data siswa: Diana Putri (2024004)', '2026-03-31 06:31:13'),
(173, 'admin', 1, 'delete', 'Admin menghapus data siswa: Eko Prasetyo (2024005)', '2026-03-31 06:31:17'),
(174, 'admin', 1, 'delete', 'Admin menghapus data siswa: Fani Azahra (2024006)', '2026-03-31 06:31:19'),
(175, 'admin', 1, 'delete', 'Admin menghapus data siswa: Galih Pratama (2024007)', '2026-03-31 06:31:21'),
(176, 'admin', 1, 'delete', 'Admin menghapus data siswa: Hana Safira (2024008)', '2026-03-31 06:31:23'),
(177, 'admin', 1, 'delete', 'Admin menghapus data siswa: Indra Kusuma (2024009)', '2026-03-31 06:31:25'),
(178, 'admin', 1, 'delete', 'Admin menghapus data siswa: Jasmine Putri (2024010)', '2026-03-31 06:31:28'),
(179, 'admin', 1, 'delete', 'Admin menghapus data siswa: Kevin Wijaya (2024011)', '2026-03-31 06:31:30'),
(180, 'admin', 1, 'delete', 'Admin menghapus data siswa: Luna Sari (2024012)', '2026-03-31 06:31:32'),
(181, 'admin', 1, 'delete', 'Admin menghapus data siswa: Mario Teguh (2024013)', '2026-03-31 06:31:34'),
(182, 'admin', 1, 'delete', 'Admin menghapus data siswa: Nina Amalia (2024014)', '2026-03-31 06:31:37'),
(183, 'admin', 1, 'delete', 'Admin menghapus data siswa: Oscar Putra (2024015)', '2026-03-31 06:31:39'),
(184, 'admin', 1, 'create', 'Admin menambahkan siswa baru: Albrisam Durrany I.L.W (11111)', '2026-03-31 06:32:48'),
(185, 'admin', 1, 'update', 'Admin mengedit data siswa: Albrisam Durrany I.L.W (11111)', '2026-03-31 06:33:01'),
(186, 'admin', 1, 'update', 'Admin mengubah profil', '2026-03-31 06:43:12'),
(187, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-02 05:57:53'),
(188, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2026-04-02 06:02:22'),
(189, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-02 06:03:43'),
(190, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-02 06:07:56'),
(191, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Hadir', '2026-04-02 06:08:43'),
(192, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-02 06:11:36'),
(193, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Sakit', '2026-04-02 06:26:58'),
(194, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-02 06:35:46'),
(195, 'admin', 1, 'delete', 'Admin menghapus absensi Sakit untuk Albrisam Durrany I.L.W pada tanggal 02/04/2026', '2026-04-02 06:36:11'),
(196, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-02 06:37:46'),
(197, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Sakit', '2026-04-02 06:40:13'),
(198, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-02 06:42:18'),
(199, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-02 06:49:17'),
(200, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-02 06:49:51'),
(201, 'admin', 1, 'create', 'Admin menambahkan absensi Sakit untuk Albrisam Durrany I.L.W pada tanggal 02/04/2026', '2026-04-02 06:50:34'),
(202, 'admin', 1, 'delete', 'Admin menghapus absensi Sakit untuk Albrisam Durrany I.L.W pada tanggal 02/04/2026', '2026-04-02 06:50:38'),
(203, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai ', '2026-04-02 06:51:09'),
(204, 'admin', 1, 'approval', 'Admin menyetujui absensi untuk Albrisam Durrany I.L.W dengan status Sakit', '2026-04-02 06:56:26'),
(205, 'admin', 1, 'update', 'Admin mengedit absensi Albrisam Durrany I.L.W tanggal 02/04/2026', '2026-04-02 06:58:31'),
(206, 'admin', 1, 'create', 'Admin menambahkan siswa baru: Ardhi Muhammad (22222)', '2026-04-02 07:02:45'),
(207, 'siswa', 20, 'logout', 'Siswa Albrisam Durrany I.L.W logout dari sistem', '2026-04-02 07:02:53'),
(208, 'siswa', 21, 'login', 'Siswa Ardhi Muhammad login ke sistem', '2026-04-02 07:03:04'),
(209, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai ', '2026-04-02 07:03:24'),
(210, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-02 07:04:40'),
(211, 'admin', 1, 'approval', 'Admin menyetujui absensi untuk Ardhi Muhammad dengan status Hadir', '2026-04-02 07:04:56'),
(212, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Ardhi Muhammad pada tanggal 02/04/2026', '2026-04-02 07:09:22'),
(213, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai ', '2026-04-02 07:22:12'),
(214, 'siswa', 21, 'delete', 'Siswa Ardhi Muhammad membatalkan pengajuan absensi', '2026-04-02 07:23:47'),
(215, 'admin', 1, 'delete', 'Admin menghapus absensi Sakit untuk Albrisam Durrany I.L.W pada tanggal 02/04/2026', '2026-04-02 07:25:46'),
(216, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai ', '2026-04-02 07:26:52'),
(217, 'admin', 1, 'approval', 'Admin menyetujui absensi untuk Ardhi Muhammad dengan status Hadir', '2026-04-02 07:27:06'),
(218, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Ardhi Muhammad pada tanggal 02/04/2026', '2026-04-02 07:27:21'),
(219, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai ', '2026-04-02 07:32:16'),
(220, 'admin', 1, 'approval', 'Admin menyetujui absensi untuk Ardhi Muhammad dengan status Terlambat', '2026-04-02 07:33:52'),
(221, 'admin', 1, 'delete', 'Admin menghapus absensi Terlambat untuk Ardhi Muhammad pada tanggal 02/04/2026', '2026-04-02 07:34:33'),
(222, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai ', '2026-04-02 07:34:50'),
(223, 'admin', 1, 'approval', 'Admin menyetujui absensi untuk Ardhi Muhammad dengan status Alpha', '2026-04-02 07:35:43'),
(224, 'admin', 1, 'update', 'Admin mengedit absensi Ardhi Muhammad tanggal 02/04/2026', '2026-04-02 07:42:05'),
(225, 'siswa', 21, 'logout', 'Siswa Ardhi Muhammad logout dari sistem', '2026-04-02 07:42:52'),
(226, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-02 07:43:39'),
(227, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-02 07:46:55'),
(228, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai ', '2026-04-02 07:47:27'),
(229, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-02 07:51:42'),
(230, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-07 02:00:48'),
(231, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-07 02:06:18'),
(232, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai ', '2026-04-07 02:06:27'),
(233, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 07/04/2026', '2026-04-07 02:10:02'),
(234, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Hadir', '2026-04-07 02:23:42'),
(235, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-07 02:24:53'),
(236, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Hadir (disetujui otomatis)', '2026-04-07 02:36:27'),
(237, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Albrisam Durrany I.L.W pada tanggal 07/04/2026', '2026-04-07 02:37:46'),
(238, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Hadir (disetujui otomatis)', '2026-04-07 02:39:33'),
(239, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Albrisam Durrany I.L.W pada tanggal 07/04/2026', '2026-04-07 02:43:01'),
(240, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-10 03:14:58'),
(241, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-10 03:27:13'),
(242, 'siswa', 20, 'logout', 'Siswa Albrisam Durrany I.L.W logout dari sistem', '2026-04-10 03:33:26'),
(243, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-10 03:33:46'),
(244, 'siswa', 20, 'update', 'Siswa mengubah profil', '2026-04-10 03:34:20'),
(245, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-10 03:35:41'),
(246, 'admin', 1, 'update', 'Admin mengedit data siswa: Albrisam Durrany I.L.W (11111)', '2026-04-10 03:35:58'),
(247, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 03:37:22'),
(248, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-10 03:39:50'),
(249, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-10 03:41:06'),
(250, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 03:41:21'),
(251, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-10 03:41:33'),
(252, 'siswa', 20, 'logout', 'Siswa Albrisam Durrany I.L.W logout dari sistem', '2026-04-10 03:41:55'),
(253, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-10 03:42:08'),
(254, 'admin', 1, 'update', 'Admin mengedit data siswa: Ardhi Muhammad (22222)', '2026-04-10 03:42:34'),
(255, 'siswa', 21, 'login', 'Siswa Ardhi Muhammad login ke sistem', '2026-04-10 03:43:31'),
(256, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 03:43:50'),
(257, 'admin', 1, 'update', 'Admin mengedit absensi Ardhi Muhammad tanggal 10/04/2026', '2026-04-10 03:44:58'),
(258, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 03:47:46'),
(259, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 03:50:01'),
(260, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Ardhi Muhammad pada tanggal 10/04/2026', '2026-04-10 03:50:13'),
(261, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 03:50:26'),
(262, 'siswa', 21, 'delete', 'Siswa Ardhi Muhammad membatalkan pengajuan absensi', '2026-04-10 03:52:51'),
(263, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 03:53:03'),
(264, 'admin', 1, 'approval', 'Admin menolak absensi  untuk Ardhi Muhammad', '2026-04-10 04:05:54'),
(265, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Ardhi Muhammad pada tanggal 10/04/2026', '2026-04-10 04:05:58'),
(266, 'siswa', 21, 'absensi', 'Siswa Ardhi Muhammad mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 04:06:26'),
(267, 'admin', 1, 'update', 'Admin mengedit absensi Ardhi Muhammad tanggal 10/04/2026', '2026-04-10 04:07:36'),
(268, 'siswa', 21, 'logout', 'Siswa Ardhi Muhammad logout dari sistem', '2026-04-10 04:09:52'),
(269, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-10 04:09:55'),
(270, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 04:10:46'),
(271, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-10 04:11:01'),
(272, 'admin', 1, 'approval', 'Admin menyetujui absensi  untuk Albrisam Durrany I.L.W', '2026-04-10 04:11:52'),
(273, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 04:11:56'),
(274, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2026-04-10 04:13:20'),
(275, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-10 04:13:32'),
(276, 'admin', 1, 'update', 'Admin mengubah profil', '2026-04-10 04:14:13'),
(277, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-10 04:14:28'),
(278, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 04:27:43'),
(279, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-10 04:27:47'),
(280, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 04:27:54'),
(281, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 04:28:44'),
(282, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 04:30:06'),
(283, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-10 05:27:24'),
(284, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-10 05:27:58'),
(285, 'admin', 1, 'approval', 'Admin menyetujui absensi  untuk Albrisam Durrany I.L.W', '2026-04-10 05:28:22'),
(286, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Ardhi Muhammad pada tanggal 10/04/2026', '2026-04-10 05:28:29'),
(287, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 05:28:31'),
(288, 'admin', 1, 'create', 'Admin menambahkan siswa baru: Administrator (33333)', '2026-04-10 05:30:28'),
(289, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 05:46:00'),
(290, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-10 05:46:04'),
(291, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 05:46:11'),
(292, 'admin', 1, 'update', 'Admin mengedit absensi Albrisam Durrany I.L.W tanggal 10/04/2026', '2026-04-10 05:46:30'),
(293, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 05:50:23'),
(294, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 05:50:32'),
(295, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 05:52:42'),
(296, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 05:52:55'),
(297, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-10 05:53:41'),
(298, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 06:16:14'),
(299, 'siswa', 20, 'logout', 'Siswa Albrisam Durrany I.L.W logout dari sistem', '2026-04-10 06:21:10'),
(300, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-10 06:21:15'),
(301, 'siswa', 20, 'logout', 'Siswa Albrisam Durrany I.L.W logout dari sistem', '2026-04-10 06:21:22'),
(302, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-10 06:21:25'),
(303, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-10 06:21:32'),
(304, 'admin', 1, 'update', 'Admin mengedit absensi Albrisam Durrany I.L.W tanggal 10/04/2026', '2026-04-10 06:33:11'),
(305, 'admin', 1, 'update', 'Admin mengedit absensi Albrisam Durrany I.L.W tanggal 10/04/2026', '2026-04-10 06:33:32'),
(306, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 06:33:39'),
(307, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 06:37:53'),
(308, 'admin', 1, 'update', 'Admin mengedit absensi Albrisam Durrany I.L.W tanggal 10/04/2026', '2026-04-10 06:38:15'),
(309, 'admin', 1, 'approval', 'Admin menyetujui absensi  untuk Albrisam Durrany I.L.W', '2026-04-10 06:38:20'),
(310, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 06:38:27'),
(311, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 06:39:23'),
(312, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 06:39:39'),
(313, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 06:42:32'),
(314, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk Albrisam Durrany I.L.W pada tanggal 10/04/2026', '2026-04-10 06:42:48'),
(315, 'siswa', 20, 'absensi', 'Siswa Albrisam Durrany I.L.W mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-10 06:42:57'),
(316, 'siswa', 20, 'delete', 'Siswa Albrisam Durrany I.L.W membatalkan pengajuan absensi', '2026-04-10 06:43:12'),
(317, 'siswa', 20, 'login', 'Siswa Albrisam Durrany I.L.W login ke sistem', '2026-04-14 00:56:10'),
(318, 'siswa', 20, 'logout', 'Siswa Albrisam Durrany I.L.W logout dari sistem', '2026-04-14 00:56:20'),
(319, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-14 00:56:25'),
(320, 'admin', 1, 'delete', 'Admin menghapus data siswa: Administrator (33333)', '2026-04-14 00:56:37'),
(321, 'admin', 1, 'delete', 'Admin menghapus data siswa: Ardhi Muhammad (22222)', '2026-04-14 00:56:39'),
(322, 'admin', 1, 'delete', 'Admin menghapus data siswa: Albrisam Durrany I.L.W (11111)', '2026-04-14 00:56:41'),
(323, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 35 baru, 0 diupdate', '2026-04-14 01:01:08'),
(324, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 0 baru, 35 diupdate', '2026-04-14 01:02:36'),
(325, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 0 baru, 35 diupdate', '2026-04-14 01:04:19'),
(326, 'admin', 1, 'delete', 'Admin menghapus seluruh data siswa Kelas 10 RPL (Total: 35 siswa)', '2026-04-14 01:07:18'),
(327, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 35 baru, 0 diupdate', '2026-04-14 01:07:24'),
(328, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 0 baru, 35 diupdate', '2026-04-14 02:00:27'),
(329, 'admin', 1, 'delete', 'Admin menghapus seluruh data siswa Kelas 10 RPL (Total: 35 siswa)', '2026-04-14 02:00:37'),
(330, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 36 baru, 0 diupdate', '2026-04-14 02:01:21'),
(331, 'admin', 1, 'update', 'Admin mengedit data siswa: ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO (25/2868)', '2026-04-14 02:04:40'),
(332, 'siswa', 97, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-04-14 02:05:22'),
(333, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-14 02:06:12'),
(334, 'admin', 1, 'approval', 'Admin menyetujui absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO', '2026-04-14 02:06:28'),
(335, 'admin', 1, 'update', 'Admin mengedit data siswa: JESSICA JOELYN MAHARANI (25/2885)', '2026-04-14 02:16:40'),
(336, 'admin', 1, 'update', 'Admin mengedit data siswa: JESSICA JOELYN MAHARANI (25/2885)', '2026-04-14 02:21:10'),
(337, 'admin', 1, 'update', 'Admin mengedit data siswa: ARDHI MUHAMMAD (25/2870)', '2026-04-14 02:23:35'),
(338, 'siswa', 97, 'logout', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO logout dari sistem', '2026-04-14 02:23:38'),
(339, 'siswa', 99, 'login', 'Siswa ARDHI MUHAMMAD login ke sistem', '2026-04-14 02:23:42'),
(340, 'siswa', 99, 'absensi', 'Siswa ARDHI MUHAMMAD mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-14 02:25:14'),
(341, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-14 02:25:19'),
(342, 'admin', 1, 'update', 'Admin mengedit absensi ARDHI MUHAMMAD tanggal 14/04/2026', '2026-04-14 02:26:08'),
(343, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO pada tanggal 14/04/2026', '2026-04-14 02:32:38'),
(344, 'admin', 1, 'delete', 'Admin menghapus absensi Hadir untuk ARDHI MUHAMMAD pada tanggal 14/04/2026', '2026-04-14 02:32:41'),
(345, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2026-04-14 02:38:21'),
(346, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-14 02:38:23'),
(347, 'admin', 1, 'update', 'Admin mengedit data siswa: ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO (25/2868)', '2026-04-14 02:39:34'),
(348, 'siswa', 97, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-04-14 02:39:58'),
(349, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-14 02:40:31'),
(350, 'admin', 1, 'update', 'Admin mengedit absensi ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO tanggal 14/04/2026', '2026-04-14 02:40:45'),
(351, 'admin', 1, 'delete', 'Admin menghapus absensi Izin untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO pada tanggal 14/04/2026', '2026-04-14 02:43:59'),
(352, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-14 02:44:06'),
(353, 'admin', 1, 'approval', 'Admin menyetujui absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO', '2026-04-14 02:45:20'),
(354, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO pada tanggal 14/04/2026', '2026-04-14 02:48:28'),
(355, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-14 02:49:04'),
(356, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-17 03:32:04'),
(357, 'admin', 1, 'update', 'Admin mengedit data siswa: ARDHI MUHAMMAD (25/2870)', '2026-04-17 03:32:48'),
(358, 'siswa', 99, 'login', 'Siswa ARDHI MUHAMMAD login ke sistem', '2026-04-17 03:32:53'),
(359, 'siswa', 99, 'absensi', 'Siswa ARDHI MUHAMMAD mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-17 03:33:12'),
(360, 'admin', 1, 'approval', 'Admin menyetujui absensi  untuk ARDHI MUHAMMAD', '2026-04-17 03:33:37'),
(361, 'admin', 1, 'update', 'Admin mengedit absensi ARDHI MUHAMMAD tanggal 17/04/2026', '2026-04-17 03:34:33'),
(362, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan  dari siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO', '2026-04-17 03:56:23'),
(363, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2026-04-17 04:49:44'),
(364, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-17 04:49:50'),
(365, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2026-04-17 04:55:25'),
(366, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-17 04:56:41'),
(367, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-17 04:57:03'),
(368, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2026-04-17 05:26:20'),
(369, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-17 05:26:21'),
(370, 'admin', 1, 'delete', 'Admin menghapus absensi Izin untuk ARDHI MUHAMMAD pada tanggal 17/04/2026', '2026-04-17 05:40:55'),
(371, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-04-24 02:43:33'),
(372, 'siswa', 97, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-04-24 02:47:38'),
(373, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-24 02:48:13'),
(374, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO pada tanggal 24/04/2026', '2026-04-24 02:55:29'),
(375, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-24 02:55:43'),
(376, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO pada tanggal 24/04/2026', '2026-04-24 02:57:26'),
(377, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-24 02:57:52'),
(378, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO pada tanggal 24/04/2026', '2026-04-24 03:00:23'),
(379, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-24 03:00:41'),
(380, 'admin', 1, 'approval', 'Admin Administrator menyetujui pengajuan  dari siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO', '2026-04-24 03:02:03'),
(381, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO pada tanggal 24/04/2026', '2026-04-24 03:06:03'),
(382, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-04-24 03:06:17'),
(383, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-05-05 04:14:01'),
(384, 'admin', 1, 'create', 'Admin menambahkan siswa baru: erfeerf (3333)', '2026-05-05 05:05:27'),
(385, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-05-11 05:17:45'),
(386, 'siswa', 97, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-05-11 05:19:48'),
(387, 'siswa', 97, 'delete', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO membatalkan pengajuan absensi', '2026-05-11 05:19:59'),
(388, 'siswa', 97, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-05-11 05:20:11'),
(389, 'admin', 1, 'delete', 'Admin menghapus absensi  untuk ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO pada tanggal 11/05/2026', '2026-05-11 07:21:09'),
(390, 'siswa', 97, 'logout', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO logout dari sistem', '2026-05-11 07:29:53'),
(391, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-05-11 07:30:06'),
(392, 'siswa', 97, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-05-11 07:30:18'),
(393, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 0 baru, 36 diupdate', '2026-05-11 07:38:04'),
(394, 'admin', 1, 'delete', 'Admin menghapus seluruh data siswa Kelas 10 RPL (Total: 36 siswa)', '2026-05-11 07:38:15'),
(395, 'admin', 1, 'delete', 'Admin menghapus data siswa: erfeerf (3333)', '2026-05-11 07:38:19'),
(396, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 36 baru, 0 diupdate', '2026-05-11 07:38:25'),
(397, 'admin', 1, 'delete', 'Admin menghapus seluruh data siswa Kelas 10 RPL (Total: 36 siswa)', '2026-05-11 07:40:04'),
(398, 'admin', 1, 'create', 'Admin mengimpor data siswa Excel Kelas 10 RPL: 36 baru, 0 diupdate', '2026-05-11 07:40:09'),
(399, 'siswa', 97, 'logout', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO logout dari sistem', '2026-05-11 07:40:28'),
(400, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-05-11 07:40:51'),
(401, 'admin', 1, 'update', 'Admin mengedit data siswa: ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO (25/2868)', '2026-05-11 07:41:23'),
(402, 'siswa', 170, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-05-11 07:41:45'),
(403, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-05-22 02:00:47'),
(404, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2026-05-22 02:05:57'),
(405, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-05-22 02:06:02'),
(406, 'siswa', 170, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-05-22 02:07:21'),
(407, 'admin', 1, 'update', 'Admin mengubah profil', '2026-05-22 03:58:08'),
(408, 'admin', 1, 'update', 'Admin mengubah profil', '2026-05-22 04:00:02'),
(409, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-06-17 04:16:30'),
(410, 'siswa', 170, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-06-17 04:17:08'),
(411, 'siswa', 170, 'absensi', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO mengajukan absensi sebagai Menunggu (menunggu persetujuan admin)', '2026-06-17 04:17:10'),
(412, 'siswa', 170, 'delete', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO membatalkan pengajuan absensi', '2026-06-17 04:17:28'),
(413, 'siswa', 170, 'logout', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO logout dari sistem', '2026-06-17 04:21:34'),
(414, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-06-17 04:21:37'),
(415, 'siswa', 170, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-06-17 04:23:04'),
(416, 'siswa', 170, 'logout', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO logout dari sistem', '2026-06-17 04:36:50'),
(417, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-06-17 04:36:54'),
(418, 'admin', 1, 'logout', 'Admin Administrator logout dari sistem', '2026-06-17 04:37:00'),
(419, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-06-17 04:37:28'),
(420, 'siswa', 170, 'login', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO login ke sistem', '2026-06-17 04:37:41'),
(421, 'siswa', 170, 'logout', 'Siswa ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO logout dari sistem', '2026-06-17 04:42:13'),
(422, 'admin', 1, 'login', 'Admin Administrator login ke sistem', '2026-06-17 04:42:15');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `foto_profil` varchar(255) DEFAULT 'assets/default/photo-profile.png',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `email`, `password`, `nama_lengkap`, `foto_profil`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@smkn1-sanden.sch.id', 'admin123', 'Administrator', 'uploads/admin/admin_1_1779422402.jpg', '2026-06-17 11:42:15', '2025-03-03 08:23:13', '2026-06-17 04:42:15');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `kelas` enum('10','11','12') NOT NULL,
  `jurusan` enum('RPL','DKV','AK','BR','MP') NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_profil` varchar(255) DEFAULT 'assets/default/photo-profile.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nama_lengkap`, `jenis_kelamin`, `kelas`, `jurusan`, `email`, `password`, `foto_profil`, `created_at`, `updated_at`) VALUES
(166, '25/2864', 'ADEK KIKI HERAWATI', 'P', '10', 'RPL', '252864@siswa.smkn1sanden.sch.id', 'siswa_252864', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(167, '25/2865', 'ADELIA SILVY DWI CAHYANI', 'P', '10', 'RPL', '252865@siswa.smkn1sanden.sch.id', 'siswa_252865', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(168, '25/2866', 'AFIYAN RAMADAN', 'L', '10', 'RPL', '252866@siswa.smkn1sanden.sch.id', 'siswa_252866', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(169, '25/2867', 'AKHID KHUSNIANTO', 'L', '10', 'RPL', '252867@siswa.smkn1sanden.sch.id', 'siswa_252867', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(170, '25/2868', 'ALBRISAM DURRANY ISLAMEY LISTIYO WIBOWO', 'L', '10', 'RPL', '252868@siswa.smkn1sanden.sch.id', 'admin123', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:41:23'),
(171, '25/2869', 'ALFRIAN MUHAMAD SAPUTRA', 'L', '10', 'RPL', '252869@siswa.smkn1sanden.sch.id', 'siswa_252869', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(172, '25/2870', 'ARDHI MUHAMMAD', 'L', '10', 'RPL', '252870@siswa.smkn1sanden.sch.id', 'siswa_252870', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(173, '25/2871', 'ARUM TRI AMBARWATI', 'P', '10', 'RPL', '252871@siswa.smkn1sanden.sch.id', 'siswa_252871', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(174, '25/2872', 'AULIA PUTRI MUSTOFA', 'P', '10', 'RPL', '252872@siswa.smkn1sanden.sch.id', 'siswa_252872', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(175, '25/2873', 'AZIZA ROSIHANNA FATIMAH', 'P', '10', 'RPL', '252873@siswa.smkn1sanden.sch.id', 'siswa_252873', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(176, '25/2874', 'DANISH PUTRA LESMANA', 'L', '10', 'RPL', '252874@siswa.smkn1sanden.sch.id', 'siswa_252874', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(177, '25/2875', 'DHISYA AZZAHRA MAULIDA MAHARANI', 'P', '10', 'RPL', '252875@siswa.smkn1sanden.sch.id', 'siswa_252875', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(178, '25/2876', 'EARENCA MAULA ANUGRAH SAPUTRA', 'L', '10', 'RPL', '252876@siswa.smkn1sanden.sch.id', 'siswa_252876', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(179, '25/2877', 'FARHAN ADE SETIAWAN', 'L', '10', 'RPL', '252877@siswa.smkn1sanden.sch.id', 'siswa_252877', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(180, '25/2878', 'FARIED AHMAD FEBRIYANTO', 'L', '10', 'RPL', '252878@siswa.smkn1sanden.sch.id', 'siswa_252878', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(181, '25/2879', 'FATURRACHMAN NUR ADITYA', 'L', '10', 'RPL', '252879@siswa.smkn1sanden.sch.id', 'siswa_252879', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(182, '25/2880', 'FAVIAN ARYA DANASURA', 'L', '10', 'RPL', '252880@siswa.smkn1sanden.sch.id', 'siswa_252880', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(183, '25/2881', 'FERDINA RISKY PANGESTU', 'L', '10', 'RPL', '252881@siswa.smkn1sanden.sch.id', 'siswa_252881', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(184, '25/2882', 'FINSA DWI WIDYANINGTYAS', 'P', '10', 'RPL', '252882@siswa.smkn1sanden.sch.id', 'siswa_252882', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(185, '25/2883', 'IKRAM MAULANA', 'L', '10', 'RPL', '252883@siswa.smkn1sanden.sch.id', 'siswa_252883', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(186, '25/2884', 'ILHAM DAMAR JATI', 'L', '10', 'RPL', '252884@siswa.smkn1sanden.sch.id', 'siswa_252884', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(187, '25/2885', 'JESSICA JOELYN MAHARANI', 'P', '10', 'RPL', '252885@siswa.smkn1sanden.sch.id', 'siswa_252885', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(188, '25/2886', 'MELA AZ - ZAHRA MAULIDA', 'P', '10', 'RPL', '252886@siswa.smkn1sanden.sch.id', 'siswa_252886', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(189, '25/2887', 'NABIL RIF\'ANNDARU', 'L', '10', 'RPL', '252887@siswa.smkn1sanden.sch.id', 'siswa_252887', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(190, '25/2888', 'NAZWA AGUSTINA RAMADHANI', 'P', '10', 'RPL', '252888@siswa.smkn1sanden.sch.id', 'siswa_252888', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(191, '25/2889', 'NISA LATIFA FAWZIAH', 'P', '10', 'RPL', '252889@siswa.smkn1sanden.sch.id', 'siswa_252889', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(192, '25/2890', 'PARADITYA ZAYAN GUMELAR', 'L', '10', 'RPL', '252890@siswa.smkn1sanden.sch.id', 'siswa_252890', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(193, '25/2891', 'RAKA SATYATMA WIDODO', 'L', '10', 'RPL', '252891@siswa.smkn1sanden.sch.id', 'siswa_252891', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(194, '25/2892', 'REVA DWI PRASETIA', 'L', '10', 'RPL', '252892@siswa.smkn1sanden.sch.id', 'siswa_252892', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(195, '25/2893', 'RISKI NUR ARDIYANSAH', 'L', '10', 'RPL', '252893@siswa.smkn1sanden.sch.id', 'siswa_252893', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(196, '25/2894', 'RISMADEA NOLLENCIA BELLA', 'P', '10', 'RPL', '252894@siswa.smkn1sanden.sch.id', 'siswa_252894', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(197, '25/2895', 'SANDIANITA NIRINDRA WIJAYANTI', 'P', '10', 'RPL', '252895@siswa.smkn1sanden.sch.id', 'siswa_252895', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(198, '25/2896', 'TALITHA NUR SALSABILA', 'P', '10', 'RPL', '252896@siswa.smkn1sanden.sch.id', 'siswa_252896', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(199, '25/2897', 'ZAHRA TUSYITA', 'P', '10', 'RPL', '252897@siswa.smkn1sanden.sch.id', 'siswa_252897', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(200, '25/2898', 'ZHARFANI FA AYYIN', 'P', '10', 'RPL', '252898@siswa.smkn1sanden.sch.id', 'siswa_252898', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09'),
(201, '25/2899', 'ZUDHAN SATRIA WICAKSANA', 'L', '10', 'RPL', '252899@siswa.smkn1sanden.sch.id', 'siswa_252899', 'assets/default/photo-profile.png', '2026-05-11 07:40:09', '2026-05-11 07:40:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tanggal` (`tanggal`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_type` (`user_type`,`user_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=423;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absensi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
