/*
 Navicat Premium Data Transfer

 Source Server         : testtanpapas
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : pdfviewer1

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 22/01/2026 08:32:10
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for activity_logs
-- ----------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `activity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `document_id` int NULL DEFAULT NULL,
  `folder_id` int NULL DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 252 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of activity_logs
-- ----------------------------
BEGIN;
INSERT INTO `activity_logs` (`id`, `user_id`, `activity_type`, `description`, `document_id`, `folder_id`, `timestamp`) VALUES (1, 1, 'create_folder', 'Admin admin membuat folder baru: Public', NULL, 1, '2025-08-02 21:22:42'), (2, 1, 'create_folder', 'Admin admin membuat folder baru: Public', NULL, 1, '2025-08-03 09:40:49'), (3, 1, 'create_folder', 'Admin admin membuat folder baru: IT', NULL, 2, '2025-08-03 09:41:05'), (4, 1, 'upload_document', 'Admin admin mengunggah dokumen baru: SOP Penangan Masalah User', 1, NULL, '2025-08-03 09:41:37'), (5, 1, 'create_folder', 'Admin admin membuat folder baru: GA', NULL, 3, '2025-08-03 12:12:44'), (6, 1, 'create_folder', 'Admin admin membuat folder baru: HRD', NULL, 4, '2025-08-03 12:22:03'), (7, 1, 'create_folder', 'Admin admin membuat folder: Public', NULL, 1, '2025-08-03 13:48:23'), (8, 1, 'create_folder', 'Admin admin membuat folder: IT', NULL, 2, '2025-08-03 13:48:32'), (9, 1, 'upload_document', 'Admin admin mengunggah dokumen: SOP Penangan Masalah User', 2, NULL, '2025-08-03 13:54:07'), (10, 1, 'create_folder', 'Admin admin membuat folder: GA', NULL, 3, '2025-08-03 19:14:53'), (11, 1, 'create_folder', 'Admin admin membuat folder: HRD', NULL, 4, '2025-08-03 19:15:02'), (12, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 21:49:15'), (13, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 21:49:45'), (14, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 21:50:14'), (15, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 21:51:19'), (16, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 21:51:50'), (17, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 21:54:14'), (18, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 21:58:02'), (19, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 21:58:39'), (20, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:05:30'), (21, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:06:29'), (22, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:07:06'), (23, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:10:32'), (24, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:10:55'), (25, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:12:29'), (26, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:12:46'), (27, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:14:52'), (28, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:15:14'), (29, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:15:38'), (30, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:15:58'), (31, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:20:49'), (32, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:21:12'), (33, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:28:43'), (34, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:30:04'), (35, 1, 'update_document_file', 'Admin admin memperbarui file untuk dokumen: SOP Penangan Masalah User', 2, NULL, '2025-08-03 22:39:48'), (36, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 22:40:03'), (37, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-03 23:58:36'), (38, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 07:59:43'), (39, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 08:00:14'), (40, 1, 'upload_document', 'Admin admin mengunggah: SOP Kendaraan', 3, NULL, '2025-08-04 08:30:21'), (51, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 20:17:37'), (52, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-04 20:57:14'), (53, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-04 21:43:36'), (54, 1, 'edit_announcement', 'Admin \'admin\' mengedit pengumuman: \'Hari Libur Nasional\' (ID: 1)', NULL, NULL, '2025-08-04 22:07:44'), (55, 1, 'edit_announcement', 'Admin \'admin\' mengedit pengumuman: \'Hari Libur Nasional\' (ID: 1)', NULL, NULL, '2025-08-04 22:08:26'), (56, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-04 22:20:50'), (57, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-04 22:40:15'), (58, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'Pengumuman Olahraga Hari Jumat\'', NULL, NULL, '2025-08-04 22:47:45'), (59, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Pengumuman Olahraga Hari Jumat\'', NULL, NULL, '2025-08-04 22:47:50'), (60, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'Lorem Ipsum\'', NULL, NULL, '2025-08-04 23:00:35'), (61, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Lorem Ipsum\'', NULL, NULL, '2025-08-04 23:00:44'), (62, 1, 'create_folder', 'Admin admin membuat folder: Departemen', NULL, 5, '2025-08-05 08:07:37'), (63, 1, 'create_folder', 'Admin admin membuat folder: GA', NULL, 6, '2025-08-05 08:07:50'), (64, 1, 'create_folder', 'Admin admin membuat folder: IT', NULL, 7, '2025-08-05 08:30:14'), (65, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'Ini pengumuman\'', NULL, NULL, '2025-08-05 09:16:35'), (66, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Ini pengumuman\'', NULL, NULL, '2025-08-05 09:16:42'), (67, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-05 09:31:13'), (68, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 14:46:03'), (69, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 14:54:10'), (70, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-05 15:21:13'), (71, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2025-08-05 15:21:19'), (72, 1, 'view_announcement', 'Pengguna \'admin\' melihat pengumuman: \'Hari Libur Nasional\'', NULL, NULL, '2025-08-06 09:05:02'), (73, 1, 'create_event', 'Admin \'admin\' membuat acara: \'Libur tambahan\'', NULL, NULL, '2025-08-06 09:05:39'), (74, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 16:19:56'), (75, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 19:42:06'), (76, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 19:46:07'), (77, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:08:21'), (78, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:10:56'), (79, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:11:38'), (80, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:12:00'), (81, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:17:33'), (82, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:21:44'), (83, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:22:16'), (84, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:24:26'), (85, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:28:12'), (87, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:37:40'), (88, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:38:52'), (89, 1, 'attempt_right_click', 'User \'admin\' mencoba melakukan klik kanan saat melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:38:57'), (90, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:39:30'), (91, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 20:49:14'), (92, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Penangan Masalah User\'.', NULL, NULL, '2025-08-06 21:03:31'), (93, 1, 'upload_document', 'Admin admin mengunggah: SOP Pembuatan Software IT', 4, NULL, '2025-08-06 22:14:10'), (94, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Pembuatan Software IT\'.', NULL, NULL, '2025-08-06 22:14:38'), (95, 1, 'edit_event', 'Admin \'admin\' mengedit acara: \'Libur tambahan\' (ID: 1)', NULL, NULL, '2026-01-14 07:52:34'), (96, 1, 'edit_event', 'Admin \'admin\' mengedit acara: \'Libur tambahan\' (ID: 1)', NULL, NULL, '2026-01-14 07:59:13'), (97, 1, 'edit_event', 'Admin \'admin\' mengedit acara: \'Libur tambahan\' (ID: 1)', NULL, NULL, '2026-01-14 07:59:26'), (98, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2026-01-14 08:36:35'), (99, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2026-01-14 08:37:10'), (100, 1, 'attempt_right_click', 'User \'admin\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-14 08:45:32'), (101, 1, 'attempt_right_click', 'User \'admin\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-14 08:45:33'), (102, 1, 'attempt_right_click', 'User \'admin\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-14 08:45:33'), (103, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2026-01-14 08:47:09'), (104, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2026-01-14 08:50:59'), (105, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2026-01-14 08:51:19'), (106, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Pembuatan Software IT\'.', NULL, NULL, '2026-01-14 08:51:23'), (109, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2026-01-14 09:09:49'), (146, 1, 'view_document', 'User \'admin\' melihat dokumen \'Milik Hanif GA\'.', NULL, NULL, '2026-01-14 13:10:34'), (147, 1, 'view_document', 'User \'admin\' melihat dokumen \'SOP Kendaraan\'.', NULL, NULL, '2026-01-14 13:10:39'), (151, 1, 'view_document', 'User \'admin\' melihat dokumen \'Milik Hanif GA\'.', NULL, NULL, '2026-01-14 13:14:07'), (152, 1, 'view_document', 'User \'admin\' melihat dokumen \'Milik HRD\'.', NULL, NULL, '2026-01-14 13:14:08'), (161, 6, 'create_folder', 'Admin managerit membuat folder: HRD', NULL, 12, '2026-01-19 08:26:19'), (162, 6, 'create_folder', 'Admin managerit membuat folder: IT', NULL, 13, '2026-01-19 08:27:27'), (163, 6, 'create_folder', 'Admin managerit membuat folder: GA', NULL, 14, '2026-01-19 08:27:58'), (164, 6, 'create_folder', 'Admin managerit membuat folder: Finance', NULL, 15, '2026-01-19 08:28:16'), (165, 6, 'upload_document', 'Admin managerit uploading: Dokumen Milik IT', 10, NULL, '2026-01-19 08:33:44'), (166, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 08:33:52'), (167, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:11:08'), (168, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:11:12'), (169, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:12:07'), (170, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:18:11'), (171, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:18:13'), (172, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:18:13'), (173, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:48:04'), (174, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:48:08'), (175, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:49:12'), (176, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:49:13'), (177, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:34'), (178, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:35'), (179, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:36'), (180, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:36'), (181, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:38'), (182, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:38'), (183, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:39'), (184, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:39'), (185, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:39'), (186, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:39'), (187, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:40'), (188, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:40'), (189, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 10:49:41'), (190, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 10:59:42'), (191, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:01:12'), (192, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:07:57'), (193, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:12:10'), (194, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:12:11'), (195, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:12:11'), (196, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:12:11'), (197, 11, 'attempt_right_click', 'User \'staffit\' mencoba melakukan klik kanan saat melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:12:12'), (198, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:13:25'), (199, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:14:09'), (200, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:14:18'), (201, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:15:54'), (202, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:19:34'), (203, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:21:03'), (204, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:21:04'), (205, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:24:37'), (206, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:29:23'), (207, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Dokumen Milik IT\'.', NULL, NULL, '2026-01-19 11:30:03'), (208, 1, 'create_announcement', 'Admin \'admin\' membuat pengumuman: \'tes pengumuman\'', NULL, NULL, '2026-01-19 13:12:25'), (209, 11, 'view_announcement', 'Pengguna \'staffit\' melihat pengumuman: \'tes pengumuman\'', NULL, NULL, '2026-01-19 13:18:52'), (210, 1, 'create_folder', 'Admin admin membuat folder: TI', NULL, 16, '2026-01-19 15:02:44'), (211, 1, 'create_folder', 'Admin admin membuat folder: TI', NULL, 17, '2026-01-19 16:14:18'), (212, 1, 'upload_document', 'Admin admin uploading: Ini Milik IT', 11, NULL, '2026-01-19 16:16:01'), (213, 11, 'view_document', 'User \'staffit\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-19 16:16:23'), (214, 1, 'attempt_right_click', 'User \'admin\' mencoba melakukan klik kanan saat melihat dokumen \'\'.', NULL, NULL, '2026-01-19 16:34:04'), (215, 7, 'view_document', 'User \'managerga\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:24:36'), (216, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:32:13'), (217, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:36:11'), (218, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:40:27'), (219, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:42:06'), (220, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:42:40'), (221, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:43:26'), (222, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:44:18'), (223, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:46:55'), (224, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:48:25'), (225, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:49:31'), (226, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:52:38'), (227, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:55:35'), (228, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:55:47'), (229, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:56:44'), (230, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 11:59:02'), (231, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 12:02:58'), (232, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 12:30:04'), (233, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 12:30:14'), (234, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 12:30:17'), (235, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 12:30:56'), (236, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 12:31:32'), (237, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 13:06:36'), (238, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:24:32'), (239, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:30:11'), (240, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:31:32'), (241, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:36:40'), (242, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:37:03'), (243, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:41:37'), (244, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:41:48'), (245, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:42:16'), (246, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:42:35'), (247, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:43:49'), (248, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:44:22'), (249, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:44:34'), (250, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:45:42'), (251, 1, 'view_document', 'User \'admin\' melihat dokumen \'Ini Milik IT\'.', NULL, NULL, '2026-01-20 16:46:16');
COMMIT;

-- ----------------------------
-- Table structure for announcements
-- ----------------------------
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `attachment_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `start_date` datetime NULL DEFAULT NULL,
  `end_date` datetime NULL DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_announcements_user`(`created_by` ASC) USING BTREE,
  CONSTRAINT `fk_announcements_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of announcements
-- ----------------------------
BEGIN;
INSERT INTO `announcements` (`id`, `title`, `content`, `attachment_file`, `start_date`, `end_date`, `created_by`, `created_at`, `is_pinned`) VALUES (1, 'Hari Libur Nasional', '<p>Memperingati hari libur nasional yang bertepatan pada 18 Agustus di nyatakan libur tambahan</p>', '', '2025-08-01 22:07:00', '2025-08-18 22:07:00', 1, '2025-08-04 21:43:36', 1), (2, 'Pengumuman Olahraga Hari Jumat', '<p>olahraga woey</p>', NULL, '2025-08-01 22:47:00', '2025-08-31 22:47:00', 1, '2025-08-04 22:47:45', 1), (3, 'Lorem Ipsum', '<p>\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"</p>', 'attachment_1754323235_6890d923a8627.jpg', '2025-08-01 22:59:00', '2025-08-31 23:00:00', 1, '2025-08-04 23:00:35', 1), (4, 'Ini pengumuman', '<p>coba pengumuman</p>', NULL, '2025-08-05 09:16:00', '2025-08-05 15:22:00', 1, '2025-08-05 09:16:35', 1), (5, 'tes pengumuman', '<p>asdasda</p>', NULL, '2026-01-19 13:12:00', '2026-01-19 13:14:00', 1, '2026-01-19 13:12:25', 0);
COMMIT;

-- ----------------------------
-- Table structure for carousel_images
-- ----------------------------
DROP TABLE IF EXISTS `carousel_images`;
CREATE TABLE `carousel_images`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `caption` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of carousel_images
-- ----------------------------
BEGIN;
INSERT INTO `carousel_images` (`id`, `image_name`, `caption`) VALUES (1, '688f4d4a32223.png', '1'), (2, '688f4d54612a9.jpg', '2');
COMMIT;

-- ----------------------------
-- Table structure for departements
-- ----------------------------
DROP TABLE IF EXISTS `departements`;
CREATE TABLE `departements`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of departements
-- ----------------------------
BEGIN;
INSERT INTO `departements` (`id`, `name`) VALUES (1, 'IT'), (2, 'HRD'), (3, 'GA'), (4, 'Finance');
COMMIT;

-- ----------------------------
-- Table structure for document_departments
-- ----------------------------
DROP TABLE IF EXISTS `document_departments`;
CREATE TABLE `document_departments`  (
  `document_id` int NOT NULL,
  `departement_id` int NOT NULL,
  PRIMARY KEY (`document_id`, `departement_id`) USING BTREE,
  INDEX `departement_id`(`departement_id` ASC) USING BTREE,
  CONSTRAINT `dd_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `dd_ibfk_2` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of document_departments
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for document_permissions
-- ----------------------------
DROP TABLE IF EXISTS `document_permissions`;
CREATE TABLE `document_permissions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `user_id` int NULL DEFAULT NULL,
  `departement_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `document_id`(`document_id` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `departement_id`(`departement_id` ASC) USING BTREE,
  CONSTRAINT `document_permissions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `document_permissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `document_permissions_ibfk_3` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of document_permissions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for document_user_access
-- ----------------------------
DROP TABLE IF EXISTS `document_user_access`;
CREATE TABLE `document_user_access`  (
  `document_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`document_id`, `user_id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `dua_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `dua_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of document_user_access
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for document_versions
-- ----------------------------
DROP TABLE IF EXISTS `document_versions`;
CREATE TABLE `document_versions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `version_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'Catatan atau ringkasan perubahan untuk versi ini',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `document_id`(`document_id` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `document_versions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of document_versions
-- ----------------------------
BEGIN;
INSERT INTO `document_versions` (`id`, `document_id`, `version_file_name`, `notes`, `uploaded_at`, `user_id`) VALUES (1, 2, '688f078fd8a6b.pdf', 'Kesalahan 1', '2025-08-03 19:26:15', 1), (2, 2, '688f5567c1fe8.pdf', 'Kesalahan 2', '2025-08-03 19:40:22', 1), (3, 2, '688f58b6e0471.pdf', 'Kesalahan 3', '2025-08-03 19:58:57', 1), (4, 2, '688f5d114626f.pdf', 'Kesalahan 4', '2025-08-03 20:00:27', 1), (5, 2, '688f5d6b43cd6.pdf', 'Kesalahan 5', '2025-08-03 20:12:38', 1), (6, 2, '688f6046f24eb.pdf', 'revisi terkait perubahan flowchart', '2025-08-03 22:39:48', 1);
COMMIT;

-- ----------------------------
-- Table structure for documents
-- ----------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `folder_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `folder_id`(`folder_id` ASC) USING BTREE,
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of documents
-- ----------------------------
BEGIN;
INSERT INTO `documents` (`id`, `title`, `file_name`, `folder_id`) VALUES (2, 'SOP Penangan Masalah User', '688f82c4b6bcb.pdf', 2), (3, 'SOP Kendaraan', '68900d2d31658.pdf', 3), (4, 'SOP Pembuatan Software IT', '689371422d041.pdf', 2), (11, 'Ini Milik IT', '696df6511cfa8.pdf', 16);
COMMIT;

-- ----------------------------
-- Table structure for elearning_completion
-- ----------------------------
DROP TABLE IF EXISTS `elearning_completion`;
CREATE TABLE `elearning_completion`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `material_id` int NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `user_material_unique`(`user_id` ASC, `material_id` ASC) USING BTREE,
  INDEX `material_id`(`material_id` ASC) USING BTREE,
  CONSTRAINT `elearning_completion_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `elearning_completion_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `elearning_materials` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of elearning_completion
-- ----------------------------
BEGIN;
INSERT INTO `elearning_completion` (`id`, `user_id`, `material_id`, `completed_at`) VALUES (1, 1, 1, '2025-08-07 09:07:27'), (2, 1, 2, '2025-08-07 09:07:30'), (5, 14, 1, '2026-01-19 13:30:26'), (6, 14, 2, '2026-01-19 13:30:31');
COMMIT;

-- ----------------------------
-- Table structure for elearning_courses
-- ----------------------------
DROP TABLE IF EXISTS `elearning_courses`;
CREATE TABLE `elearning_courses`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `share_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `share_token`(`share_token` ASC) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  CONSTRAINT `elearning_courses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of elearning_courses
-- ----------------------------
BEGIN;
INSERT INTO `elearning_courses` (`id`, `title`, `description`, `share_token`, `created_by`, `created_at`) VALUES (1, 'Belajar Excel Untuk Pemula', 'Materi ini disajikan untuk training excel secara mandiri', '86f973b6356995f9d5c3e08e9c772d7f', 1, '2025-08-07 09:06:45');
COMMIT;

-- ----------------------------
-- Table structure for elearning_materials
-- ----------------------------
DROP TABLE IF EXISTS `elearning_materials`;
CREATE TABLE `elearning_materials`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content_type` enum('text','video') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `video_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `course_id`(`course_id` ASC) USING BTREE,
  CONSTRAINT `elearning_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `elearning_courses` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of elearning_materials
-- ----------------------------
BEGIN;
INSERT INTO `elearning_materials` (`id`, `course_id`, `title`, `content_type`, `content_text`, `video_file_name`, `sort_order`) VALUES (1, 1, 'Belajar Excel Video 1', 'video', NULL, 'video_1_68940a35d53b1.mp4', 0), (2, 1, 'Belajar Excel Video 2', 'video', NULL, 'video_1_68940a35d5b6c.mp4', 1);
COMMIT;

-- ----------------------------
-- Table structure for events
-- ----------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NULL DEFAULT NULL,
  `event_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '#3788d8',
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_events_user`(`created_by` ASC) USING BTREE,
  CONSTRAINT `fk_events_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of events
-- ----------------------------
BEGIN;
INSERT INTO `events` (`id`, `title`, `description`, `start_datetime`, `end_datetime`, `event_color`, `created_by`, `created_at`) VALUES (1, 'Libur tambahan', '', '2026-01-14 13:05:00', '2025-08-18 09:05:00', '#3788d8', 1, '2025-08-06 09:05:39');
COMMIT;

-- ----------------------------
-- Table structure for folder_departments
-- ----------------------------
DROP TABLE IF EXISTS `folder_departments`;
CREATE TABLE `folder_departments`  (
  `folder_id` int NOT NULL,
  `departement_id` int NOT NULL,
  PRIMARY KEY (`folder_id`, `departement_id`) USING BTREE,
  INDEX `departement_id`(`departement_id` ASC) USING BTREE,
  CONSTRAINT `fd_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fd_ibfk_2` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of folder_departments
-- ----------------------------
BEGIN;
INSERT INTO `folder_departments` (`folder_id`, `departement_id`) VALUES (16, 1);
COMMIT;

-- ----------------------------
-- Table structure for folder_user_access
-- ----------------------------
DROP TABLE IF EXISTS `folder_user_access`;
CREATE TABLE `folder_user_access`  (
  `folder_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`folder_id`, `user_id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `fua_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fua_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of folder_user_access
-- ----------------------------
BEGIN;
INSERT INTO `folder_user_access` (`folder_id`, `user_id`) VALUES (16, 6), (16, 7), (16, 8), (16, 10), (16, 11);
COMMIT;

-- ----------------------------
-- Table structure for folders
-- ----------------------------
DROP TABLE IF EXISTS `folders`;
CREATE TABLE `folders`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parent_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parent_id`(`parent_id` ASC) USING BTREE,
  CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of folders
-- ----------------------------
BEGIN;
INSERT INTO `folders` (`id`, `name`, `parent_id`) VALUES (1, 'Public', NULL), (2, 'IT', 1), (3, 'GA', 1), (4, 'HRD', 1), (5, 'Departemen', NULL), (16, 'TI', 5);
COMMIT;

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 57 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of notifications
-- ----------------------------
BEGIN;
INSERT INTO `notifications` (`id`, `user_id`, `message`, `link`, `is_read`, `created_at`) VALUES (21, 1, 'New documents \'Milik Hanif GA\' have been added.', 'dashboard.php?doc_id=5', 1, '2026-01-14 11:20:16'), (25, 1, 'New documents \'Milik HRD\' have been added.', 'dashboard.php?doc_id=6', 1, '2026-01-14 13:13:11'), (29, 1, 'New documents \'Milik Haryo inance\' have been added.', 'dashboard.php?doc_id=7', 1, '2026-01-14 13:15:57'), (33, 1, 'New documents \'Milik Budi IT\' have been added.', 'dashboard.php?doc_id=8', 1, '2026-01-14 13:16:47'), (37, 1, 'New documents \'Milik Haryo Finance\' have been added.', 'dashboard.php?doc_id=9', 1, '2026-01-14 13:32:57'), (41, 1, 'New documents \'Dokumen Milik IT\' have been added.', 'dashboard.php?doc_id=10', 1, '2026-01-19 08:33:44'), (42, 11, 'New documents \'Dokumen Milik IT\' have been added.', 'dashboard.php?doc_id=10', 0, '2026-01-19 08:33:44'), (43, 12, 'New documents \'Dokumen Milik IT\' have been added.', 'dashboard.php?doc_id=10', 0, '2026-01-19 08:33:44'), (44, 13, 'New documents \'Dokumen Milik IT\' have been added.', 'dashboard.php?doc_id=10', 0, '2026-01-19 08:33:44'), (45, 14, 'New documents \'Dokumen Milik IT\' have been added.', 'dashboard.php?doc_id=10', 0, '2026-01-19 08:33:44'), (46, 7, 'New documents \'Dokumen Milik IT\' have been added.', 'dashboard.php?doc_id=10', 1, '2026-01-19 08:33:44'), (47, 8, 'New documents \'Dokumen Milik IT\' have been added.', 'dashboard.php?doc_id=10', 0, '2026-01-19 08:33:44'), (48, 10, 'New documents \'Dokumen Milik IT\' have been added.', 'dashboard.php?doc_id=10', 0, '2026-01-19 08:33:44'), (49, 11, 'New documents \'Ini Milik IT\' have been added.', 'dashboard.php?doc_id=11', 0, '2026-01-19 16:16:01'), (50, 12, 'New documents \'Ini Milik IT\' have been added.', 'dashboard.php?doc_id=11', 0, '2026-01-19 16:16:01'), (51, 13, 'New documents \'Ini Milik IT\' have been added.', 'dashboard.php?doc_id=11', 0, '2026-01-19 16:16:01'), (52, 14, 'New documents \'Ini Milik IT\' have been added.', 'dashboard.php?doc_id=11', 0, '2026-01-19 16:16:01'), (53, 6, 'New documents \'Ini Milik IT\' have been added.', 'dashboard.php?doc_id=11', 0, '2026-01-19 16:16:01'), (54, 7, 'New documents \'Ini Milik IT\' have been added.', 'dashboard.php?doc_id=11', 0, '2026-01-19 16:16:01'), (55, 8, 'New documents \'Ini Milik IT\' have been added.', 'dashboard.php?doc_id=11', 0, '2026-01-19 16:16:01'), (56, 10, 'New documents \'Ini Milik IT\' have been added.', 'dashboard.php?doc_id=11', 0, '2026-01-19 16:16:01');
COMMIT;

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., manage_users, view_documents',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `permission_name`(`permission_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of permissions
-- ----------------------------
BEGIN;
INSERT INTO `permissions` (`id`, `permission_name`, `description`) VALUES (1, 'manage_documents', 'Dapat menambah, mengedit, dan menghapus dokumen/folder.'), (2, 'manage_users', 'Dapat menambah, mengedit, dan menghapus pengguna.'), (3, 'manage_roles', 'Dapat mengelola peran dan hak aksesnya.'), (4, 'view_all_documents', 'Dapat melihat semua dokumen, terlepas dari departemen.'), (5, 'view_audit_trail', 'Dapat melihat halaman jejak audit (audit trail).'), (6, 'view_analytics', 'Dapat melihat halaman laporan dan analitik.'), (7, 'manage_events', 'Dapat menambah, mengedit, dan menghapus acara di kalender.'), (8, 'manage_polls', 'Dapat membuat, mengedit, dan melihat hasil polling atau survei.'), (9, 'manage_elearning', 'Dapat membuat, mengedit, dan menghapus kursus e-learning beserta materinya.'), (10, 'view_elearning_report', 'Dapat melihat laporan penyelesaian kursus e-learning.');
COMMIT;

-- ----------------------------
-- Table structure for poll_options
-- ----------------------------
DROP TABLE IF EXISTS `poll_options`;
CREATE TABLE `poll_options`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `question_id`(`question_id` ASC) USING BTREE,
  CONSTRAINT `poll_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `poll_questions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of poll_options
-- ----------------------------
BEGIN;
INSERT INTO `poll_options` (`id`, `question_id`, `option_text`) VALUES (1, 1, 'a'), (2, 1, 'b'), (3, 1, 'c'), (4, 2, 'a'), (5, 2, 'b'), (6, 2, 'c'), (7, 3, 'a'), (8, 3, 'b'), (9, 3, 'c'), (10, 4, 'a'), (11, 4, 'b'), (12, 4, 'c');
COMMIT;

-- ----------------------------
-- Table structure for poll_questions
-- ----------------------------
DROP TABLE IF EXISTS `poll_questions`;
CREATE TABLE `poll_questions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `poll_id` int NOT NULL,
  `question_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `poll_id`(`poll_id` ASC) USING BTREE,
  CONSTRAINT `poll_questions_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of poll_questions
-- ----------------------------
BEGIN;
INSERT INTO `poll_questions` (`id`, `poll_id`, `question_text`, `sort_order`) VALUES (1, 1, '1', 0), (2, 1, '2', 1), (3, 2, '1', 0), (4, 2, '2', 1);
COMMIT;

-- ----------------------------
-- Table structure for poll_responses
-- ----------------------------
DROP TABLE IF EXISTS `poll_responses`;
CREATE TABLE `poll_responses`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `poll_id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_id` int NOT NULL,
  `user_id` int NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unique_vote`(`poll_id` ASC, `question_id` ASC, `ip_address` ASC) USING BTREE,
  INDEX `poll_id`(`poll_id` ASC) USING BTREE,
  INDEX `question_id`(`question_id` ASC) USING BTREE,
  INDEX `option_id`(`option_id` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `poll_responses_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `poll_responses_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `poll_questions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `poll_responses_ibfk_3` FOREIGN KEY (`option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `poll_responses_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of poll_responses
-- ----------------------------
BEGIN;
INSERT INTO `poll_responses` (`id`, `poll_id`, `question_id`, `option_id`, `user_id`, `ip_address`, `submitted_at`) VALUES (1, 1, 1, 1, NULL, '127.0.0.1', '2025-08-06 09:53:52'), (2, 1, 2, 4, NULL, '127.0.0.1', '2025-08-06 09:53:52'), (3, 2, 3, 7, NULL, '::1', '2026-01-14 08:39:33'), (4, 2, 4, 10, NULL, '::1', '2026-01-14 08:39:33');
COMMIT;

-- ----------------------------
-- Table structure for polls
-- ----------------------------
DROP TABLE IF EXISTS `polls`;
CREATE TABLE `polls`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `status` enum('active','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `share_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `share_token`(`share_token` ASC) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  CONSTRAINT `polls_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of polls
-- ----------------------------
BEGIN;
INSERT INTO `polls` (`id`, `title`, `description`, `status`, `share_token`, `created_by`, `created_at`) VALUES (1, 'Survey 1', 'test', 'active', '731a805bd5095cdf9f8cc3747e15ac1b', 1, '2025-08-06 09:29:07'), (2, 'Survey 2', 'Survey baru', 'active', '4e09f7894ae86faf76046b88fb12ddf6', 1, '2025-08-06 09:33:25');
COMMIT;

-- ----------------------------
-- Table structure for public_announcements
-- ----------------------------
DROP TABLE IF EXISTS `public_announcements`;
CREATE TABLE `public_announcements`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `announcement_id` int NOT NULL,
  `token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `token`(`token` ASC) USING BTREE,
  INDEX `announcement_id`(`announcement_id` ASC) USING BTREE,
  CONSTRAINT `fk_public_ann_id` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of public_announcements
-- ----------------------------
BEGIN;
INSERT INTO `public_announcements` (`id`, `announcement_id`, `token`, `created_at`) VALUES (1, 3, '1fb4f23755c06e5f5b7daf95899174bf', '2025-08-05 09:08:19'), (2, 4, '1c67631fdfc46378f0e2f45aece6c19c', '2025-08-06 09:34:30');
COMMIT;

-- ----------------------------
-- Table structure for role_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions`  (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`) USING BTREE,
  INDEX `permission_id`(`permission_id` ASC) USING BTREE,
  CONSTRAINT `fk_role_permissions_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of role_permissions
-- ----------------------------
BEGIN;
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (3, 1), (3, 2), (3, 4), (3, 5), (3, 8);
COMMIT;

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `role_name`(`role_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of roles
-- ----------------------------
BEGIN;
INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES (1, 'Admin', 'Akses penuh ke semua fitur manajemen.'), (2, 'Staff', 'Akses dasar untuk melihat dokumen.'), (3, 'Manager', 'Dapat melihat semua dokumen dan laporan, tapi tidak bisa mengubah user.');
COMMIT;

-- ----------------------------
-- Table structure for shared_links
-- ----------------------------
DROP TABLE IF EXISTS `shared_links`;
CREATE TABLE `shared_links`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `expires_at` datetime NULL DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `token`(`token` ASC) USING BTREE,
  INDEX `document_id`(`document_id` ASC) USING BTREE,
  INDEX `fk_shared_links_created_by`(`created_by` ASC) USING BTREE,
  CONSTRAINT `fk_shared_links_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_shared_links_document_id` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of shared_links
-- ----------------------------
BEGIN;
INSERT INTO `shared_links` (`id`, `document_id`, `token`, `password`, `expires_at`, `created_by`, `created_at`) VALUES (1, 3, 'c0f86866f6e908e66654e2cb7f756bc7', '$2y$10$PlR34LFOBP17CgwxdNLKmeXny1nRTudH6ffej1FmPgliIGHlOJGzi', '2025-08-04 11:12:00', 1, '2025-08-04 11:09:32'), (2, 2, '4479cbae4374d89c1fbb0455d4d83771', '$2y$10$1JCbUM/pZgrV1.zTKBFAde0Ob4mJuo1VUjdrqUgxewZh9TXqSRqm2', '2025-08-04 11:18:00', 1, '2025-08-04 11:14:22'), (3, 3, '07302e65d27ff23862a117b8bd1a63fe', '$2y$10$zD5weJUGVMsjRv37uWPtXuRgpEy.ERScdGBZUwcrfKCKk07ufN/4C', '2025-08-06 09:36:00', 1, '2025-08-06 09:35:33');
COMMIT;

-- ----------------------------
-- Table structure for shortcuts
-- ----------------------------
DROP TABLE IF EXISTS `shortcuts`;
CREATE TABLE `shortcuts`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of shortcuts
-- ----------------------------
BEGIN;
INSERT INTO `shortcuts` (`id`, `name`, `url`, `icon_class`, `sort_order`, `created_at`) VALUES (1, 'Email', 'https://mail.hostinger.com/', 'fas fa-envelope', 10, '2025-08-06 15:27:11'), (2, 'Office 365', 'https://www.office.com/', 'fas fa-file-word', 20, '2025-08-06 15:27:11'), (3, 'ERP', 'http://hris.berdikarimeubel.com/odoo', 'fas fa-chart-bar', 30, '2025-08-06 15:27:11'), (4, 'HRIS', '#', 'fas fa-users', 40, '2025-08-06 15:27:11'), (5, 'Helpdesk', '#', 'fas fa-headset', 50, '2025-08-06 15:27:11'), (6, 'Lainnya', '#', 'fas fa-cogs', 60, '2025-08-06 15:27:11');
COMMIT;

-- ----------------------------
-- Table structure for user_favorite_documents
-- ----------------------------
DROP TABLE IF EXISTS `user_favorite_documents`;
CREATE TABLE `user_favorite_documents`  (
  `user_id` int NOT NULL,
  `document_id` int NOT NULL,
  PRIMARY KEY (`user_id`, `document_id`) USING BTREE,
  INDEX `document_id`(`document_id` ASC) USING BTREE,
  CONSTRAINT `user_favorite_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `user_favorite_documents_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of user_favorite_documents
-- ----------------------------
BEGIN;
INSERT INTO `user_favorite_documents` (`user_id`, `document_id`) VALUES (1, 2), (1, 3), (1, 4);
COMMIT;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_id` int NULL DEFAULT 2,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Nonaktif',
  `departement_id` int NULL DEFAULT NULL,
  `theme` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'light' COMMENT 'Preferensi tema: light atau dark',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE,
  INDEX `users_ibfk_2`(`role_id` ASC) USING BTREE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- ----------------------------
-- Records of users
-- ----------------------------
BEGIN;
INSERT INTO `users` (`id`, `username`, `password`, `role_id`, `profile_picture`, `is_active`, `departement_id`, `theme`) VALUES (1, 'admin', '$2y$10$jGPmmxQuHet6DRr1bVOBkejmNTV2OL9SMs1vHkU8w/fZ8NrE1CI/u', 1, 'user_1_1754316834.jpg', 1, NULL, 'light'), (6, 'managerit', '$2y$10$xq1IklZMFHnnKIXs5pgTBOEfRIYc6g.Nimj1tHXNANRSXj6fn18e2', 3, NULL, 1, 1, 'light'), (7, 'managerga', '$2y$10$TGuklSe96Pwbgw74qRMM5O5.rTBJ26RN9Qehf1rp.58Y8YBotFBx6', 3, NULL, 1, 3, 'light'), (8, 'managerfinance', '$2y$10$pvzuW4elS7OSnMcbk/6TnOcZoqrl3zdwlkQ71HdozDLPqVUJmx5OW', 3, NULL, 1, 4, 'light'), (10, 'managerhrd', '$2y$10$faWu.x2K0YANVAayp50VcOp4x.CtmH3WteBaQpLX74sMzLscR/BD.', 3, NULL, 1, 2, 'light'), (11, 'staffit', '$2y$10$KSWNNcNW17s5RdN.odviY.m0yW9x/bheoYm2.hbhGiB5/IymiUWey', 2, NULL, 1, 1, 'light'), (12, 'staffga', '$2y$10$Pbn9J/KjyN0GCFf7WPKSNeUDkTz/l2LZmlAIGWKlBp2peLQeTRQtK', 2, NULL, 1, 3, 'light'), (13, 'stafffinance', '$2y$10$Xi3Y5PgM7hYws0ShdO3H5OBnjWjUxynvMnlpWkW7rGuqcxvgD2ZPy', 2, NULL, 1, 4, 'light'), (14, 'staffhrd', '$2y$10$s49A/KYo0VY7JIvFRVK9BuRSrrWg35/xEL/BLog1bTcrpCC.NI.Yu', 2, NULL, 1, 2, 'light');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
