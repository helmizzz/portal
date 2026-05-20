-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2025 at 05:53 PM
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
-- Database: `pdfviewer`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `document_id` int(11) DEFAULT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity_type`, `description`, `document_id`, `folder_id`, `timestamp`) VALUES
(1, 1, 'create_folder', 'Admin admin membuat folder baru: Public', NULL, 1, '2025-08-02 14:22:42'),
(2, 1, 'create_folder', 'Admin admin membuat folder baru: Public', NULL, 1, '2025-08-03 02:40:49'),
(3, 1, 'create_folder', 'Admin admin membuat folder baru: IT', NULL, 2, '2025-08-03 02:41:05'),
(4, 1, 'upload_document', 'Admin admin mengunggah dokumen baru: SOP Penangan Masalah User', 1, NULL, '2025-08-03 02:41:37'),
(5, 1, 'create_folder', 'Admin admin membuat folder baru: GA', NULL, 3, '2025-08-03 05:12:44'),
(6, 1, 'create_folder', 'Admin admin membuat folder baru: HRD', NULL, 4, '2025-08-03 05:22:03'),
(7, 1, 'create_folder', 'Admin admin membuat folder: Public', NULL, 1, '2025-08-03 06:48:23'),
(8, 1, 'create_folder', 'Admin admin membuat folder: IT', NULL, 2, '2025-08-03 06:48:32'),
(9, 1, 'upload_document', 'Admin admin mengunggah dokumen: SOP Penangan Masalah User', 2, NULL, '2025-08-03 06:54:07'),
(10, 1, 'create_folder', 'Admin admin membuat folder: GA', NULL, 3, '2025-08-03 12:14:53'),
(11, 1, 'create_folder', 'Admin admin membuat folder: HRD', NULL, 4, '2025-08-03 12:15:02'),
(12, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 14:49:15'),
(13, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 14:49:45'),
(14, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 14:50:14'),
(15, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 14:51:19'),
(16, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 14:51:50'),
(17, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 14:54:14'),
(18, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 14:58:02'),
(19, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 14:58:39'),
(20, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:05:30'),
(21, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:06:29'),
(22, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:07:06'),
(23, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:10:32'),
(24, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:10:55'),
(25, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:12:29'),
(26, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:12:46'),
(27, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:14:52'),
(28, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:15:14'),
(29, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:15:38'),
(30, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:15:58'),
(31, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:20:49'),
(32, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:21:12'),
(33, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:28:43'),
(34, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:30:04'),
(35, 1, 'update_document_file', 'Admin admin memperbarui file untuk dokumen: SOP Penangan Masalah User', 2, NULL, '2025-08-03 15:39:48'),
(36, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 15:40:03'),
(37, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 16:58:36'),
(38, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 00:59:43'),
(39, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 01:00:14'),
(40, 1, 'upload_document', 'Admin admin mengunggah: SOP Kendaraan', 3, NULL, '2025-08-04 01:30:21'),
(41, 2, 'view_document', 'User \'budi\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-04 01:32:19'),
(42, 2, 'view_document', 'User \'budi\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-04 01:32:39'),
(43, 2, 'view_document', 'User \'budi\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 01:32:43'),
(44, 2, 'view_document', 'User \'budi\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-04 01:33:13'),
(45, 2, 'view_document', 'User \'budi\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-04 01:33:41'),
(46, 2, 'attempt_right_click', 'User \'budi\' mencoba melakukan klik kanan saat melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-04 01:34:13'),
(47, 2, 'attempt_right_click', 'User \'budi\' mencoba melakukan klik kanan saat melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-04 01:34:14'),
(48, 2, 'attempt_right_click', 'User \'budi\' mencoba melakukan klik kanan saat melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-04 01:34:15'),
(49, 2, 'attempt_right_click', 'User \'budi\' mencoba melakukan klik kanan saat melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-04 01:34:16'),
(50, 2, 'view_document', 'User \'budi\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 04:54:25'),
(51, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 13:17:37'),
(52, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 13:57:14'),
(53, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-04 14:43:36'),
(54, 1, 'edit_announcement', 'Admin \'admin\' mengedit pengumuman: \'Hari Libur Nasional\' (ID: 1)', NULL, NULL, '2025-08-04 15:07:44'),
(55, 1, 'edit_announcement', 'Admin \'admin\' mengedit pengumuman: \'Hari Libur Nasional\' (ID: 1)', NULL, NULL, '2025-08-04 15:08:26'),
(56, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-04 15:20:50'),
(57, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-04 15:40:15'),
(58, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'Pengumuman Olahraga Hari Jumat\'', NULL, NULL, '2025-08-04 15:47:45'),
(59, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Pengumuman Olahraga Hari Jumat\'', NULL, NULL, '2025-08-04 15:47:50'),
(60, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'Lorem Ipsum\'', NULL, NULL, '2025-08-04 16:00:35'),
(61, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Lorem Ipsum\'', NULL, NULL, '2025-08-04 16:00:44'),
(62, 1, 'create_folder', 'Admin admin membuat folder: Departemen', NULL, 5, '2025-08-05 01:07:37'),
(63, 1, 'create_folder', 'Admin admin membuat folder: GA', NULL, 6, '2025-08-05 01:07:50'),
(64, 1, 'create_folder', 'Admin admin membuat folder: IT', NULL, 7, '2025-08-05 01:30:14'),
(65, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'Ini pengumuman\'', NULL, NULL, '2025-08-05 02:16:35'),
(66, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Ini pengumuman\'', NULL, NULL, '2025-08-05 02:16:42'),
(67, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-05 02:31:13'),
(68, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 07:46:03'),
(69, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 07:54:10'),
(70, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 08:21:13'),
(71, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-05 08:21:19'),
(72, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 12:06:17'),
(73, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 13:05:54'),
(74, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 13:19:20'),
(75, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 13:19:26'),
(76, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 13:20:58'),
(77, 1, 'upload_document', 'Admin admin mengunggah: SOP Asset IT', 4, NULL, '2025-08-05 13:30:49'),
(78, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Asset IT\'.', NULL, NULL, '2025-08-05 13:31:07'),
(79, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Lorem Ipsum\'', NULL, NULL, '2025-08-05 15:45:41');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `attachment_file` varchar(255) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `attachment_file`, `start_date`, `end_date`, `created_by`, `created_at`, `is_pinned`) VALUES
(1, 'Hari Libur Nasional', '<p>Memperingati hari libur nasional yang bertepatan pada 18 Agustus di nyatakan libur tambahan</p>', '', '2025-08-01 22:07:00', '2025-08-18 22:07:00', 1, '2025-08-04 14:43:36', 1),
(2, 'Pengumuman Olahraga Hari Jumat', '<p>olahraga woey</p>', NULL, '2025-08-01 22:47:00', '2025-08-31 22:47:00', 1, '2025-08-04 15:47:45', 1),
(3, 'Lorem Ipsum', '<p>\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"</p>', 'attachment_1754323235_6890d923a8627.jpg', '2025-08-01 22:59:00', '2025-08-31 23:00:00', 1, '2025-08-04 16:00:35', 1),
(4, 'Ini pengumuman', '<p>coba pengumuman</p>', NULL, '2025-08-05 09:16:00', '2025-08-05 15:22:00', 1, '2025-08-05 02:16:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `carousel_images`
--

CREATE TABLE `carousel_images` (
  `id` int(11) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carousel_images`
--

INSERT INTO `carousel_images` (`id`, `image_name`, `caption`) VALUES
(1, '688f4d4a32223.png', '1'),
(2, '688f4d54612a9.jpg', '2');

-- --------------------------------------------------------

--
-- Table structure for table `departements`
--

CREATE TABLE `departements` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departements`
--

INSERT INTO `departements` (`id`, `name`) VALUES
(1, 'IT'),
(2, 'HRD'),
(3, 'GA'),
(4, 'Finance');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `folder_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `file_name`, `folder_id`) VALUES
(2, 'SOP Penangan Masalah User', '688f82c4b6bcb.pdf', 2),
(3, 'SOP Kendaraan', '68900d2d31658.pdf', 3),
(4, 'SOP Asset IT', '689207895b564.pdf', 2);

-- --------------------------------------------------------

--
-- Table structure for table `document_departments`
--

CREATE TABLE `document_departments` (
  `document_id` int(11) NOT NULL,
  `departement_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_permissions`
--

CREATE TABLE `document_permissions` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `departement_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_user_access`
--

CREATE TABLE `document_user_access` (
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `version_file_name` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL COMMENT 'Catatan atau ringkasan perubahan untuk versi ini',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_versions`
--

INSERT INTO `document_versions` (`id`, `document_id`, `version_file_name`, `notes`, `uploaded_at`, `user_id`) VALUES
(1, 2, '688f078fd8a6b.pdf', 'Kesalahan 1', '2025-08-03 12:26:15', 1),
(2, 2, '688f5567c1fe8.pdf', 'Kesalahan 2', '2025-08-03 12:40:22', 1),
(3, 2, '688f58b6e0471.pdf', 'Kesalahan 3', '2025-08-03 12:58:57', 1),
(4, 2, '688f5d114626f.pdf', 'Kesalahan 4', '2025-08-03 13:00:27', 1),
(5, 2, '688f5d6b43cd6.pdf', 'Kesalahan 5', '2025-08-03 13:12:38', 1),
(6, 2, '688f6046f24eb.pdf', 'revisi terkait perubahan flowchart', '2025-08-03 15:39:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `name`, `parent_id`) VALUES
(1, 'Public', NULL),
(2, 'IT', 1),
(3, 'GA', 1),
(4, 'HRD', 1),
(5, 'Departemen', NULL),
(6, 'GA', 5),
(7, 'IT', 5);

-- --------------------------------------------------------

--
-- Table structure for table `folder_departments`
--

CREATE TABLE `folder_departments` (
  `folder_id` int(11) NOT NULL,
  `departement_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `folder_user_access`
--

CREATE TABLE `folder_user_access` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 2, 'Dokumen baru \'SOP Penangan Masalah User\' telah ditambahkan.', 'dashboard.php?doc_id=2', 1, '2025-08-03 06:54:07'),
(2, 3, 'Dokumen baru \'SOP Penangan Masalah User\' telah ditambahkan.', 'dashboard.php?doc_id=2', 0, '2025-08-03 06:54:07'),
(3, 4, 'Dokumen baru \'SOP Penangan Masalah User\' telah ditambahkan.', 'dashboard.php?doc_id=2', 0, '2025-08-03 06:54:07'),
(4, 2, 'Dokumen \'SOP Penangan Masalah User\' telah diperbarui.', 'dashboard.php?doc_id=2', 1, '2025-08-03 15:39:48'),
(5, 3, 'Dokumen \'SOP Penangan Masalah User\' telah diperbarui.', 'dashboard.php?doc_id=2', 0, '2025-08-03 15:39:48'),
(6, 4, 'Dokumen \'SOP Penangan Masalah User\' telah diperbarui.', 'dashboard.php?doc_id=2', 0, '2025-08-03 15:39:48'),
(7, 2, 'Dokumen baru \'SOP Kendaraan\' telah ditambahkan.', 'dashboard.php?doc_id=3', 1, '2025-08-04 01:30:21'),
(8, 3, 'Dokumen baru \'SOP Kendaraan\' telah ditambahkan.', 'dashboard.php?doc_id=3', 0, '2025-08-04 01:30:21'),
(9, 4, 'Dokumen baru \'SOP Kendaraan\' telah ditambahkan.', 'dashboard.php?doc_id=3', 0, '2025-08-04 01:30:21'),
(10, 2, 'Dokumen baru \'SOP Asset IT\' telah ditambahkan.', 'dashboard.php?doc_id=4', 0, '2025-08-05 13:30:49'),
(11, 3, 'Dokumen baru \'SOP Asset IT\' telah ditambahkan.', 'dashboard.php?doc_id=4', 0, '2025-08-05 13:30:49'),
(12, 4, 'Dokumen baru \'SOP Asset IT\' telah ditambahkan.', 'dashboard.php?doc_id=4', 0, '2025-08-05 13:30:49');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(255) NOT NULL COMMENT 'e.g., manage_users, view_documents',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`, `description`) VALUES
(1, 'manage_documents', 'Dapat menambah, mengedit, dan menghapus dokumen/folder.'),
(2, 'manage_users', 'Dapat menambah, mengedit, dan menghapus pengguna.'),
(3, 'manage_roles', 'Dapat mengelola peran dan hak aksesnya.'),
(4, 'view_all_documents', 'Dapat melihat semua dokumen, terlepas dari departemen.'),
(5, 'view_audit_trail', 'Dapat melihat halaman jejak audit (audit trail).'),
(6, 'view_analytics', 'Dapat melihat halaman laporan dan analitik.');

-- --------------------------------------------------------

--
-- Table structure for table `public_announcements`
--

CREATE TABLE `public_announcements` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `token` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `public_announcements`
--

INSERT INTO `public_announcements` (`id`, `announcement_id`, `token`, `created_at`) VALUES
(1, 3, '1fb4f23755c06e5f5b7daf95899174bf', '2025-08-05 02:08:19'),
(2, 4, '220873ba311560e002c2b7bd3f74d6a1', '2025-08-05 13:33:54');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
(1, 'Admin', 'Akses penuh ke semua fitur manajemen.'),
(2, 'User', 'Akses dasar untuk melihat dokumen.'),
(3, 'Manager', 'Dapat melihat semua dokumen dan laporan, tapi tidak bisa mengubah user.');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(3, 4),
(3, 6);

-- --------------------------------------------------------

--
-- Table structure for table `shared_links`
--

CREATE TABLE `shared_links` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shared_links`
--

INSERT INTO `shared_links` (`id`, `document_id`, `token`, `password`, `expires_at`, `created_by`, `created_at`) VALUES
(1, 3, 'c0f86866f6e908e66654e2cb7f756bc7', '$2y$10$PlR34LFOBP17CgwxdNLKmeXny1nRTudH6ffej1FmPgliIGHlOJGzi', '2025-08-04 11:12:00', 1, '2025-08-04 04:09:32'),
(2, 2, '4479cbae4374d89c1fbb0455d4d83771', '$2y$10$1JCbUM/pZgrV1.zTKBFAde0Ob4mJuo1VUjdrqUgxewZh9TXqSRqm2', '2025-08-04 11:18:00', 1, '2025-08-04 04:14:22');

-- --------------------------------------------------------

--
-- Table structure for table `shortcuts`
--

CREATE TABLE `shortcuts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon_class` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shortcuts`
--

INSERT INTO `shortcuts` (`id`, `name`, `url`, `icon_class`, `sort_order`) VALUES
(1, 'Email', 'https://mail.google.com', 'fas fa-envelope', 10),
(2, 'Office 365', 'https://www.office.com/', 'fas fa-file-word', 20),
(3, 'ERP', '#', 'fas fa-chart-bar', 30),
(4, 'HRIS', '#', 'fas fa-users', 40),
(5, 'Helpdesk', '#', 'fas fa-headset', 50),
(6, 'Lainnya', '#', 'fas fa-cogs', 60);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT 2,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Nonaktif',
  `departement_id` int(11) DEFAULT NULL,
  `theme` varchar(10) NOT NULL DEFAULT 'light' COMMENT 'Preferensi tema: light atau dark'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role_id`, `profile_picture`, `is_active`, `departement_id`, `theme`) VALUES
(1, 'admin', '$2y$10$jGPmmxQuHet6DRr1bVOBkejmNTV2OL9SMs1vHkU8w/fZ8NrE1CI/u', 1, 'user_1_1754316834.jpg', 1, NULL, 'light'),
(2, 'budi', '$2y$10$mrd6PrXP9ccVNwfe6zCrt.grgyfsLrDp/ji4VFvPfYDSdhqSk/YjO', 2, NULL, 1, 1, 'light'),
(3, 'siti', '$2y$10$GnYvbqHJCFJZA2j9SGUvju/O6vzDu7rGXXl4tMxcdgNX2xmluHS32', 2, NULL, 1, 2, 'light'),
(4, 'haryo', '$2y$10$vdsdJO68.IYS6Q4N57cMqu9Wdir1LH.7uhMC.Icv5MpWLRsrYIX6.', 2, NULL, 1, 3, 'light');

-- --------------------------------------------------------

--
-- Table structure for table `user_favorite_documents`
--

CREATE TABLE `user_favorite_documents` (
  `user_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_favorite_documents`
--

INSERT INTO `user_favorite_documents` (`user_id`, `document_id`) VALUES
(1, 2),
(1, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_announcements_user` (`created_by`);

--
-- Indexes for table `carousel_images`
--
ALTER TABLE `carousel_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Indexes for table `document_departments`
--
ALTER TABLE `document_departments`
  ADD PRIMARY KEY (`document_id`,`departement_id`),
  ADD KEY `departement_id` (`departement_id`);

--
-- Indexes for table `document_permissions`
--
ALTER TABLE `document_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `departement_id` (`departement_id`);

--
-- Indexes for table `document_user_access`
--
ALTER TABLE `document_user_access`
  ADD PRIMARY KEY (`document_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `folder_departments`
--
ALTER TABLE `folder_departments`
  ADD PRIMARY KEY (`folder_id`,`departement_id`),
  ADD KEY `departement_id` (`departement_id`);

--
-- Indexes for table `folder_user_access`
--
ALTER TABLE `folder_user_access`
  ADD PRIMARY KEY (`folder_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `public_announcements`
--
ALTER TABLE `public_announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `announcement_id` (`announcement_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `shared_links`
--
ALTER TABLE `shared_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `fk_shared_links_created_by` (`created_by`);

--
-- Indexes for table `shortcuts`
--
ALTER TABLE `shortcuts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `users_ibfk_2` (`role_id`);

--
-- Indexes for table `user_favorite_documents`
--
ALTER TABLE `user_favorite_documents`
  ADD PRIMARY KEY (`user_id`,`document_id`),
  ADD KEY `document_id` (`document_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `carousel_images`
--
ALTER TABLE `carousel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departements`
--
ALTER TABLE `departements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `document_permissions`
--
ALTER TABLE `document_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `public_announcements`
--
ALTER TABLE `public_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shared_links`
--
ALTER TABLE `shared_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shortcuts`
--
ALTER TABLE `shortcuts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcements_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_departments`
--
ALTER TABLE `document_departments`
  ADD CONSTRAINT `dd_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dd_ibfk_2` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_permissions`
--
ALTER TABLE `document_permissions`
  ADD CONSTRAINT `document_permissions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_permissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_permissions_ibfk_3` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_user_access`
--
ALTER TABLE `document_user_access`
  ADD CONSTRAINT `dua_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dua_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_versions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `folder_departments`
--
ALTER TABLE `folder_departments`
  ADD CONSTRAINT `fd_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fd_ibfk_2` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `folder_user_access`
--
ALTER TABLE `folder_user_access`
  ADD CONSTRAINT `fua_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fua_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `public_announcements`
--
ALTER TABLE `public_announcements`
  ADD CONSTRAINT `fk_public_ann_id` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shared_links`
--
ALTER TABLE `shared_links`
  ADD CONSTRAINT `fk_shared_links_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_shared_links_document_id` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_favorite_documents`
--
ALTER TABLE `user_favorite_documents`
  ADD CONSTRAINT `user_favorite_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorite_documents_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
