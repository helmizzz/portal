-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.15.0.7171
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for portalrev
CREATE DATABASE IF NOT EXISTS `portalrev` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `portalrev`;

-- Dumping structure for table portalrev.activity_logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `activity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `document_id` int DEFAULT NULL,
  `folder_id` int DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.activity_logs: ~127 rows (approximately)
INSERT INTO `activity_logs` (`id`, `user_id`, `activity_type`, `description`, `document_id`, `folder_id`, `timestamp`) VALUES
	(1, 1, 'delete_document', 'Admin admin menghapus dokumen: ADAD', 1, NULL, '2026-01-27 00:58:02'),
	(2, 1, 'delete_document', 'Admin admin menghapus dokumen: DOC-001-IT', 2, NULL, '2026-01-27 00:58:16'),
	(3, 1, 'delete_document', 'Admin admin menghapus dokumen: DOC-002-IT', 3, NULL, '2026-01-27 00:58:22'),
	(4, 1, 'delete_document', 'Admin admin menghapus dokumen: ADAD', 6, NULL, '2026-01-27 00:58:26'),
	(5, 1, 'delete_document', 'Admin admin menghapus dokumen: DOC-001-IT', 4, NULL, '2026-01-27 00:58:38'),
	(6, 1, 'delete_document', 'Admin admin menghapus dokumen: DOC-002-IT', 5, NULL, '2026-01-27 00:58:42'),
	(7, 1, 'delete_document', 'Admin admin menghapus dokumen: DOC-123-IT', 7, NULL, '2026-01-27 08:01:04'),
	(10, 1, 'delete_document', 'Admin admin menghapus dokumen: DAD-090-IT', 9, NULL, '2026-01-27 13:17:07'),
	(11, 1, 'delete_document', 'Admin admin menghapus dokumen: ADAD', 13, NULL, '2026-01-27 16:04:54'),
	(12, 1, 'delete_document', 'Admin admin menghapus dokumen: asd', 14, NULL, '2026-01-27 16:04:56'),
	(13, 1, 'delete_document', 'Admin admin menghapus dokumen: DOC-123-IT', 10, NULL, '2026-01-27 16:04:59'),
	(14, 1, 'delete_document', 'Admin admin menghapus dokumen: ADAD', 12, NULL, '2026-01-27 16:05:05'),
	(15, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2030', NULL, NULL, '2026-01-28 03:20:25'),
	(16, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2029', NULL, NULL, '2026-01-28 03:20:30'),
	(17, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2029', NULL, NULL, '2026-01-28 03:20:33'),
	(18, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2029', NULL, NULL, '2026-01-28 03:20:36'),
	(19, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2029', NULL, NULL, '2026-01-28 03:20:39'),
	(20, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2028', NULL, NULL, '2026-01-28 03:20:41'),
	(21, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2028', NULL, NULL, '2026-01-28 03:20:44'),
	(22, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2027', NULL, NULL, '2026-01-28 03:20:47'),
	(23, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2025', NULL, NULL, '2026-01-28 03:20:49'),
	(24, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2023', NULL, NULL, '2026-01-28 03:20:52'),
	(25, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2021', NULL, NULL, '2026-01-28 03:20:55'),
	(26, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 15, NULL, '2026-01-28 03:21:36'),
	(27, 1, 'delete_tahun', 'Admin admin menghapus tahun: 10', NULL, NULL, '2026-01-28 03:21:41'),
	(28, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2023', NULL, NULL, '2026-01-28 03:55:34'),
	(29, 1, 'delete_folder', 'Admin admin menghapus folder: ', NULL, NULL, '2026-01-28 04:33:32'),
	(30, 1, 'delete_folder', 'Admin admin menghapus folder: ', NULL, NULL, '2026-01-28 04:34:45'),
	(31, 1, 'delete_document', 'Admin admin menghapus dokumen: sd', 16, NULL, '2026-01-28 04:35:01'),
	(32, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 17, NULL, '2026-01-28 04:35:04'),
	(33, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2021', NULL, NULL, '2026-01-28 04:43:53'),
	(34, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 18, NULL, '2026-01-28 04:44:09'),
	(35, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2021', NULL, NULL, '2026-01-28 04:53:33'),
	(36, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 19, NULL, '2026-01-28 06:14:49'),
	(37, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2024', NULL, NULL, '2026-01-28 06:16:59'),
	(38, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2021', NULL, NULL, '2026-01-28 06:27:56'),
	(39, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 20, NULL, '2026-01-28 06:45:48'),
	(40, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 21, NULL, '2026-01-28 06:46:05'),
	(41, 1, 'delete_document', 'Admin admin menghapus dokumen: DOC-123-IT', 22, NULL, '2026-01-29 02:06:26'),
	(42, 1, 'delete_document', 'Admin admin menghapus dokumen: DOC-123-GA', 23, NULL, '2026-01-29 02:07:01'),
	(43, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 24, NULL, '2026-01-29 02:07:40'),
	(44, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 25, NULL, '2026-01-29 02:13:52'),
	(45, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 26, NULL, '2026-01-29 02:14:01'),
	(46, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 27, NULL, '2026-01-29 02:14:03'),
	(47, 1, 'delete_document', 'Admin admin menghapus dokumen: ad', 28, NULL, '2026-01-29 02:14:07'),
	(48, 1, 'delete_folder', 'Admin admin menghapus folder: HRD (2021)', NULL, NULL, '2026-01-29 02:14:24'),
	(49, 1, 'delete_folder', 'Admin admin menghapus folder: FINANCE (2021)', NULL, NULL, '2026-01-29 02:14:35'),
	(50, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 29, NULL, '2026-01-29 02:15:29'),
	(51, 1, 'delete_folder', 'Admin admin menghapus folder: FINANCE (2021)', NULL, NULL, '2026-01-29 02:15:57'),
	(52, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 30, NULL, '2026-01-29 02:24:21'),
	(53, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 31, NULL, '2026-01-29 02:27:18'),
	(54, 1, 'delete_document', 'Admin admin menghapus dokumen: as', 32, NULL, '2026-01-29 02:30:15'),
	(55, 1, 'delete_folder', 'Admin admin menghapus folder: FINANCE (2021)', NULL, NULL, '2026-01-29 06:09:59'),
	(56, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2021', NULL, NULL, '2026-01-29 06:10:05'),
	(57, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-06 09:29:52'),
	(58, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-06 09:53:18'),
	(59, 1, 'view_document', 'User \'admin\' melihat dokumen \'ab.pdf\'.', NULL, NULL, '2026-02-09 01:36:40'),
	(60, 1, 'view_document', 'User \'admin\' melihat dokumen \'ab.pdf\'.', NULL, NULL, '2026-02-09 01:56:34'),
	(61, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 01:59:02'),
	(62, 1, 'view_document', 'User \'admin\' melihat dokumen \'ab.pdf\'.', NULL, NULL, '2026-02-09 02:11:13'),
	(63, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 02:11:49'),
	(64, 1, 'view_document', 'User \'admin\' melihat dokumen \'ab.pdf\'.', NULL, NULL, '2026-02-09 02:11:59'),
	(65, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 02:12:27'),
	(66, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 08:13:37'),
	(67, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 08:13:39'),
	(68, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 08:13:39'),
	(69, 1, 'view_document', 'User \'admin\' melihat dokumen \'File Finance.pdf\'.', NULL, NULL, '2026-02-09 08:13:39'),
	(70, 1, 'view_document', 'User \'admin\' melihat dokumen \'File Finance.pdf\'.', NULL, NULL, '2026-02-09 08:13:39'),
	(71, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 08:13:40'),
	(72, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 08:13:41'),
	(73, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 08:13:41'),
	(74, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 08:13:46'),
	(75, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-09 08:20:37'),
	(76, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-10 01:17:05'),
	(77, 1, 'view_document', 'User \'admin\' melihat dokumen \'ab.pdf\'.', NULL, NULL, '2026-02-10 04:23:02'),
	(78, 1, 'view_document', 'User \'admin\' melihat dokumen \'ab.pdf\'.', NULL, NULL, '2026-02-10 04:23:05'),
	(79, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-10 04:23:24'),
	(80, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-10 04:25:30'),
	(81, 1, 'view_document', 'User \'admin\' melihat dokumen \'as.pdf\'.', NULL, NULL, '2026-02-10 04:50:17'),
	(82, 1, 'view_document', 'User \'admin\' melihat dokumen \'PROSEDUR PENGENDALIAN HAMA FURNITUR.pdf\'.', NULL, NULL, '2026-02-10 06:40:55'),
	(83, 1, 'delete_folder', 'Admin admin menghapus folder: RDS (2025)', NULL, NULL, '2026-02-10 08:44:23'),
	(84, 1, 'delete_folder', 'Admin admin menghapus folder: RDS (2021)', NULL, NULL, '2026-02-10 08:44:47'),
	(85, 1, 'delete_folder', 'Admin admin menghapus folder: IT (2025)', NULL, NULL, '2026-02-10 08:45:32'),
	(86, 1, 'delete_folder', 'Admin admin menghapus folder: HRD (2025)', NULL, NULL, '2026-02-10 08:45:35'),
	(87, 1, 'delete_folder', 'Admin admin menghapus folder: GA (2025)', NULL, NULL, '2026-02-10 08:45:38'),
	(88, 1, 'delete_folder', 'Admin admin menghapus folder: FINANCE (2025)', NULL, NULL, '2026-02-10 08:46:45'),
	(89, 1, 'delete_folder', 'Admin admin menghapus folder: IT (2021)', NULL, NULL, '2026-02-10 08:47:26'),
	(90, 1, 'delete_folder', 'Admin admin menghapus folder: HRD (2021)', NULL, NULL, '2026-02-10 08:47:29'),
	(91, 1, 'delete_folder', 'Admin admin menghapus folder: GA (2021)', NULL, NULL, '2026-02-10 08:47:33'),
	(92, 1, 'delete_folder', 'Admin admin menghapus folder: FINANCE (2021)', NULL, NULL, '2026-02-10 08:47:36'),
	(93, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:53:16'),
	(94, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:53:35'),
	(95, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:55:13'),
	(96, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:55:23'),
	(97, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:56:09'),
	(98, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:21'),
	(99, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:22'),
	(100, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:22'),
	(101, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:22'),
	(102, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:23'),
	(103, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:23'),
	(104, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:23'),
	(105, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:23'),
	(106, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:24'),
	(107, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:24'),
	(108, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:25'),
	(109, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:25'),
	(110, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:26'),
	(111, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 08:59:31'),
	(112, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 09:00:42'),
	(113, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 09:07:00'),
	(114, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 09:10:51'),
	(115, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-10 09:11:05'),
	(116, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 01:37:47'),
	(117, 5, 'view_document', 'User \'staff02-3\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:14:06'),
	(118, 5, 'view_document', 'User \'staff02-3\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:14:09'),
	(119, 5, 'view_document', 'User \'staff02-3\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:14:14'),
	(120, 5, 'view_document', 'User \'staff02-3\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:14:15'),
	(121, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:17:42'),
	(122, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:19:22'),
	(123, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:26:31'),
	(124, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:26:40'),
	(125, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:26:53'),
	(126, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:27:00'),
	(127, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:27:01'),
	(128, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 03:27:13'),
	(129, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 04:03:01'),
	(130, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG.pdf\'.', NULL, NULL, '2026-02-11 04:03:02'),
	(131, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG.pdf\'.', NULL, NULL, '2026-02-11 04:23:12'),
	(132, 1, 'update_document_file', 'Admin admin memperbarui file untuk dokumen: ', 54, NULL, '2026-02-11 04:30:33'),
	(133, 1, 'view_document', 'User \'admin\' melihat dokumen \'698c05e938eda.pdf\'.', NULL, NULL, '2026-02-11 04:36:27'),
	(134, 1, 'view_document', 'User \'admin\' melihat dokumen \'698c05e938eda.pdf\'.', NULL, NULL, '2026-02-11 04:41:39'),
	(135, 1, 'view_document', 'User \'admin\' melihat dokumen \'698c05e938eda.pdf\'.', NULL, NULL, '2026-02-11 04:41:41'),
	(136, 1, 'view_document', 'User \'admin\' melihat dokumen \'698c05e938eda.pdf\'.', NULL, NULL, '2026-02-11 04:41:42'),
	(137, 1, 'view_document', 'User \'admin\' melihat dokumen \'698c05e938eda.pdf\'.', NULL, NULL, '2026-02-11 04:45:49'),
	(138, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG_1770791249_1770795934.pdf\'.', NULL, NULL, '2026-02-11 10:01:12'),
	(139, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG_1770791249_1770795934.pdf\'.', NULL, NULL, '2026-02-12 03:12:36'),
	(140, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG_1770791249_1770795934.pdf\'.', NULL, NULL, '2026-02-12 03:56:10'),
	(141, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG PANJANG_1770791249_1770795934.pdf\'.', NULL, NULL, '2026-02-19 02:32:50'),
	(142, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2024', NULL, NULL, '2026-02-19 02:50:45'),
	(143, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2023', NULL, NULL, '2026-02-19 02:50:52'),
	(144, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2022', NULL, NULL, '2026-02-19 02:50:55'),
	(145, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2021', NULL, NULL, '2026-02-19 02:50:57'),
	(146, 1, 'delete_tahun', 'Admin admin menghapus tahun: 2024', NULL, NULL, '2026-02-19 03:37:15'),
	(147, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG.pdf\'.', NULL, NULL, '2026-02-19 06:47:45'),
	(148, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG pdf_1771484092.pdf\'.', NULL, NULL, '2026-02-19 06:55:55'),
	(149, 1, 'view_document', 'User \'admin\' melihat dokumen \'INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG.pdf\'.', NULL, NULL, '2026-04-29 06:25:00');

-- Dumping structure for table portalrev.announcements
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `attachment_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_announcements_user` (`created_by`) USING BTREE,
  CONSTRAINT `fk_announcements_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.announcements: ~0 rows (approximately)

-- Dumping structure for table portalrev.carousel_images
CREATE TABLE IF NOT EXISTS `carousel_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `caption` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.carousel_images: ~0 rows (approximately)

-- Dumping structure for table portalrev.departements
CREATE TABLE IF NOT EXISTS `departements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.departements: ~37 rows (approximately)
INSERT INTO `departements` (`id`, `name`) VALUES
	(7, 'Corporate Secretary'),
	(8, 'Corporate Communication'),
	(9, 'Informasi & Technology Development'),
	(10, 'Expert Staff Communication & Organization'),
	(11, 'Expert Staff General Affair & Assignment'),
	(12, 'Corporate Legal'),
	(13, 'Internal Audit'),
	(14, 'Risk Management'),
	(15, 'Finance, Accounting, & Tax Departemen'),
	(16, 'Operation Departement'),
	(17, 'Maintenance'),
	(18, 'Research & Development'),
	(19, 'PPIC'),
	(20, 'Production'),
	(21, 'Wood Working & PP2 Section'),
	(22, 'Sanding & Hardware Section'),
	(23, 'Staining & Finishing Section'),
	(24, 'Weaving Section'),
	(25, 'Upholstrey Section'),
	(26, 'Pre-Assy & Wrapping Section'),
	(27, 'Furniture & Hardware Section'),
	(28, 'Supporting Departement'),
	(29, 'Quality Control'),
	(30, 'Warehouse'),
	(31, 'Logistic'),
	(32, 'Procurement'),
	(33, 'QHSE'),
	(34, 'Sales Departement'),
	(35, 'Global Sales'),
	(36, 'Domestic Sales'),
	(37, 'HRGA'),
	(38, 'Project Beta'),
	(39, 'Project Alpha'),
	(40, 'Komite 6S');

-- Dumping structure for table portalrev.document_departments
CREATE TABLE IF NOT EXISTS `document_departments` (
  `document_id` int NOT NULL,
  `departement_id` int NOT NULL,
  PRIMARY KEY (`document_id`,`departement_id`) USING BTREE,
  KEY `departement_id` (`departement_id`) USING BTREE,
  CONSTRAINT `dd_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `dd_ibfk_2` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.document_departments: ~0 rows (approximately)

-- Dumping structure for table portalrev.document_jenis
CREATE TABLE IF NOT EXISTS `document_jenis` (
  `id_jdoc` int NOT NULL,
  `jenis_doc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_jdoc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table portalrev.document_jenis: ~2 rows (approximately)
INSERT INTO `document_jenis` (`id_jdoc`, `jenis_doc`) VALUES
	(1, 'SOP'),
	(2, 'IK');

-- Dumping structure for table portalrev.document_permissions
CREATE TABLE IF NOT EXISTS `document_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `departement_id` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `document_id` (`document_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `departement_id` (`departement_id`) USING BTREE,
  CONSTRAINT `dp_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `dp_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `dp_ibfk_3` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.document_permissions: ~0 rows (approximately)

-- Dumping structure for table portalrev.document_user_access
CREATE TABLE IF NOT EXISTS `document_user_access` (
  `document_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`document_id`,`user_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `dua_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `dua_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.document_user_access: ~0 rows (approximately)

-- Dumping structure for table portalrev.document_versions
CREATE TABLE IF NOT EXISTS `document_versions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `version_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `document_id` (`document_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `dv_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `dv_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.document_versions: ~0 rows (approximately)
INSERT INTO `document_versions` (`id`, `document_id`, `version_file_name`, `notes`, `uploaded_at`, `user_id`) VALUES
	(1, 2, 'INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG pdf.pdf', 'REV 01', '2026-02-19 06:54:52', 1);

-- Dumping structure for table portalrev.documents
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  `created_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_dept` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tahun` char(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.documents: ~3 rows (approximately)
INSERT INTO `documents` (`id`, `file_code`, `file_name`, `uploaded_at`, `created_by`, `nama_dept`, `tahun`, `file_path`, `updated_at`, `title`, `is_active`) VALUES
	(1, 'BMN-02-3-SOP-2025-001', 'INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG.pdf', '2026-02-19 11:02:32', 'admin', 'Corporate Communication', '2025', '../uploads/2025/Corporate Communication/INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG.pdf', NULL, NULL, 1),
	(2, 'BMN-02-3-SOP-2025-001', 'INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG pdf_1771484092.pdf', '2026-02-19 13:25:17', 'admin', 'Expert Staff General Affair & Assignment', '2025', '../uploads/2025/Expert Staff General Affair & Assignment/INI ADALAH FILE DENGAN JUDUL YANG SANGAT PANJANG pdf_1771484092.pdf', '2026-02-19 13:54:14', NULL, 1),
	(3, 'BMN-02-3-SOP-2025-001', 'Dokumen milik GA.pdf', '2026-05-20 15:45:10', 'admin', 'Internal Audit', '2025', '../uploads/2025/Internal Audit/Dokumen milik GA.pdf', NULL, NULL, 1);

-- Dumping structure for table portalrev.elearning_completion
CREATE TABLE IF NOT EXISTS `elearning_completion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `material_id` int NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_material_unique` (`user_id`,`material_id`) USING BTREE,
  KEY `material_id` (`material_id`) USING BTREE,
  CONSTRAINT `ec_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `ec_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `elearning_materials` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.elearning_completion: ~0 rows (approximately)

-- Dumping structure for table portalrev.elearning_courses
CREATE TABLE IF NOT EXISTS `elearning_courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `share_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `share_token` (`share_token`) USING BTREE,
  KEY `created_by` (`created_by`) USING BTREE,
  CONSTRAINT `elc_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.elearning_courses: ~0 rows (approximately)

-- Dumping structure for table portalrev.elearning_materials
CREATE TABLE IF NOT EXISTS `elearning_materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content_type` enum('text','video') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `video_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `course_id` (`course_id`) USING BTREE,
  CONSTRAINT `elm_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `elearning_courses` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.elearning_materials: ~0 rows (approximately)

-- Dumping structure for table portalrev.events
CREATE TABLE IF NOT EXISTS `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `event_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '#3788d8',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_events_user` (`created_by`) USING BTREE,
  CONSTRAINT `fk_events_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.events: ~0 rows (approximately)

-- Dumping structure for table portalrev.folder
CREATE TABLE IF NOT EXISTS `folder` (
  `id_fold` int DEFAULT NULL,
  `id_dep` int DEFAULT NULL,
  `id_tahun` int DEFAULT NULL,
  `folder_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  KEY `id_dep` (`id_dep`) USING BTREE,
  KEY `id_tahun` (`id_tahun`) USING BTREE,
  CONSTRAINT `folder_ibfk_1` FOREIGN KEY (`id_dep`) REFERENCES `departements` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `folder_ibfk_2` FOREIGN KEY (`id_tahun`) REFERENCES `tahun` (`id_tahun`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.folder: ~34 rows (approximately)
INSERT INTO `folder` (`id_fold`, `id_dep`, `id_tahun`, `folder_path`) VALUES
	(1, 7, 1, '../uploads/2025/Corporate Secretary'),
	(2, 8, 1, '../uploads/2025/Corporate Communication'),
	(3, 9, 1, '../uploads/2025/Informasi & Technology Development'),
	(4, 10, 1, '../uploads/2025/Expert Staff Communication & Organization'),
	(5, 11, 1, '../uploads/2025/Expert Staff General Affair & Assignment'),
	(6, 12, 1, '../uploads/2025/Corporate Legal'),
	(7, 13, 1, '../uploads/2025/Internal Audit'),
	(8, 14, 1, '../uploads/2025/Risk Management'),
	(9, 15, 1, '../uploads/2025/Finance, Accounting, & Tax Departemen'),
	(10, 16, 1, '../uploads/2025/Operation Departement'),
	(11, 17, 1, '../uploads/2025/Maintenance'),
	(12, 18, 1, '../uploads/2025/Research & Development'),
	(13, 19, 1, '../uploads/2025/PPIC'),
	(14, 20, 1, '../uploads/2025/Production'),
	(15, 21, 1, '../uploads/2025/Wood Working & PP2 Section'),
	(16, 22, 1, '../uploads/2025/Sanding & Hardware Section'),
	(17, 23, 1, '../uploads/2025/Staining & Finishing Section'),
	(18, 24, 1, '../uploads/2025/Weaving Section'),
	(19, 25, 1, '../uploads/2025/Upholstrey Section'),
	(20, 26, 1, '../uploads/2025/Pre-Assy & Wrapping Section'),
	(21, 27, 1, '../uploads/2025/Furniture & Hardware Section'),
	(22, 28, 1, '../uploads/2025/Supporting Departement'),
	(23, 29, 1, '../uploads/2025/Quality Control'),
	(24, 30, 1, '../uploads/2025/Warehouse'),
	(25, 31, 1, '../uploads/2025/Logistic'),
	(26, 32, 1, '../uploads/2025/Procurement'),
	(27, 33, 1, '../uploads/2025/QHSE'),
	(28, 34, 1, '../uploads/2025/Sales Departement'),
	(29, 35, 1, '../uploads/2025/Global Sales'),
	(30, 36, 1, '../uploads/2025/Domestic Sales'),
	(31, 37, 1, '../uploads/2025/HRGA'),
	(32, 38, 1, '../uploads/2025/Project Beta'),
	(33, 39, 1, '../uploads/2025/Project Alpha'),
	(34, 40, 1, '../uploads/2025/Komite 6S');

-- Dumping structure for table portalrev.folder_departments
CREATE TABLE IF NOT EXISTS `folder_departments` (
  `folder_id` int NOT NULL,
  `departement_id` int NOT NULL,
  PRIMARY KEY (`folder_id`,`departement_id`) USING BTREE,
  KEY `departement_id` (`departement_id`) USING BTREE,
  CONSTRAINT `fd_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fd_ibfk_2` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.folder_departments: ~0 rows (approximately)

-- Dumping structure for table portalrev.folder_user_access
CREATE TABLE IF NOT EXISTS `folder_user_access` (
  `folder_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`folder_id`,`user_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `fua_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fua_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.folder_user_access: ~0 rows (approximately)

-- Dumping structure for table portalrev.folders
CREATE TABLE IF NOT EXISTS `folders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parent_id` int DEFAULT NULL,
  `created_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE,
  CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.folders: ~0 rows (approximately)

-- Dumping structure for table portalrev.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.notifications: ~2 rows (approximately)
INSERT INTO `notifications` (`id`, `user_id`, `message`, `link`, `is_read`, `created_at`) VALUES
	(1, 4, 'Dokumen \'\' telah diperbarui.', 'dashboard.php?doc_id=54', 0, '2026-02-11 04:30:33'),
	(2, 5, 'Dokumen \'\' telah diperbarui.', 'dashboard.php?doc_id=54', 1, '2026-02-11 04:30:33');

-- Dumping structure for table portalrev.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `permission_name` (`permission_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.permissions: ~10 rows (approximately)
INSERT INTO `permissions` (`id`, `permission_name`, `description`) VALUES
	(1, 'manage_documents', 'Dapat menambah, mengedit, dan menghapus dokumen/folder.'),
	(2, 'manage_users', 'Dapat menambah, mengedit, dan menghapus pengguna.'),
	(3, 'manage_roles', 'Dapat mengelola peran dan hak aksesnya.'),
	(4, 'view_all_documents', 'Dapat melihat semua dokumen, terlepas dari departemen.'),
	(5, 'view_audit_trail', 'Dapat melihat halaman jejak audit (audit trail).'),
	(6, 'view_analytics', 'Dapat melihat halaman laporan dan analitik.'),
	(7, 'manage_events', 'Dapat menambah, mengedit, dan menghapus acara di kalender.'),
	(8, 'manage_polls', 'Dapat membuat, mengedit, dan melihat hasil polling atau survei.'),
	(9, 'manage_elearning', 'Dapat membuat, mengedit, dan menghapus kursus e-learning beserta materinya.'),
	(10, 'view_elearning_report', 'Dapat melihat laporan penyelesaian kursus e-learning.');

-- Dumping structure for table portalrev.poll_options
CREATE TABLE IF NOT EXISTS `poll_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `question_id` (`question_id`) USING BTREE,
  CONSTRAINT `po_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `poll_questions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.poll_options: ~0 rows (approximately)

-- Dumping structure for table portalrev.poll_questions
CREATE TABLE IF NOT EXISTS `poll_questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `poll_id` int NOT NULL,
  `question_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `poll_id` (`poll_id`) USING BTREE,
  CONSTRAINT `pq_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.poll_questions: ~0 rows (approximately)

-- Dumping structure for table portalrev.poll_responses
CREATE TABLE IF NOT EXISTS `poll_responses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `poll_id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_vote` (`poll_id`,`question_id`,`ip_address`) USING BTREE,
  KEY `poll_id` (`poll_id`) USING BTREE,
  KEY `question_id` (`question_id`) USING BTREE,
  KEY `option_id` (`option_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `pr_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pr_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `poll_questions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pr_ibfk_3` FOREIGN KEY (`option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `pr_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.poll_responses: ~0 rows (approximately)

-- Dumping structure for table portalrev.polls
CREATE TABLE IF NOT EXISTS `polls` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` enum('active','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `share_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `share_token` (`share_token`) USING BTREE,
  KEY `created_by` (`created_by`) USING BTREE,
  CONSTRAINT `polls_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.polls: ~0 rows (approximately)

-- Dumping structure for table portalrev.public_announcements
CREATE TABLE IF NOT EXISTS `public_announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `announcement_id` int NOT NULL,
  `token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `token` (`token`) USING BTREE,
  KEY `announcement_id` (`announcement_id`) USING BTREE,
  CONSTRAINT `fk_pa_id` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.public_announcements: ~0 rows (approximately)

-- Dumping structure for table portalrev.role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`) USING BTREE,
  KEY `permission_id` (`permission_id`) USING BTREE,
  CONSTRAINT `rp_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `rp_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.role_permissions: ~13 rows (approximately)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(1, 6),
	(1, 7),
	(1, 8),
	(1, 9),
	(1, 10),
	(2, 1),
	(3, 1),
	(3, 4),
	(3, 6);

-- Dumping structure for table portalrev.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `role_name` (`role_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.roles: ~3 rows (approximately)
INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
	(1, 'Admin', 'Akses penuh ke semua fitur manajemen.'),
	(2, 'Staff', 'Akses dasar untuk melihat dokumen.'),
	(3, 'Manager', 'Dapat melihat semua dokumen dan laporan, tapi tidak bisa mengubah user.');

-- Dumping structure for table portalrev.shared_links
CREATE TABLE IF NOT EXISTS `shared_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `token` (`token`) USING BTREE,
  KEY `document_id` (`document_id`) USING BTREE,
  KEY `fk_shared_links_created_by` (`created_by`) USING BTREE,
  CONSTRAINT `sl_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `sl_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.shared_links: ~0 rows (approximately)
INSERT INTO `shared_links` (`id`, `document_id`, `token`, `password`, `expires_at`, `created_by`, `created_at`) VALUES
	(1, 54, '58470faa7564d0e7768ee9a202838135', '$2y$10$hBrsxmYN2tC2bp5a9gNqruHt1Txkm7.Yecydb7iyxPWc3wubtAmW2', '2026-02-11 14:15:00', 1, '2026-02-11 06:15:23');

-- Dumping structure for table portalrev.shortcuts
CREATE TABLE IF NOT EXISTS `shortcuts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.shortcuts: ~6 rows (approximately)
INSERT INTO `shortcuts` (`id`, `name`, `url`, `icon_class`, `sort_order`, `created_at`) VALUES
	(1, 'Email', 'https://mail.hostinger.com/', 'fas fa-envelope', 10, '2025-08-06 08:27:11'),
	(2, 'Office 365', 'https://www.office.com/', 'fas fa-file-word', 20, '2025-08-06 08:27:11'),
	(3, 'ERP', 'http://hris.berdikarimeubel.com/odoo', 'fas fa-chart-bar', 30, '2025-08-06 08:27:11'),
	(4, 'HRIS', '#', 'fas fa-users', 40, '2025-08-06 08:27:11'),
	(5, 'Helpdesk', '#', 'fas fa-headset', 50, '2025-08-06 08:27:11'),
	(6, 'Lainnya', '#', 'fas fa-cogs', 60, '2025-08-06 08:27:11');

-- Dumping structure for table portalrev.tahun
CREATE TABLE IF NOT EXISTS `tahun` (
  `id_tahun` int NOT NULL,
  `tahun` char(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `folder_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id_tahun`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.tahun: ~0 rows (approximately)
INSERT INTO `tahun` (`id_tahun`, `tahun`, `folder_path`) VALUES
	(1, '2025', '../uploads/2025');

-- Dumping structure for table portalrev.user_favorite_documents
CREATE TABLE IF NOT EXISTS `user_favorite_documents` (
  `user_id` int NOT NULL,
  `document_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`document_id`) USING BTREE,
  KEY `document_id` (`document_id`) USING BTREE,
  CONSTRAINT `ufd_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `ufd_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.user_favorite_documents: ~0 rows (approximately)
INSERT INTO `user_favorite_documents` (`user_id`, `document_id`) VALUES
	(1, 54);

-- Dumping structure for table portalrev.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_id` int DEFAULT '2',
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `departement_id` int DEFAULT NULL,
  `theme` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'light',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  KEY `users_ibfk_2` (`role_id`) USING BTREE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table portalrev.users: ~3 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `role_id`, `profile_picture`, `is_active`, `departement_id`, `theme`) VALUES
	(1, 'admin', '$2y$10$ZO4FvxFpRaR749wsXkyXBeJGrx4YQ4hBgYkgAbcvghUZ2Mvtk2r/C', 1, NULL, 1, NULL, 'light'),
	(4, 'staff02', '$2y$10$.6IRupkmvn/EyrCXZUiRbujKEX.zDnMF4RTKLWxjQaJCEIcUmX/CO', 2, NULL, 1, 7, 'light'),
	(5, 'staff02-3', '$2y$10$7Uvi1EX3aBYwfI2EKM6cEOYstMwlG9UGyNOuqzh/ia9AXwVYUfnJi', 2, NULL, 1, 10, 'light');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
