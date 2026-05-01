-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 11:24 AM
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
-- Database: `uaddara_sba`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(10) UNSIGNED NOT NULL,
  `year_name` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `year_name`, `is_active`, `created_at`) VALUES
(1, '2025/2026', 1, '2026-03-20 18:04:21'),
(5, '2024/2025', 0, '2026-03-23 10:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `days_present` smallint(5) UNSIGNED DEFAULT 0,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `term_id`, `days_present`, `updated_by`, `updated_at`) VALUES
(1, 130, 1, 10, 7, '2026-03-25 10:05:50'),
(2, 131, 1, 10, 7, '2026-03-22 10:13:46'),
(3, 112, 1, 50, 7, '2026-03-22 21:54:51'),
(4, 113, 1, 60, 7, '2026-03-22 21:54:58'),
(5, 132, 1, 20, 7, '2026-03-22 21:55:01'),
(6, 133, 1, 60, 7, '2026-03-22 21:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `level_id` int(10) UNSIGNED NOT NULL,
  `class_name` varchar(20) NOT NULL,
  `section` varchar(5) DEFAULT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `grading_system` enum('proficiency','waec') NOT NULL DEFAULT 'proficiency'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `level_id`, `class_name`, `section`, `academic_year_id`, `created_at`, `grading_system`) VALUES
(1, 1, 'BASIC 1', '', 1, '2026-03-20 18:06:58', 'proficiency'),
(2, 3, 'BASIC 7', 'A', 1, '2026-03-20 18:24:59', 'proficiency'),
(5, 3, 'BASIC 7', 'B', 1, '2026-03-21 09:20:10', 'proficiency'),
(6, 1, 'BASIC 2', '', 1, '2026-03-21 09:24:05', 'proficiency'),
(7, 1, 'BASIC 3', '', 1, '2026-03-21 09:24:22', 'proficiency'),
(8, 1, 'BASIC 4', '', 1, '2026-03-21 09:24:47', 'proficiency'),
(9, 2, 'BASIC 5', 'A', 1, '2026-03-22 09:05:04', 'proficiency'),
(10, 2, 'BASIC 5', 'B', 1, '2026-03-22 18:47:33', 'proficiency'),
(11, 2, 'BASIC 6', 'A', 1, '2026-03-22 18:50:42', 'proficiency'),
(12, 2, 'BASIC 6', 'B', 1, '2026-03-22 18:51:02', 'proficiency'),
(13, 3, 'BASIC 8', 'A', 1, '2026-03-22 18:51:37', 'proficiency'),
(14, 3, 'BASIC 8', 'B', 1, '2026-03-22 18:51:59', 'proficiency'),
(15, 3, 'BASIC 9', 'A', 1, '2026-03-22 18:52:25', 'proficiency'),
(16, 3, 'BASIC 9', 'B', 1, '2026-03-22 18:54:50', 'proficiency'),
(20, 3, 'B-Test', 'T', 1, '2026-03-23 11:15:49', 'proficiency');

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `class_id`, `subject_id`, `teacher_id`, `term_id`, `is_locked`, `locked_at`) VALUES
(10, 1, 48, NULL, 1, 0, NULL),
(11, 2, 34, 1, 1, 0, NULL),
(17, 2, 51, 5, 1, 0, NULL),
(18, 8, 32, 7, 1, 1, '2026-03-25 11:49:00'),
(19, 8, 28, 7, 1, 1, '2026-03-25 11:49:00'),
(20, 8, 6, 7, 1, 1, '2026-03-25 11:49:00'),
(22, 8, 29, 7, 1, 1, '2026-03-25 11:49:00'),
(24, 8, 31, 7, 1, 1, '2026-03-25 11:49:00'),
(25, 15, 51, 3, 1, 0, NULL),
(38, 20, 32, 1, 1, 0, NULL),
(40, 2, 45, 10, 1, 0, NULL),
(41, 14, 45, 10, 1, 0, NULL),
(42, 13, 45, 10, 1, 0, NULL),
(43, 14, 47, 6, 1, 0, NULL),
(44, 14, 46, 6, 1, 0, NULL),
(45, 13, 47, 6, 1, 0, NULL),
(46, 13, 46, 6, 1, 0, NULL),
(47, 16, 47, 6, 1, 0, NULL),
(48, 16, 46, 6, 1, 0, NULL),
(49, 15, 47, 6, 1, 0, NULL),
(50, 15, 46, 6, 1, 0, NULL),
(51, 14, 51, 11, 1, 0, NULL),
(52, 13, 51, 11, 1, 0, NULL),
(53, 16, 51, 11, 1, 0, NULL),
(54, 8, 51, 5, 1, 0, NULL),
(55, 10, 51, 5, 1, 0, NULL),
(56, 9, 51, 5, 1, 0, NULL),
(58, 5, 51, 5, 1, 0, NULL),
(83, 1, 56, 12, 1, 0, NULL),
(84, 6, 56, 12, 1, 0, NULL),
(85, 7, 56, 12, 1, 0, NULL),
(86, 8, 56, 12, 1, 0, NULL),
(87, 10, 56, 12, 1, 0, NULL),
(88, 9, 56, 12, 1, 0, NULL),
(89, 12, 56, 12, 1, 0, NULL),
(90, 11, 56, 12, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class_teachers`
--

CREATE TABLE `class_teachers` (
  `class_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_teachers`
--

INSERT INTO `class_teachers` (`class_id`, `teacher_id`) VALUES
(8, 7),
(10, 9),
(11, 1),
(15, 3);

-- --------------------------------------------------------

--
-- Table structure for table `computed_scores`
--

CREATE TABLE `computed_scores` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `class_subject_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `class_score` decimal(6,2) DEFAULT NULL,
  `exam_score` decimal(6,2) DEFAULT NULL,
  `overall_total` decimal(6,2) DEFAULT NULL,
  `proficiency_level` tinyint(3) UNSIGNED DEFAULT NULL,
  `subject_position` int(10) UNSIGNED DEFAULT NULL,
  `computed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computed_scores`
--

INSERT INTO `computed_scores` (`id`, `student_id`, `class_subject_id`, `term_id`, `class_score`, `exam_score`, `overall_total`, `proficiency_level`, `subject_position`, `computed_at`) VALUES
(1, 1, 11, 1, 40.00, 50.00, 90.00, 1, 1, '2026-03-21 01:07:47'),
(25, 3, 18, 1, 0.00, 0.00, 0.00, 5, 1, '2026-03-21 20:26:35'),
(26, 112, 19, 1, 47.00, 43.00, 90.00, 1, 8, '2026-03-31 12:20:40'),
(27, 113, 19, 1, 29.00, 24.00, 53.00, 4, 43, '2026-03-31 12:22:17'),
(28, 114, 19, 1, 49.00, 46.00, 95.00, 1, 4, '2026-03-31 12:22:17'),
(29, 115, 19, 1, 49.00, 47.00, 96.00, 1, 2, '2026-03-31 12:22:17'),
(30, 116, 19, 1, 30.00, 28.00, 58.00, 3, 37, '2026-03-31 12:22:17'),
(31, 117, 19, 1, 40.00, 36.00, 76.00, 2, 17, '2026-03-31 12:22:17'),
(32, 118, 19, 1, 33.00, 30.00, 63.00, 3, 34, '2026-03-31 12:22:17'),
(33, 119, 19, 1, 35.00, 29.00, 64.00, 3, 32, '2026-03-25 09:50:51'),
(34, 120, 19, 1, 47.00, 43.00, 90.00, 1, 8, '2026-03-31 12:22:17'),
(35, 121, 19, 1, 29.00, 24.00, 53.00, 4, 43, '2026-03-31 12:22:17'),
(36, 122, 19, 1, 35.00, 32.00, 67.00, 3, 27, '2026-03-31 12:22:17'),
(37, 123, 19, 1, 28.00, 26.00, 54.00, 3, 40, '2026-03-31 12:22:17'),
(38, 124, 19, 1, 36.00, 31.00, 67.00, 3, 27, '2026-03-31 12:22:17'),
(39, 125, 19, 1, 23.00, 22.00, 45.00, 4, 46, '2026-03-31 12:22:17'),
(40, 126, 19, 1, 35.00, 29.00, 64.00, 3, 32, '2026-03-25 09:50:51'),
(41, 127, 19, 1, 47.00, 43.00, 90.00, 1, 8, '2026-03-31 12:22:17'),
(42, 128, 19, 1, 29.00, 24.00, 53.00, 4, 43, '2026-03-31 12:22:17'),
(43, 129, 19, 1, 28.00, 26.00, 54.00, 3, 40, '2026-03-31 12:22:17'),
(44, 130, 19, 1, 50.00, 50.00, 100.00, 1, 1, '2026-03-25 09:50:51'),
(45, 131, 19, 1, 28.00, 29.00, 57.00, 3, 39, '2026-03-31 12:22:17'),
(46, 132, 19, 1, 35.00, 38.00, 73.00, 2, 24, '2026-03-25 09:50:51'),
(47, 133, 19, 1, 28.00, 32.00, 60.00, 3, 36, '2026-03-31 12:22:17'),
(48, 134, 19, 1, 28.00, 26.00, 54.00, 3, 40, '2026-03-31 12:22:17'),
(49, 135, 19, 1, 41.00, 35.00, 76.00, 2, 17, '2026-03-31 12:22:17'),
(50, 136, 19, 1, 36.00, 31.00, 67.00, 3, 27, '2026-03-31 12:22:17'),
(51, 137, 19, 1, 23.00, 22.00, 45.00, 4, 46, '2026-03-31 12:22:17'),
(52, 138, 19, 1, 47.00, 44.00, 91.00, 1, 6, '2026-03-31 12:22:17'),
(53, 139, 19, 1, 38.00, 33.00, 71.00, 2, 25, '2026-03-31 12:22:17'),
(54, 140, 19, 1, 39.00, 37.00, 76.00, 2, 17, '2026-03-31 12:22:17'),
(55, 141, 19, 1, 23.00, 20.00, 43.00, 4, 48, '2026-03-31 12:22:17'),
(56, 142, 19, 1, 43.00, 40.00, 83.00, 1, 11, '2026-03-31 12:22:17'),
(57, 143, 19, 1, 42.00, 36.00, 78.00, 2, 15, '2026-03-31 12:22:17'),
(58, 144, 19, 1, 49.00, 46.00, 95.00, 1, 4, '2026-03-31 12:22:17'),
(59, 145, 19, 1, 41.00, 35.00, 76.00, 2, 17, '2026-03-31 12:22:17'),
(60, 146, 19, 1, 47.00, 44.00, 91.00, 1, 6, '2026-03-31 12:22:17'),
(61, 147, 19, 1, 38.00, 33.00, 71.00, 2, 25, '2026-03-31 12:22:17'),
(62, 148, 19, 1, 23.00, 20.00, 43.00, 4, 48, '2026-03-31 12:22:17'),
(63, 149, 19, 1, 43.00, 40.00, 83.00, 1, 11, '2026-03-31 12:22:17'),
(64, 150, 19, 1, 49.00, 47.00, 96.00, 1, 2, '2026-03-31 12:22:17'),
(65, 151, 19, 1, 30.00, 28.00, 58.00, 3, 37, '2026-03-31 12:22:17'),
(66, 152, 19, 1, 40.00, 36.00, 76.00, 2, 17, '2026-03-31 12:22:17'),
(67, 153, 19, 1, 33.00, 30.00, 63.00, 3, 34, '2026-03-31 12:22:17'),
(68, 154, 19, 1, 42.00, 36.00, 78.00, 2, 15, '2026-03-31 12:22:17'),
(69, 155, 19, 1, 43.00, 38.00, 81.00, 1, 13, '2026-03-31 12:22:17'),
(70, 156, 19, 1, 35.00, 32.00, 67.00, 3, 27, '2026-03-31 12:22:17'),
(71, 157, 19, 1, 39.00, 28.00, 67.00, 3, 27, '2026-03-31 12:22:17'),
(72, 158, 19, 1, 41.00, 35.00, 76.00, 2, 17, '2026-03-31 12:22:17'),
(73, 159, 19, 1, 39.00, 37.00, 76.00, 2, 17, '2026-03-31 12:22:17'),
(74, 160, 19, 1, 43.00, 38.00, 81.00, 1, 13, '2026-03-31 12:22:17'),
(712, 2, 10, 1, 33.33, 30.00, 63.33, 3, 2, '2026-03-22 10:18:46'),
(713, 4, 10, 1, 27.50, 42.50, 70.00, 2, 1, '2026-03-22 10:18:46'),
(714, 5, 10, 1, 12.50, 0.00, 12.50, 5, 3, '2026-03-23 16:09:36'),
(715, 18, 10, 1, 34.17, 25.00, 59.17, 3, 2, '2026-03-23 16:09:36'),
(4731, 113, 22, 1, 0.00, 0.00, 0.00, 5, 2, '2026-03-25 09:50:51'),
(4749, 130, 22, 1, 48.00, 50.00, 98.00, 1, 1, '2026-03-31 12:22:17'),
(4751, 131, 22, 1, 0.00, 0.00, 0.00, 5, 2, '2026-03-25 09:50:51'),
(5274, 130, 54, 1, 49.00, 28.00, 77.00, 2, 1, '2026-03-31 12:22:17'),
(5277, 131, 54, 1, 44.00, 21.00, 65.00, 3, 2, '2026-03-31 12:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `exam_scores`
--

CREATE TABLE `exam_scores` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `class_subject_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `raw_score` decimal(6,2) DEFAULT NULL,
  `exam_score` decimal(6,2) DEFAULT NULL,
  `entered_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_scores`
--

INSERT INTO `exam_scores` (`id`, `student_id`, `class_subject_id`, `term_id`, `raw_score`, `exam_score`, `entered_by`, `updated_at`) VALUES
(1, 2, 10, 1, 60.00, 30.00, 3, '2026-03-20 23:21:40'),
(2, 1, 11, 1, 100.00, 50.00, 1, '2026-03-20 23:42:09'),
(3, 18, 10, 1, 50.00, 25.00, 3, '2026-03-21 22:01:22'),
(4, 4, 10, 1, 85.00, 42.50, 3, '2026-03-21 22:02:04'),
(5, 130, 19, 1, 100.00, 50.00, 7, '2026-03-24 11:33:32'),
(6, 131, 19, 1, 58.00, 29.00, 7, '2026-03-22 09:42:27'),
(7, 112, 19, 1, 85.00, 42.50, 7, '2026-03-22 09:42:27'),
(8, 113, 19, 1, 47.00, 23.50, 7, '2026-03-22 09:42:27'),
(9, 132, 19, 1, 76.00, 38.00, 7, '2026-03-22 09:42:27'),
(10, 133, 19, 1, 63.00, 31.50, 7, '2026-03-22 09:42:27'),
(11, 114, 19, 1, 91.00, 45.50, 7, '2026-03-22 09:42:27'),
(12, 134, 19, 1, 52.00, 26.00, 7, '2026-03-22 09:42:27'),
(13, 135, 19, 1, 69.00, 34.50, 7, '2026-03-22 09:42:27'),
(14, 136, 19, 1, 61.00, 30.50, 7, '2026-03-22 09:42:27'),
(15, 137, 19, 1, 43.00, 21.50, 7, '2026-03-22 09:42:27'),
(16, 138, 19, 1, 88.00, 44.00, 7, '2026-03-22 09:42:27'),
(17, 139, 19, 1, 66.00, 33.00, 7, '2026-03-22 09:42:27'),
(18, 140, 19, 1, 74.00, 37.00, 7, '2026-03-22 09:42:27'),
(19, 141, 19, 1, 39.00, 19.50, 7, '2026-03-22 09:42:27'),
(20, 142, 19, 1, 79.00, 39.50, 7, '2026-03-22 09:42:27'),
(21, 115, 19, 1, 93.00, 46.50, 7, '2026-03-22 09:42:27'),
(22, 116, 19, 1, 55.00, 27.50, 7, '2026-03-22 09:42:27'),
(23, 117, 19, 1, 71.00, 35.50, 7, '2026-03-22 09:42:27'),
(24, 118, 19, 1, 60.00, 30.00, 7, '2026-03-22 09:42:27'),
(25, 143, 19, 1, 72.00, 36.00, 7, '2026-03-22 09:42:27'),
(26, 119, 19, 1, 58.00, 29.00, 7, '2026-03-22 09:42:27'),
(27, 120, 19, 1, 85.00, 42.50, 7, '2026-03-22 09:42:27'),
(28, 121, 19, 1, 47.00, 23.50, 7, '2026-03-22 09:42:27'),
(29, 160, 19, 1, 76.00, 38.00, 7, '2026-03-22 09:42:27'),
(30, 122, 19, 1, 63.00, 31.50, 7, '2026-03-22 09:42:27'),
(31, 144, 19, 1, 91.00, 45.50, 7, '2026-03-22 09:42:27'),
(32, 123, 19, 1, 52.00, 26.00, 7, '2026-03-22 09:42:27'),
(33, 145, 19, 1, 69.00, 34.50, 7, '2026-03-22 09:42:27'),
(34, 124, 19, 1, 61.00, 30.50, 7, '2026-03-22 09:42:27'),
(35, 125, 19, 1, 43.00, 21.50, 7, '2026-03-22 09:42:27'),
(36, 146, 19, 1, 88.00, 44.00, 7, '2026-03-22 09:42:27'),
(37, 147, 19, 1, 66.00, 33.00, 7, '2026-03-22 09:42:27'),
(38, 159, 19, 1, 74.00, 37.00, 7, '2026-03-22 09:42:27'),
(39, 148, 19, 1, 39.00, 19.50, 7, '2026-03-22 09:42:27'),
(40, 149, 19, 1, 79.00, 39.50, 7, '2026-03-22 09:42:27'),
(41, 150, 19, 1, 93.00, 46.50, 7, '2026-03-22 09:42:27'),
(42, 151, 19, 1, 55.00, 27.50, 7, '2026-03-22 09:42:27'),
(43, 152, 19, 1, 71.00, 35.50, 7, '2026-03-22 09:42:27'),
(44, 153, 19, 1, 60.00, 30.00, 7, '2026-03-22 09:42:27'),
(45, 154, 19, 1, 72.00, 36.00, 7, '2026-03-22 09:42:27'),
(46, 126, 19, 1, 58.00, 29.00, 7, '2026-03-22 09:42:27'),
(47, 127, 19, 1, 85.00, 42.50, 7, '2026-03-22 09:42:27'),
(48, 128, 19, 1, 47.00, 23.50, 7, '2026-03-22 09:42:27'),
(49, 155, 19, 1, 76.00, 38.00, 7, '2026-03-22 09:42:27'),
(50, 156, 19, 1, 63.00, 31.50, 7, '2026-03-22 09:42:27'),
(51, 157, 19, 1, 56.00, 28.00, 7, '2026-03-25 09:43:42'),
(52, 129, 19, 1, 52.00, 26.00, 7, '2026-03-22 09:42:27'),
(53, 158, 19, 1, 69.00, 34.50, 7, '2026-03-22 09:42:27'),
(54, 3, 19, 1, NULL, NULL, 7, '2026-03-22 22:45:42'),
(55, 130, 22, 1, 100.00, 50.00, 7, '2026-03-24 11:53:40'),
(56, 130, 54, 1, 56.00, 28.00, 5, '2026-03-31 11:58:46'),
(57, 131, 54, 1, 42.00, 21.00, 5, '2026-03-31 11:59:29');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `identifier` varchar(200) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `ip_address`, `attempted_at`) VALUES
(13, 'agbenyenusemartin@gmail.cadmin@uaddara.edu.ghom', '::1', '2026-03-20 17:57:00'),
(14, 'admin@uaddara.edu.ghadmin@uaddara.edu.gh', '::1', '2026-03-20 18:28:17'),
(15, 'admin@uaddara.edu.ghadmin@uaddara.edu.gh', '::1', '2026-03-20 18:28:50'),
(18, 'admin', '::1', '2026-03-20 23:20:13'),
(19, 'admin', '::1', '2026-03-20 23:20:19'),
(20, 'admin', '::1', '2026-03-20 23:20:22'),
(21, 'admin@uaddara.edu.ghagbenyenussemartin@gmail.com', '::1', '2026-03-20 23:45:41'),
(22, '+233557869989', '::1', '2026-03-21 00:28:33'),
(23, 'ad', '::1', '2026-03-21 00:31:33'),
(24, 'Diana', '::1', '2026-03-21 21:49:17'),
(25, 'diana', '::1', '2026-03-21 21:59:29'),
(26, 'admin', '::1', '2026-03-21 22:23:10'),
(27, 'admin', '::1', '2026-03-21 22:23:45'),
(28, 'admin', '::1', '2026-03-21 22:23:55'),
(30, 'admin', '::1', '2026-03-21 22:24:34'),
(31, '0004', '::1', '2026-03-22 07:54:07'),
(32, 'dia', '::1', '2026-03-22 22:44:38'),
(33, '0557869981', '::1', '2026-03-23 05:20:12'),
(34, '0557869981', '::1', '2026-03-23 05:22:55'),
(35, '0557869981', '::1', '2026-03-23 05:23:24'),
(36, '81', '::1', '2026-03-23 05:23:43'),
(37, '0557869981admin@school.edu.gh', '::1', '2026-03-23 05:24:14'),
(38, '0557869981', '::1', '2026-03-23 05:24:32'),
(39, '0557869981', '::1', '2026-03-23 05:25:02'),
(40, 'a', '::1', '2026-03-23 05:37:03'),
(41, 'ad', '::1', '2026-03-23 05:37:54'),
(42, 'diana', '::1', '2026-03-23 13:48:00'),
(43, 'admin@uaddara.edu.ghsamuel@gmail.com', '::1', '2026-03-23 13:58:58'),
(44, 'sam@gmail.comadmin@uaddara.edu.gh', '::1', '2026-03-23 14:57:19'),
(45, 'admin@gmail.com', '::1', '2026-03-23 15:14:31'),
(46, 'admin@gmail.com', '::1', '2026-03-23 15:14:58'),
(47, 'admin@gmail.com', '::1', '2026-03-23 15:15:10'),
(48, 'admin@gmail.com', '::1', '2026-03-23 15:16:59'),
(49, 'admin@uaddara.edu.ghadmin@uaddara.edu.gh', '::1', '2026-03-23 16:04:07'),
(50, 'admin@gmail.com', '::1', '2026-03-23 16:04:55'),
(51, 'admin@admin.com', '::1', '2026-03-23 16:05:30'),
(52, 'admin@gmail.com', '::1', '2026-03-23 16:05:50'),
(54, 'admin@uaddara.edu.ghadmin@marabel.com', '::1', '2026-03-23 16:47:31'),
(55, 'admin@marabel.com', '::1', '2026-03-23 16:47:45'),
(56, 'admin@marabel.com', '::1', '2026-03-23 16:47:52'),
(57, 'admin', '::1', '2026-04-04 21:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('success','info','warning','error') DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `link`, `is_read`, `created_at`) VALUES
(1, NULL, 'Academic Year Activated', '\'2025/2026\' has been set as the active academic year.', 'success', '/admin/years', 1, '2026-03-23 11:30:13'),
(2, NULL, 'Term Activated', '\'Term 1\' has been set as the active term.', 'success', '/admin/terms', 1, '2026-03-23 11:31:15'),
(3, NULL, 'Term Activated', '\'Term 2\' has been set as the active term.', 'success', '/admin/terms', 1, '2026-03-23 11:33:05'),
(4, NULL, 'Term Activated', '\'Term 1\' has been set as the active term.', 'success', '/admin/terms', 1, '2026-03-23 11:33:32'),
(5, NULL, 'Term Activated', '\'Term 2\' has been set as the active term.', 'success', '/admin/terms', 1, '2026-03-23 11:33:35'),
(6, NULL, 'Term Activated', '\'Term 1\' has been set as the active term.', 'success', '/admin/terms', 1, '2026-03-23 15:01:24'),
(7, NULL, 'Term Activated', '\'Term 2\' has been set as the active term.', 'success', '/admin/terms', 1, '2026-03-23 15:07:58'),
(8, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-23 16:33:49'),
(9, NULL, 'Results Unpublished', 'Report cards for BASIC 4 have been hidden.', 'info', '/admin/publish', 1, '2026-03-23 16:33:57'),
(10, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 09:50:51'),
(11, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 09:50:51'),
(12, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 09:51:38'),
(13, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 09:51:38'),
(14, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 09:55:03'),
(15, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 09:55:03'),
(16, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 10:09:49'),
(17, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 10:09:49'),
(18, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 10:23:54'),
(19, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 10:23:55'),
(20, NULL, 'Bulk Results Hidden', 'All results for the term have been unpublished.', 'warning', '/admin/publish', 1, '2026-03-25 10:24:14'),
(21, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 10:24:22'),
(22, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 10:24:22'),
(23, NULL, 'Results Unpublished', 'Report cards for BASIC 4 have been hidden.', 'info', '/admin/publish', 1, '2026-03-25 10:24:27'),
(24, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 10:24:50'),
(25, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 10:24:50'),
(26, NULL, 'Results Unpublished', 'Report cards for BASIC 4 have been hidden.', 'info', '/admin/publish', 1, '2026-03-25 11:14:56'),
(27, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 11:15:02'),
(28, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 11:15:02'),
(29, NULL, 'Results Published', 'Report cards for BASIC 1 have been published.', 'success', '/admin/publish', 1, '2026-03-25 11:36:56'),
(30, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 11:49:07'),
(31, 4, 'Results Released', 'Academic results for BASIC 4 are now available for viewing.', 'success', '/parent', 0, '2026-03-25 11:49:07'),
(32, NULL, 'Results Unpublished', 'Report cards for BASIC 4 have been hidden.', 'info', '/admin/publish', 1, '2026-03-25 12:09:07'),
(33, NULL, 'Results Published', 'Report cards for BASIC 4 have been published.', 'success', '/admin/publish', 1, '2026-03-25 12:16:16'),
(34, 4, 'Results Released', 'Academic results for ADU DARKO NATHANIEL (BASIC 4) are available for viewing.', 'success', '/parent', 0, '2026-03-25 12:16:16'),
(35, NULL, 'Results Unpublished', 'Report cards for BASIC 1 have been hidden.', 'info', '/admin/publish', 1, '2026-03-25 12:17:16'),
(36, NULL, 'Term Activated', '\'Term 1 Test\' has been set as the active term.', 'success', '/admin/terms', 1, '2026-03-25 14:27:15'),
(37, NULL, 'Term Activated', '\'Term 2\' has been set as the active term.', 'success', '/admin/terms', 1, '2026-03-25 14:28:01'),
(38, NULL, 'Academic Year Activated', '\'2024/2025\' has been set as the active academic year.', 'success', '/admin/years', 1, '2026-03-25 14:31:24'),
(39, 10, 'Welcome to Uaddara Basic School', 'Your account has been created. Your default password is: password123', 'success', NULL, 0, '2026-03-25 14:38:08'),
(40, 2, 'New Teacher Joined', 'Staff member \'Dorcas AFUA OSEI\' was registered by System Administrator.', 'info', NULL, 1, '2026-03-25 14:38:08'),
(41, NULL, 'Academic Year Activated', '\'2025/2026\' has been set as the active academic year.', 'success', '/admin/years', 1, '2026-03-25 14:39:09'),
(42, 10, 'New Subject Assignment', 'You have been assigned new subject(s) in: BASIC 7, BASIC 8, BASIC 8.', 'success', '/teacher/scores', 0, '2026-03-25 14:40:43'),
(43, 6, 'New Subject Assignment', 'You have been assigned new subject(s) in: BASIC 8, BASIC 8, BASIC 9, BASIC 9.', 'success', '/teacher/scores', 1, '2026-03-25 14:43:45'),
(44, 11, 'Welcome to Uaddara Basic School', 'Your account has been created. Your default password is: password123', 'success', NULL, 0, '2026-03-25 14:45:45'),
(45, 2, 'New Teacher Joined', 'Staff member \'Kennedy SARFO KANKAM\' was registered by System Administrator.', 'info', NULL, 1, '2026-03-25 14:45:45'),
(46, 11, 'New Subject Assignment', 'You have been assigned new subject(s) in: BASIC 8, BASIC 8, BASIC 9.', 'success', '/teacher/scores', 0, '2026-03-25 14:53:49'),
(47, 5, 'New Subject Assignment', 'You have been assigned new subject(s) in: BASIC 4, BASIC 5, BASIC 5, BASIC 7, BASIC 7.', 'success', '/teacher/scores', 0, '2026-03-31 11:53:17'),
(48, 12, 'Welcome to Uaddara Basic School', 'Your account has been created. Your default password is: password123', 'success', NULL, 0, '2026-04-04 21:00:18'),
(49, 2, 'New Teacher Joined', 'Staff member \'Christiana OBIRI\' was registered by System Administrator.', 'info', NULL, 1, '2026-04-04 21:00:18'),
(50, 12, 'New Subject Assignment', 'You have been assigned new subject(s) in: BASIC 1, BASIC 2, BASIC 3, BASIC 4, BASIC 5, BASIC 5, BASIC 6, BASIC 6.', 'success', '/teacher/scores', 0, '2026-04-04 21:01:21'),
(51, 12, 'New Subject Assignment', 'You have been assigned new subject(s) in: BASIC 1, BASIC 2, BASIC 3, BASIC 4, BASIC 5, BASIC 5, BASIC 6, BASIC 6.', 'success', '/teacher/scores', 0, '2026-04-04 21:05:36'),
(52, 12, 'New Subject Assignment', 'You have been assigned new subject(s) in: BASIC 1, BASIC 2, BASIC 3, BASIC 4, BASIC 5, BASIC 5, BASIC 6, BASIC 6.', 'success', '/teacher/scores', 0, '2026-04-04 21:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `otp_tokens`
--

CREATE TABLE `otp_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `phone` varchar(20) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_tokens`
--

INSERT INTO `otp_tokens` (`id`, `phone`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(6, '0501345770', '01887b84cacc4dd22befe85987277917de770115f75b0e35901901f61238c633', '2026-03-22 20:29:57', '2026-03-22 20:29:57', '2026-03-22 20:29:25'),
(10, '0557869989', 'ca45640ee7f3ecf08a683a15caf06ab89ef0c6663f969ecd6c39db5fdc54b936', '2026-04-04 20:13:53', '2026-04-04 20:13:53', '2026-04-04 20:13:35');

-- --------------------------------------------------------

--
-- Table structure for table `predefined_remarks`
--

CREATE TABLE `predefined_remarks` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` enum('teacher','headmaster') NOT NULL,
  `content` text NOT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `predefined_remarks`
--

INSERT INTO `predefined_remarks` (`id`, `category`, `content`, `is_system`, `created_by`, `created_at`) VALUES
(1, 'teacher', 'Shows good understanding of classwork. Keep it up.', 1, NULL, '2026-03-23 05:17:00'),
(2, 'teacher', 'A hardworking student with steady progress.', 1, NULL, '2026-03-23 05:17:00'),
(3, 'teacher', 'Needs to participate more actively in class.', 1, NULL, '2026-03-23 05:17:00'),
(4, 'teacher', 'Has improved significantly this term. Well done.', 1, NULL, '2026-03-23 05:17:00'),
(5, 'teacher', 'Should pay more attention during lessons.', 1, NULL, '2026-03-23 05:17:00'),
(6, 'teacher', 'Demonstrates good behaviour and respect towards others.', 1, NULL, '2026-03-23 05:17:00'),
(7, 'teacher', 'Needs to improve on homework submission.', 1, NULL, '2026-03-23 05:17:00'),
(8, 'teacher', 'A promising student who can do even better with more effort.', 1, NULL, '2026-03-23 05:17:00'),
(9, 'teacher', 'Shows interest in learning and asks relevant questions.', 1, NULL, '2026-03-23 05:17:00'),
(10, 'teacher', 'Performance is satisfactory but there is room for improvement.', 1, NULL, '2026-03-23 05:17:00'),
(11, 'headmaster', 'A disciplined student. Keep striving for excellence.', 1, NULL, '2026-03-23 05:17:00'),
(12, 'headmaster', 'Good performance. Aim higher next term.', 1, NULL, '2026-03-23 05:17:00'),
(13, 'headmaster', 'Shows potential. Work harder to achieve better results.', 1, NULL, '2026-03-23 05:17:00'),
(14, 'headmaster', 'Keep up the good work and remain focused.', 1, NULL, '2026-03-23 05:17:00'),
(15, 'headmaster', 'A commendable effort this term.', 1, NULL, '2026-03-23 05:17:00'),
(16, 'headmaster', 'Needs to take studies more seriously.', 1, NULL, '2026-03-23 05:17:00'),
(17, 'headmaster', 'Exhibits good character. Maintain it.', 1, NULL, '2026-03-23 05:17:01'),
(18, 'headmaster', 'Performance is improving. Keep pushing forward.', 1, NULL, '2026-03-23 05:17:01'),
(19, 'headmaster', 'Parents are encouraged to support learning at home.', 1, NULL, '2026-03-23 05:17:01'),
(20, 'headmaster', 'A satisfactory performance. More effort is required.', 1, NULL, '2026-03-23 05:17:01');

-- --------------------------------------------------------

--
-- Table structure for table `report_card_locks`
--

CREATE TABLE `report_card_locks` (
  `id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `published_by` int(10) UNSIGNED DEFAULT NULL,
  `sms_sent` tinyint(1) NOT NULL DEFAULT 0,
  `sms_sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_card_locks`
--

INSERT INTO `report_card_locks` (`id`, `class_id`, `term_id`, `is_published`, `published_at`, `published_by`, `sms_sent`, `sms_sent_at`) VALUES
(1, 2, 1, 0, '2026-03-21 01:35:15', 2, 0, NULL),
(2, 8, 1, 1, '2026-03-25 12:16:16', 2, 0, NULL),
(14, 1, 1, 0, '2026-03-25 11:36:56', 2, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sba_component_scores`
--

CREATE TABLE `sba_component_scores` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `class_subject_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `individual_test` decimal(5,2) DEFAULT NULL,
  `group_work` decimal(5,2) DEFAULT NULL,
  `class_test` decimal(5,2) DEFAULT NULL,
  `project` decimal(5,2) DEFAULT NULL,
  `sub_total` decimal(6,2) DEFAULT NULL,
  `class_score` decimal(6,2) DEFAULT NULL,
  `entered_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sba_component_scores`
--

INSERT INTO `sba_component_scores` (`id`, `student_id`, `class_subject_id`, `term_id`, `individual_test`, `group_work`, `class_test`, `project`, `sub_total`, `class_score`, `entered_by`, `updated_at`) VALUES
(1, 2, 10, 1, 15.00, 10.00, 15.00, NULL, 40.00, 33.33, 3, '2026-03-20 23:21:11'),
(2, 1, 11, 1, 8.00, 15.00, 15.00, 10.00, 48.00, 40.00, 1, '2026-03-20 23:42:19'),
(3, 3, 18, 1, NULL, NULL, NULL, NULL, 0.00, 0.00, 7, '2026-03-21 18:41:00'),
(4, 18, 10, 1, 6.00, 5.00, 15.00, 15.00, 41.00, 34.17, 3, '2026-03-21 22:01:20'),
(5, 4, 10, 1, 2.00, 6.00, 10.00, 15.00, 33.00, 27.50, 3, '2026-03-21 22:01:58'),
(6, 5, 10, 1, NULL, NULL, 15.00, NULL, 15.00, 12.50, 3, '2026-03-21 22:02:20'),
(7, 130, 19, 1, 15.00, 15.00, 15.00, 15.00, 60.00, 50.00, 7, '2026-03-24 11:33:18'),
(8, 131, 19, 1, 10.00, 11.00, NULL, 12.00, 33.00, 27.50, 7, '2026-03-25 05:19:54'),
(9, 112, 19, 1, 14.00, 13.00, 15.00, 14.00, 56.00, 46.67, 7, '2026-03-25 05:14:13'),
(10, 113, 19, 1, 8.00, 9.00, 10.00, 8.00, 35.00, 29.17, 7, '2026-03-22 09:42:27'),
(11, 132, 19, 1, 2.00, 14.00, 13.00, 13.00, 42.00, 35.00, 7, '2026-03-25 09:36:52'),
(12, 133, 19, 1, 3.00, 10.00, 11.00, 10.00, 34.00, 28.33, 7, '2026-03-25 09:37:17'),
(13, 114, 19, 1, 15.00, 15.00, 14.00, 15.00, 59.00, 49.17, 7, '2026-03-22 09:42:27'),
(14, 134, 19, 1, 9.00, 8.00, 7.00, 9.00, 33.00, 27.50, 7, '2026-03-22 09:42:27'),
(15, 135, 19, 1, 13.00, 12.00, 13.00, 11.00, 49.00, 40.83, 7, '2026-03-22 09:42:27'),
(16, 136, 19, 1, 10.00, 11.00, 12.00, 10.00, 43.00, 35.83, 7, '2026-03-22 09:42:27'),
(17, 137, 19, 1, 7.00, 8.00, 6.00, 7.00, 28.00, 23.33, 7, '2026-03-22 09:42:27'),
(18, 138, 19, 1, 14.00, 13.00, 15.00, 14.00, 56.00, 46.67, 7, '2026-03-22 09:42:27'),
(19, 139, 19, 1, 11.00, 12.00, 10.00, 12.00, 45.00, 37.50, 7, '2026-03-22 09:42:27'),
(20, 140, 19, 1, 12.00, 11.00, 13.00, 11.00, 47.00, 39.17, 7, '2026-03-22 09:42:27'),
(21, 141, 19, 1, 6.00, 7.00, 8.00, 6.00, 27.00, 22.50, 7, '2026-03-22 09:42:27'),
(22, 142, 19, 1, 13.00, 14.00, 12.00, 13.00, 52.00, 43.33, 7, '2026-03-22 09:42:27'),
(23, 115, 19, 1, 15.00, 14.00, 15.00, 15.00, 59.00, 49.17, 7, '2026-03-22 09:42:27'),
(24, 116, 19, 1, 9.00, 10.00, 9.00, 8.00, 36.00, 30.00, 7, '2026-03-22 09:42:27'),
(25, 117, 19, 1, 12.00, 12.00, 11.00, 13.00, 48.00, 40.00, 7, '2026-03-22 09:42:27'),
(26, 118, 19, 1, 10.00, 9.00, 10.00, 11.00, 40.00, 33.33, 7, '2026-03-22 09:42:27'),
(27, 143, 19, 1, 13.00, 12.00, 14.00, 11.00, 50.00, 41.67, 7, '2026-03-22 09:42:27'),
(28, 119, 19, 1, 10.00, 11.00, 9.00, 12.00, 42.00, 35.00, 7, '2026-03-22 09:42:27'),
(29, 120, 19, 1, 14.00, 13.00, 15.00, 14.00, 56.00, 46.67, 7, '2026-03-22 09:42:27'),
(30, 121, 19, 1, 8.00, 9.00, 10.00, 8.00, 35.00, 29.17, 7, '2026-03-22 09:42:27'),
(31, 160, 19, 1, 12.00, 14.00, 13.00, 13.00, 52.00, 43.33, 7, '2026-03-22 09:42:27'),
(32, 122, 19, 1, 11.00, 10.00, 11.00, 10.00, 42.00, 35.00, 7, '2026-03-22 09:42:27'),
(33, 144, 19, 1, 15.00, 15.00, 14.00, 15.00, 59.00, 49.17, 7, '2026-03-22 09:42:27'),
(34, 123, 19, 1, 9.00, 8.00, 7.00, 9.00, 33.00, 27.50, 7, '2026-03-22 09:42:27'),
(35, 145, 19, 1, 13.00, 12.00, 13.00, 11.00, 49.00, 40.83, 7, '2026-03-22 09:42:27'),
(36, 124, 19, 1, 10.00, 11.00, 12.00, 10.00, 43.00, 35.83, 7, '2026-03-22 09:42:27'),
(37, 125, 19, 1, 7.00, 8.00, 6.00, 7.00, 28.00, 23.33, 7, '2026-03-22 09:42:27'),
(38, 146, 19, 1, 14.00, 13.00, 15.00, 14.00, 56.00, 46.67, 7, '2026-03-22 09:42:27'),
(39, 147, 19, 1, 11.00, 12.00, 10.00, 12.00, 45.00, 37.50, 7, '2026-03-22 09:42:27'),
(40, 159, 19, 1, 12.00, 11.00, 13.00, 11.00, 47.00, 39.17, 7, '2026-03-22 09:42:27'),
(41, 148, 19, 1, 6.00, 7.00, 8.00, 6.00, 27.00, 22.50, 7, '2026-03-22 09:42:27'),
(42, 149, 19, 1, 13.00, 14.00, 12.00, 13.00, 52.00, 43.33, 7, '2026-03-22 09:42:27'),
(43, 150, 19, 1, 15.00, 14.00, 15.00, 15.00, 59.00, 49.17, 7, '2026-03-22 09:42:27'),
(44, 151, 19, 1, 9.00, 10.00, 9.00, 8.00, 36.00, 30.00, 7, '2026-03-22 09:42:27'),
(45, 152, 19, 1, 12.00, 12.00, 11.00, 13.00, 48.00, 40.00, 7, '2026-03-22 09:42:27'),
(46, 153, 19, 1, 10.00, 9.00, 10.00, 11.00, 40.00, 33.33, 7, '2026-03-22 09:42:27'),
(47, 154, 19, 1, 13.00, 12.00, 14.00, 11.00, 50.00, 41.67, 7, '2026-03-22 09:42:27'),
(48, 126, 19, 1, 10.00, 11.00, 9.00, 12.00, 42.00, 35.00, 7, '2026-03-22 09:42:27'),
(49, 127, 19, 1, 14.00, 13.00, 15.00, 14.00, 56.00, 46.67, 7, '2026-03-22 09:42:27'),
(50, 128, 19, 1, 8.00, 9.00, 10.00, 8.00, 35.00, 29.17, 7, '2026-03-22 09:42:27'),
(51, 155, 19, 1, 12.00, 14.00, 13.00, 13.00, 52.00, 43.33, 7, '2026-03-22 09:42:27'),
(52, 156, 19, 1, 11.00, 10.00, 11.00, 10.00, 42.00, 35.00, 7, '2026-03-22 09:42:27'),
(53, 157, 19, 1, 3.00, 15.00, 14.00, 15.00, 47.00, 39.17, 7, '2026-03-25 09:43:58'),
(54, 129, 19, 1, 9.00, 8.00, 7.00, 9.00, 33.00, 27.50, 7, '2026-03-22 09:42:27'),
(55, 158, 19, 1, 13.00, 12.00, 13.00, 11.00, 49.00, 40.83, 7, '2026-03-22 09:42:27'),
(56, 3, 19, 1, NULL, NULL, NULL, NULL, 0.00, 0.00, 7, '2026-03-22 22:45:34'),
(57, 130, 22, 1, 15.00, 15.00, 12.00, 15.00, 57.00, 47.50, 7, '2026-03-24 11:53:38'),
(58, 131, 22, 1, NULL, NULL, NULL, NULL, 0.00, 0.00, 7, '2026-03-25 01:58:24'),
(59, 113, 22, 1, NULL, NULL, NULL, NULL, 0.00, 0.00, 7, '2026-03-25 01:58:40'),
(60, 130, 54, 1, 15.00, 14.00, 15.00, 15.00, 59.00, 49.17, 5, '2026-03-31 11:58:33'),
(61, 131, 54, 1, 10.00, 15.00, 13.00, 15.00, 53.00, 44.17, 5, '2026-03-31 11:59:38');

-- --------------------------------------------------------

--
-- Table structure for table `school_levels`
--

CREATE TABLE `school_levels` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `code` varchar(10) NOT NULL,
  `sort_order` tinyint(3) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_levels`
--

INSERT INTO `school_levels` (`id`, `name`, `code`, `sort_order`) VALUES
(1, 'Lower Primary', 'LP', 1),
(2, 'Upper Primary', 'UP', 2),
(3, 'Junior High School', 'JHS', 3);

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipient_phone` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sms_type` enum('otp','report_card','broadcast','reminder') NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT NULL,
  `response_data` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `recipient_phone`, `message`, `sms_type`, `sent_at`, `status`, `response_data`) VALUES
(1, '0557869989', 'Your Uaddara Basic School login code is: 160853. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-21 00:20:50', 'sent', '{}'),
(2, '0557869989', 'Your Uaddara Basic School login code is: 666097. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-21 00:28:51', 'sent', '{}'),
(3, '0557869989', 'Your Uaddara Basic School login code is: 831036. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-21 01:36:37', 'sent', '{}'),
(4, '0557869989', 'Your Uaddara Basic School login code is: 642126. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-22 07:52:50', 'sent', '{}'),
(5, '0557869989', 'Your Uaddara Basic School login code is: 519313. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-22 09:46:41', 'sent', '{}'),
(6, '0501345770', 'Your Uaddara Basic School login code is: 890671. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-22 20:29:27', 'sent', '{}'),
(7, '0557869989', 'Kindly note that your child will not be coming to school tomorrow in accordance with the Government’s directive.', 'broadcast', '2026-03-22 21:03:14', 'sent', '{}'),
(8, '0501345770', 'Kindly note that your child will not be coming to school tomorrow in accordance with the Government’s directive.', 'broadcast', '2026-03-22 21:03:15', 'sent', '{}'),
(9, '0557869989', 'Your Uaddara Basic School login code is: 501358. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-24 05:49:29', 'sent', '{}'),
(10, '0557869989', 'Your Uaddara Basic School login code is: 540285. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-25 11:46:13', 'failed', 'Unknown request error <0>.'),
(11, '0557869989', 'Your Uaddara Basic School login code is: 155459. Valid for 5 minutes. Do not share.', 'broadcast', '2026-03-25 11:47:23', 'sent', '{}'),
(12, '0557869989', 'Dear Parent, results for ADU DARKO NATHANIEL (Current Term ) have been published. View now at the portal.', 'report_card', '2026-03-25 12:16:17', 'sent', '{}'),
(13, '0557869989', 'Your Uaddara Basic School login code is: 355800. Valid for 5 minutes. Do not share.', 'broadcast', '2026-04-04 20:13:36', 'sent', '{}');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id_number` varchar(50) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `photo_path` varchar(500) DEFAULT NULL,
  `current_class_id` int(10) UNSIGNED DEFAULT NULL,
  `academic_year_id` int(10) UNSIGNED DEFAULT NULL,
  `pin_hash` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','transferred') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id_number`, `full_name`, `surname`, `date_of_birth`, `gender`, `photo_path`, `current_class_id`, `academic_year_id`, `pin_hash`, `status`, `created_at`) VALUES
(1, '0001', 'Martin Agbenyenuse', 'Agbenyenuse', '2016-03-02', 'Male', NULL, 8, 1, NULL, 'inactive', '2026-03-20 18:02:41'),
(2, '0002', 'Adeline Agbenyenuse', 'Agbenyenuse', '2021-06-20', 'Male', NULL, 8, 1, NULL, 'inactive', '2026-03-20 21:52:50'),
(3, '0003', 'Adomako Cecilia', '', '2016-07-26', 'Female', NULL, 8, 1, NULL, 'inactive', '2026-03-21 09:33:22'),
(4, '0004', 'ABDULLAH    NEKANAWIA ABDUI MUIZZ', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(5, '0005', 'ADJEI  VICENT', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(6, '0006', 'AGANDA COLLINS', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(7, '0007', 'BROBBEY AFRIYIE OLIVER', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(8, '0008', 'MINTA KYEI  DOMINIC', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(9, '0009', 'MOHAMMED  JUNAID', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(10, '0010', 'BOUR  KWAKU NELSON  JUNIOR', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(11, '0011', 'OPOKU  BAFFOE  GIDEON', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(12, '0012', 'OSEI  KOFI  AKYEDIE', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(13, '0013', 'OSEI  TUTU  MAXIMILLIAN', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(14, '0014', 'OSONDU  MESHACH  MMASICHUKWU', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(15, '0015', 'ADJEI  WILLIAM  SIKAPA', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(16, '0016', 'NYARKO OVERBLESS  REXFORD', NULL, NULL, 'Male', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(17, '0017', 'OWUSU-KYEI ANIMUONYAM', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(18, '0018', 'ABANGA  MIVAN', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(19, '0019', 'AGYEI   SUSSANA', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(20, '0020', 'ASORE  BAKHITA  BANIGAN', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(21, '0021', 'ATIM  GREADNESS  AJAMLENALIM', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(22, '0022', 'BOBIE  FOSUAA ANTOINETTE', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(23, '0023', 'FRIMPONG  ANASTASIA    AFRIYIE', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(24, '0024', 'GORDOR  THERESA ADZO  DEDE', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(25, '0025', 'KANBIGS  AYINBUNO  JOSEPHINE', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(26, '0026', 'NKRUMAH  SOMPA  NYARKO NYAMEDO', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(27, '0027', 'NUMENYA  FLORENCE', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(28, '0028', 'NSENKYIRE MILLICENT BAFFOUR', NULL, NULL, 'Female', NULL, 1, 1, NULL, 'active', '2026-03-21 21:58:18'),
(29, '0029', 'ACHEAMPONG OPOKU RUDOLF', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(30, '0030', 'AMANKWAH SARKODIE DICKSON', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(31, '0031', 'AMOAH ADAGYINE JUNIOR', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(32, '0032', 'ASARE BEDIAKO AYITEY JAYDEN', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(33, '0033', 'ASARE NELSON BOATENG ISAAC', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(34, '0034', 'ASUBONTENG YEBOAH K. BARZILLAI', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(35, '0035', 'BABA SAMPANA NATHANIEL YIRIBEY', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(36, '0036', 'BOAKYE BOATENG KWAKU DOMINIC', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(37, '0037', 'DUAH BOAKYE KWABENA MICHAEL', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(38, '0038', 'DUAH FRIMPONG REINDORFSON YAW', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(39, '0039', 'FESTUS ADOM FRIMPONG ASANTE K.', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(40, '0040', 'KWARTENG YAMOAH OHENEBA MICHAEL', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(41, '0041', 'NSIAH ANTHONY JUNIOR', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(42, '0042', 'OBENG GODSWAY', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(43, '0043', 'OPPONG BERKO JOEL', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(44, '0044', 'OSEI OWUSU MENSAH', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(45, '0045', 'OWUSU CARRICK NICHOLAS', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(46, '0046', 'OWUSU FAITHFUL LISTOWELL', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(47, '0047', 'OWUSU KUMI DUAH JASON', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(48, '0048', 'OWUSU SONY', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(49, '0049', 'SAM DESMOND', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(50, '0050', 'SARFO BERKO FREDRICK', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(51, '0051', 'SULEMAN ISSAKA', NULL, NULL, 'Male', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(52, '0052', 'ACKAH AHUM AFIBA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(53, '0053', 'ADU AFIA SIKAPA JULIET', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(54, '0054', 'AHETO ELIKIM ELIZABETH', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(55, '0055', 'AKOGO ETORNAM PRECIOUS', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(56, '0056', 'AMOAKO AGYEIWAA ANIMOUNYAM', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(57, '0057', 'AMPODU OWUSUAA AFIA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(58, '0058', 'AMPONSAH ODUROWAA BEZAELLA S.', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(59, '0059', 'BENTUM NANA AMA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(60, '0060', 'BOAKYE ANGEL', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(61, '0061', 'GYAMFI ANIMA ODEHYEBA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(62, '0062', 'OPPONG AGYEMANG CHELSEA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(63, '0063', 'OWUSU NANA BLESSING', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(64, '0064', 'OWUSU SONIA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(65, '0065', 'SARPONG ADWUBI ADEPA DIANA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(66, '0066', 'TAYLOR NYAMEKYE MICHELLE', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(67, '0067', 'YERILEBLENA POWONPAA FAITH', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(68, '0068', 'APPIAH AGYEIWAA AKUA DANIELLA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(69, '0069', 'GOLOPONU ALBERTA', NULL, NULL, 'Female', NULL, 6, 1, NULL, 'active', '2026-03-21 22:05:40'),
(70, '0070', 'ADU GYAMFI WALTER YAW TAWIA', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(71, '0071', 'AMOAKO BRAIN', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(72, '0072', 'AFRIFA CHARLES', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(73, '0073', 'AMOAH ANTWI NANA', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(74, '0074', 'ATTIPOE KELVIN', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(75, '0075', 'BAAH FRIMPONG BRIGHT', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(76, '0076', 'BOAKYE NHYIRABA OFORI EMMANUEL', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(77, '0077', 'BOLEPA-WESIE KWAME DIVINE', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(78, '0078', 'KWARTENG AGYEI DUAH DAVID', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(79, '0079', 'NSOBILA CHRISTIAN', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(80, '0080', 'NYAMEKYE AKWASI AIKINS', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(81, '0081', 'OBI CHIKAODI PETER', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(82, '0082', 'GOODLUCK ADOM JONATHAN', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(83, '0083', 'OFORI BOATENG KWAME', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(84, '0084', 'OFORI DESMOND', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(85, '0085', 'OKYERE SAKYI DANIEL', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(86, '0086', 'OPOKU AGYEMANG TWUMASI ELIJAH', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(87, '0087', 'OSEI BOATENG VICTOR PEREKEME', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(88, '0088', 'OWUSU GYIMAH ERNEST', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(89, '0089', 'OWUSU KODUA NANA YAW', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(90, '0090', 'PAGAYERE ABRADAGA PRINCE', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(91, '0091', 'ASARE NANA SAE KINGSLEY', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(92, '0092', 'DOMAGUMA BERNARD', NULL, NULL, 'Male', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(93, '0093', 'DONKOR NHYIRA', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(94, '0094', 'ACHEAMPONG ADOMAH NANA AMA', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(95, '0095', 'ADOKPOKA ATANGA CHRISTABLE', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(96, '0096', 'AGYEI GLORY', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(97, '0097', 'ASIAMAH ASAKOMAH KEREN', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(98, '0098', 'ASANTEWAA BRANTUO LOIS', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(99, '0099', 'ABBOR MENSAH NIGHTANGLE', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(100, '0100', 'BOAKYE MANSAH EMMANUELLA', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(101, '0101', 'DUAH ABIGAIL', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(102, '0102', 'GYAMERAH SERWAA RIGHTEOUS', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(103, '0103', 'KWARTENG AGYAPOMAA HOLLIANN', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(104, '0104', 'MENSAH KONADU GRACE', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(105, '0105', 'MILLS NAA RACHAEL', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(106, '0106', 'OPOKU MENSAH TIWAA', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(107, '0107', 'OWUSU JOELLA', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(108, '0108', 'OWUSU KYEI ASEDA', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(109, '0109', 'TOAANO MERCY', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(110, '0110', 'BONSU GYAN NANA GODSLOVE', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(111, '0111', 'BIBIKININ YENNUMI BERNICE', NULL, NULL, 'Female', NULL, 7, 1, NULL, 'active', '2026-03-22 09:03:27'),
(112, '0112', 'ADU DARKO NATHANIEL', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(113, '0113', 'ADU SARKODIE JARED', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(114, '0114', 'AMANKWAH DANIEL', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(115, '0115', 'BAAH OTOO LANCELOT', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(116, '0116', 'BERKO CHARLES OPPONG', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(117, '0117', 'BOAKYE KWAME PEREZ', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(118, '0118', 'BONSU AGYEI ELVIS', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(119, '0119', 'BOUR KOJO DESMOND', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(120, '0120', 'BOYE KOFI CALEB', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(121, '0121', 'DABO KWASI FRANK', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(122, '0122', 'DUAH KUMAH ASHLEY', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(123, '0123', 'FOSU OSEI-AKOTO NATHAN', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(124, '0124', 'FRIMPONG GODFRED', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(125, '0125', 'FRIMPONG KOFI DANIEL', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(126, '0126', 'OWUSU AMPONG JEAN', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(127, '0127', 'OWUSU ANTWI NANA KWAME', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(128, '0128', 'OWUSU KWABENA LORD', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(129, '0129', 'YEBOAH NKUNIM ANIN', NULL, NULL, 'Male', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(130, '0130', 'ACKAH BOZOMAH YABA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(131, '0131', 'ADJEI NINA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(132, '0132', 'ADUSEI DANKWAH AKUA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(133, '0133', 'AHETO DEBORAH ETORNAM', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(134, '0134', 'AMO WIREDUWAA REGINA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(135, '0135', 'AMOAH ADOMA ESTHER', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(136, '0136', 'AMOAH ADOMA PURITY', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(137, '0137', 'AMOAKO MARY ANN', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(138, '0138', 'AMPONSAH AMA SERWAA MEHETABEL', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(139, '0139', 'ANSAH OWUSU JULIANA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(140, '0140', 'APPIAH JOY MILLICENT', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(141, '0141', 'ASAMOAH DUFIE SARAH', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(142, '0142', 'ASORE ABAMDEN MYRA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(143, '0143', 'BORDEN KONADEN RHODALYN', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(144, '0144', 'DUAH KWARTEMAA IDA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(145, '0145', 'FRIMPONG ANIMWAA BLESSING', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(146, '0146', 'GYAMPOH  MIRABEL KOKWE MCENOCH', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(147, '0147', 'MARFO ASANTEWAA GEORGETTE', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(148, '0148', 'MENSAH ADUTUMWAA RICHLOVE', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(149, '0149', 'NARTEY THYWILL', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(150, '0150', 'NKRUMAH TAKYIWAA  ANIMUONYAM KRISTODEA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(151, '0151', 'OFORI ANTWIWAA NANA ADWOA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(152, '0152', 'OHENEWAA CECILIA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(153, '0153', 'OWUSU  AGYEMANG ADUTWUMWAA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(154, '0154', 'OWUSU  BANAHENE KEZIA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(155, '0155', 'SARPONG AGYEIWAA AKUA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(156, '0156', 'TENABU  FRIMPONG SHANTEL', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(157, '0157', 'TETTEY DEDE  ALBERTA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(158, '0158', 'YUSSIF  HANIFA MANDEIYA', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(159, '0159', 'MARIO OWUSU MENSAH DIWOMERE', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(160, '0160', 'DAUDA USMAN ARTHUR', NULL, NULL, 'Female', NULL, 8, 1, NULL, 'active', '2026-03-22 09:04:26'),
(161, '0161', 'ACHEAMPONG KWABENA FORKUO', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(162, '0162', 'ADOMAKO L. KWABENA FOFIE', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(163, '0163', 'ADU ALVIN DARKO', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(164, '0164', 'ADU GRAHAM GYAMFI', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(165, '0165', 'ADU GRANT GYAMFI', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(166, '0166', 'AGYEI RANSFORD', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(167, '0167', 'AMOAH GODWIN WIAH', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(168, '0168', 'AMPADU AKWASI OBENG', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(169, '0169', 'APPIAH JASON NKUNIM OPOKU', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(170, '0170', 'ASAMOAH EMMANUEL ANTWI', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(171, '0171', 'ASAMOAH KALEB N. KWADWO', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:21'),
(172, '0172', 'AYAABA PAUL', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(173, '0173', 'BUDUON T. BANYISAH NIAYOR', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(174, '0174', 'OPOKU DESMOND DANSO', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(175, '0175', 'GYIMAH BENEDICT ADDO', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(176, '0176', 'MENKAH KWAME A. BOATENG', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(177, '0177', 'MENSAH SAMUEL KYEI', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(178, '0178', 'ODURO AMPONSAH KOFI D. OPPONG', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(179, '0179', 'OSEI AARON YIRENKYI', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(180, '0180', 'OSEI JUDE NSIAH', NULL, NULL, 'Male', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(181, '0181', 'AFRIFA AKOSUA BOATEMAA', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(182, '0182', 'ASARE AFIA SAAH', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(183, '0183', 'ASARE RUTH DANSOWAA', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(184, '0184', 'ATTEY MAWUTOR LINA', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(185, '0185', 'AYAABA PAULINA', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(186, '0186', 'BAFFOUR-A. SPENCER MONEY A.', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(187, '0187', 'BOATENG CAROL BOAHEMAA', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(188, '0188', 'COLEMAN CHERYL COMFORT', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(189, '0189', 'GYAMFI KEREN POKUAA', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(190, '0190', 'IDDRISU NADIATU', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(191, '0191', 'MENSAH BERNICE SARPONG', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(192, '0192', 'OBENEWAA ABIGAIL', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(193, '0193', 'OPOKU VIRGINIA NYAMEKYE', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(194, '0194', 'SERWAA JELILA', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(195, '0195', 'SEY WINIFRED ANOKYEWAA', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(196, '0196', 'SHIPHRAH B. N. AKUA BIRAGO', NULL, NULL, 'Female', NULL, 9, 1, NULL, 'active', '2026-03-22 09:05:22'),
(197, '0197', 'ADJEI REUEL EL-DAN OWUSU BEMPAH', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(198, '0198', 'AGYEMANG-MINTA NANA YAW', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(199, '0199', 'APPIAH JESSE GOODLUCK', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(200, '0200', 'APPIAH SAMUEL ADUBOFOUR', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(201, '0201', 'ASAMOAH OHENE PRINCE', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(202, '0202', 'ASANTE NANA KOFI  FAITH', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(203, '0203', 'ASSIFUAH JEWEL AUGUSTUS', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(204, '0204', 'ATANGA BISMARK', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(205, '0205', 'BOAHEN JAYDEN KOFI', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(206, '0206', 'BOATENG PEPRAH PAULUX', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(207, '0207', 'BONSU KONADU AKYEDIE', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(208, '0208', 'DARKO ACQUAH REUBEN', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(209, '0209', 'DOMFE PAUL', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(210, '0210', 'FRIMPONG ERIC KWADWO ASARE', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(211, '0211', 'GYAMPOH CLARENCE MCENOCH BOAFO', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(212, '0212', 'IREBO LUCKY', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(213, '0213', 'OFORI OTABIL MENSAH', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(214, '0214', 'OKYERE JOEL ACHEAMPONG', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(215, '0215', 'OPOKU MARVIN TUTU', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(216, '0216', 'OPOKU OTHNIEL ASAFO AGYEI', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(217, '0217', 'OSEI KWAME BENARD', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(218, '0218', 'OSEI MICHEAL', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(219, '0219', 'OSEI OWUSU ADUTWUM', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(220, '0220', 'OSEI TUTU DANIEL AGYEMANG', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(221, '0221', 'OWUSU AKYEAW MICHEAL', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(222, '0222', 'OWUSU MENSAH NANA KOFI', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(223, '0223', 'SARFO AKWASI DERRICK', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(224, '0224', 'SARKODIE GIFTED NANA KESSE', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(225, '0225', 'YAKUBU MUKAILA', NULL, NULL, 'Male', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(226, '0226', 'ABDUL RAKIA WAHAB', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(227, '0227', 'ADJEI COMFORT', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(228, '0228', 'ADU ISSABELLA ELODE', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(229, '0229', 'AGYEMANG SARAH', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(230, '0230', 'AKUOKO EMMANUELLA SERWAA N.', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(231, '0231', 'ASARE PRINCESS NHYIRABA', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(232, '0232', 'AZIBAH ZOE WINGPANG', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(233, '0233', 'BAAH RACHEAL AFRANEWAA', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(234, '0234', 'BOAHEN AMA POKUAA', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(235, '0235', 'BOAKYE KWARTENG BOAHEMAA', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(236, '0236', 'BOATENG DANIELLA OWUSUWAA', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(237, '0237', 'DWOMOH PATRICIA', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(238, '0238', 'KOTOKA GRACELYN AFI SEDEM', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(239, '0239', 'KUSIWAA MARY', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(240, '0240', 'MENSAH A. GYAMFUAH CHRISTABEL', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(241, '0241', 'MENSAH ANGEL', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(242, '0242', 'MENSAH ROSE', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(243, '0243', 'NKRUMAH PRINCESS NYAMEKYE', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(244, '0244', 'NTI ASARE NHYIRA LOVELY', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(245, '0245', 'OPOKU PERSIS', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(246, '0246', 'OSEI SAAH QUEENSTER', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(247, '0247', 'SAFOWAA AMA', NULL, NULL, 'Female', NULL, 11, 1, NULL, 'active', '2026-03-22 19:11:22'),
(248, '0248', 'ADDAI INSPIRATION', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(249, '0249', 'ADUSEI ISAAC', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(250, '0250', 'APEFA COFFIE EMMANUEL', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(251, '0251', 'APPIAH GODWIN', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(252, '0252', 'ANTWI BOASIAKO DAVID', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(253, '0253', 'BIBIKININ YENUYAL HASSON', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(254, '0254', 'BOATENG REX-EL LUCIOUS', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(255, '0255', 'KANBIGS DOMINIC', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(256, '0256', 'MAWULI KWEKU EBENEZER', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(257, '0257', 'OSEI TUTU ABLE', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(258, '0258', 'OWUSU OSEI GODBLESS', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(259, '0259', 'PEPRAH BOAKYE MARVIN', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(260, '0260', 'VICTOR ROBERT KAMEL', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(261, '0261', 'OPOKU NYAMEKYE NANA KOFI', NULL, NULL, 'Male', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(262, '0262', 'ACHEAMPONG GUSTLINE', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:53'),
(263, '0263', 'ABROWAA QUANSAH LILIAN', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(264, '0264', 'ADROWAA QUANSAH LILY', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(265, '0265', 'ADUTWUMWAA VALENTINA', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(266, '0266', 'ANKOMA ADELAIDE', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(267, '0267', 'AMANKWAA F. ABIGAIL', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(268, '0268', 'AMANKONA A. MARY', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(269, '0269', 'ASANTE SARFO JULIANA', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(270, '0270', 'BEESI A. ROSELLA ARTHUR', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(271, '0271', 'BOAKYEWAA PEPRAH MIRIAM', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(272, '0272', 'BRUWAH AKUA NHYIRA', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(273, '0273', 'FORDJOUR MANU SHERYL', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(274, '0274', 'FOSUHEMAA NTIM ABIGAIL', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(275, '0275', 'OWUSUWAA EDIT', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(276, '0276', 'PAINSTIL CHRISTINE', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(277, '0277', 'TWENEBOAH DIVINE LOVE', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(278, '0278', 'VICTORIA AMO TIMA', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(279, '0279', 'VORMAWAH WIAFEWAA COMFORT', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(280, '0280', 'VICTORIA OWUSU NYARKO KODUA', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(281, '0281', 'EYIMYAM NYANEMA ANSAH', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(282, '0282', 'ABROAMPAH LAWRENCIA OHENEWAA', NULL, NULL, 'Female', NULL, 10, 1, NULL, 'active', '2026-03-22 19:11:54'),
(283, '0283', 'ANARFI DANNIS BRUNO', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(284, '0284', 'ADU NANA ERNEST', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(285, '0285', 'ADU TUTU ISHMAEL', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(286, '0286', 'AFOAKWAH SAMUEL', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(287, '0287', 'AGBEKO RANSFORD', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(288, '0288', 'AGYEI ERNEST', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(289, '0289', 'ANTWI KELVIN NANA', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(290, '0290', 'APPIAH SAMUEL', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(291, '0291', 'ASARE GABRIEL', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(292, '0292', 'ASARE YEBOAH ASANTE OBREMPONG', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(293, '0293', 'BAAH-NUAMAH KWASI BOACHIE', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(294, '0294', 'BAFFOUR-ASARE SAM', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(295, '0295', 'BERCHIE ANTWI ELDRED', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(296, '0296', 'BOAHEN GODWIN', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(297, '0297', 'BOAKYE KWAKU', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(298, '0298', 'BOATENG EVANS KOFI', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(299, '0299', 'BOATENG GIDEON', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(300, '0300', 'BOATENG JUSTICE AKWASI', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(301, '0301', 'BOATENG PRINCE', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(302, '0302', 'BOTWE ALAN DIVINE', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(303, '0303', 'FRIMPONG OPOKU CHRISTIAN', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(304, '0304', 'KORANTENG NANA EDUA RONY', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(305, '0305', 'NTI KYEI DAVID', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(306, '0306', 'OSEI BOAMAH OBED', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(307, '0307', 'OWUSU ACHEAMPONG  CLAUDE', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(308, '0308', 'OWUSU AFRIYIE JUNIOR', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(309, '0309', 'ROCKSON OBO KELVEN', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(310, '0310', 'TANDOH ELVIS', NULL, NULL, 'Male', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(311, '0311', 'BAALAH AKOSUA AUDERY', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(312, '0312', 'ACHEAMPONG AFUA AFRIYIE', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(313, '0313', 'ACQUAH BAAWAH URSULA EWURAMA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(314, '0314', 'ADU-POKU KENDRA JEREMIE', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(315, '0315', 'ADUSEI EDLYN', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(316, '0316', 'AGYEMANG ACHEAMPONMAA SHAMIMA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(317, '0317', 'AGYEMANG ASANTEWAA AMA ANGEL', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(318, '0318', 'AHENFWAA NANA ESI', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(319, '0319', 'AMOATENG ADWOA ACHIAA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(320, '0320', 'AMPONG VASANA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(321, '0321', 'ASIAMAH COMFORT LORRITA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(322, '0322', 'AWUDU BLESSING', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(323, '0323', 'BOACHIE AKUA AMOANIMAA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(324, '0324', 'BOAKYE KUMAH ELDOXA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(325, '0325', 'BOATENG HARRIS', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(326, '0326', 'BROBBEY AGYEMANG JANET', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(327, '0327', 'DONKOR ACHEAMPONMAA ANGEL', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(328, '0328', 'HOWARD SERWAA MARY', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(329, '0329', 'KUATEWO CELESTINE ELORM', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(330, '0330', 'MAAWAN EVELYN', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(331, '0331', 'NHYIRA KEREN NYAMEKYE', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(332, '0332', 'ODAME PRINCESS SERWAA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(333, '0333', 'OFORI AGNES', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(334, '0334', 'OSEI CATHERINE', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(335, '0335', 'OWUSU ANIMA COVENANT', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(336, '0336', 'PEPRAH JOSEPHINE', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(337, '0337', 'POKUAA MAAME YAA BLESSING', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(338, '0338', 'YEBOAH POMAA IMMACULATE', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(339, '0339', 'YEBOAH QUEENSTER YAA', NULL, NULL, 'Female', NULL, 2, 1, NULL, 'active', '2026-03-22 19:12:07'),
(340, '0340', 'ADOM SARFO NATHAN', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(341, '0341', 'ADU  DARKO PAVEL', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(342, '0342', 'ADU EMMANUEL JUNIOR', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(343, '0343', 'ADU GYAMFI MICHAEL', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(344, '0344', 'AKOSAH MICHAEL', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(345, '0345', 'OSEI ANTHONY MENSAH', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(346, '0346', 'APPIAH KING DAVIDS', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(347, '0347', 'ARTHUR BEESI PRINCE PERRY', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(348, '0348', 'ASANTE JUFFIL', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(349, '0349', 'ASARE DAMOAH KWAKU', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(350, '0350', 'ASSUMENG BAFFOUR JADEN', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(351, '0351', 'BINEY LORD', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(352, '0352', 'BOADI ASAMOAH  GODFRED', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(353, '0353', 'BOATENG OHENE RONALD', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(354, '0354', 'BORDEN ODURO ASARE AGYEI', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(355, '0355', 'OTENG BRYAN', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(356, '0356', 'BANAHENE CURTIS ADU', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(357, '0357', 'ADOBAW EMMANUEL ATO BUABENG', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(358, '0358', 'GYIMAH KOFI JOSHUA', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(359, '0359', 'KARIKARI PRINCE', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(360, '0360', 'AVIO KIRAN WEDOBA', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(361, '0361', 'OSEI BONSU WYCOFF', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(362, '0362', 'OTOO STEPHEN', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(363, '0363', 'OWUSU ACHIAW RICHARD', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(364, '0364', 'OWUSU ANSAH GYAMFI  NANA', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(365, '0365', 'SARFO KANTANKA MENSAH KWAME', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(366, '0366', 'SARKODIE RUBEN', NULL, NULL, 'Male', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(367, '0367', 'ADAMS-PAMBOA KAHSI NAQIA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(368, '0368', 'AGYARE ANTWIWAA PATIENCE', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(369, '0369', 'AGYEI OWUSUAA PAFFLINA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(370, '0370', 'AGYEMANG YAA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(371, '0371', 'AMANING TWUMASI AMA AGYEIWAA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(372, '0372', 'AMANKONA NANCY KYERAA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(373, '0373', 'AMOH ANGEL JOY DEDE', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(374, '0374', 'ASIAMAH  NYARKOAH LOIS', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(375, '0375', 'ASIEDU MARGDAN AWURABENA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(376, '0376', 'ATIBILA JUDITH', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(377, '0377', 'BOADU ACHIAA PRAISES', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(378, '0378', 'BOADUWAA EMMANUELLA  NANA ADWOA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(379, '0379', 'BOAMPONG LUCRECIA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(380, '0380', 'BONSU HACKMAN BRIDGET', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(381, '0381', 'BONSU MICHELLE AKOSUA  ASUAMAA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(382, '0382', 'DUKU  AMUZUAH ELIZABETH', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(383, '0383', 'FOFIE  AKOMAA NANA AKUA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(384, '0384', 'HASSAN MAJORY JOSEPH JAQUELYN', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(385, '0385', 'IBRAHIM FAIZA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(386, '0386', 'KUSI GYEABOAH STEPHANIE', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(387, '0387', 'LAMPTEY BENEDICTA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(388, '0388', 'MANU-ABABIO CHRISTABEL', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(389, '0389', 'MENSAH-KANNIN AMA  PARACH', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(390, '0390', 'OSEI EMMANUELLA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(391, '0391', 'OSEI GYAMFUAA AKOSUA ISSABELLA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(392, '0392', 'OSEI TUTU SARPOMAA BLESSING', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(393, '0393', 'PARTEY VALENTINA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(394, '0394', 'QUANSAH  ELIZABETH', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(395, '0395', 'WIREDU FRIMPOMAA PORTIA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(396, '0396', 'WIREKO BOATEMAA BLESSING', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(397, '0397', 'YEBOAH EDNA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(398, '0398', 'YEBOAH THEODORA', NULL, NULL, 'Female', NULL, 13, 1, NULL, 'active', '2026-03-22 19:12:12'),
(399, '0399', 'AGBO KOFI PAUL', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(400, '0400', 'AGYEMANG EMMANUEL', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(401, '0401', 'AHENKRAH JUSTIN', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(402, '0402', 'AKYEN KWAW JOY', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(403, '0403', 'ANDERSON AWOTWE TONY CLIVE', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(404, '0404', 'ANTWI JOHN', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(405, '0405', 'ASABRE ADDAI PRINCE', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(406, '0406', 'ASARE BEDIAKO YAW', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(407, '0407', 'ASARE JOSEPH JUNIOR', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(408, '0408', 'ASIEDU EMMANUEL', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(409, '0409', 'BAFFOE DANQUAH BENEDICT', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(410, '0410', 'BOAKYE DERRICK', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(411, '0411', 'DUAH OPOKU CHRISTIAN', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(412, '0412', 'KYEI BAFFOUR  ENCHILL EMMANUEL', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(413, '0413', 'MAHAMA YUSSIF', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(414, '0414', 'MAMUDA SULEMANA', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(415, '0415', 'MINTAH NYAMEKYE MCKEON', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(416, '0416', 'MOHAMMED KAMIL', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(417, '0417', 'OBENG MENSAH ISSAC', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(418, '0418', 'OPOKU AGYEMANG YAW', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(419, '0419', 'OPOKU OBENG CHRISTIAN', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(420, '0420', 'OSEI OPOKU  CALEB', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(421, '0421', 'SAGOE SAMUEL', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(422, '0422', 'SAMBA MOHAMMED GODWIN  ABOTISOM', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(423, '0423', 'SARFO BOATENG KENNEDY', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(424, '0424', 'SARFO EMMANUEL KWABENA', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(425, '0425', 'SARFO SAMUEL', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(426, '0426', 'TANG PUAL MWININGKAARA', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(427, '0427', 'TWENEBOAH KODUAH ALEXIS', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(428, '0428', 'YAKUBU ZAKARI', NULL, NULL, 'Male', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(429, '0429', 'ADOMAKO CRYWINDA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(430, '0430', 'AKANJO CHARITY', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(431, '0431', 'AMANKWAH AGYEI BEATRICE', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(432, '0432', 'ANSAH KORANTEMAA NKUNIMDINI ABENA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(433, '0433', 'AVOKA STEPHANIE', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(434, '0434', 'BASHIRU RAHAMAH', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(435, '0435', 'BOAKYE AMPOMAAH EMILY', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(436, '0436', 'BOATENG POKUAA STEPHANIE', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(437, '0437', 'BONSU KUSIWAA MISHAELLA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(438, '0438', 'DARKO BENEDICTA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(439, '0439', 'DUAH AMANKWAA JENNIFER', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(440, '0440', 'GERSHON KWESIWAA IVY', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(441, '0441', 'KOKAR ABIGAIL', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(442, '0442', 'MANU OWUSU JOSEPHINE', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(443, '0443', 'NSIAH ACHIAA NHYIRA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(444, '0444', 'NUAMAH MENSAH THECLA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(445, '0445', 'NYARKO KESSE MENSAH NHYIRA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(446, '0446', 'ODURO OWUSUWAA MIKAELA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(447, '0447', 'OKYERE BOWAA EMMANUELLA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(448, '0448', 'OPOKU BLESSING NYAMEKYE', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(449, '0449', 'OPOKU NYAMEKYE KEREN', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(450, '0450', 'OSEI EVELYN CHARITY', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(451, '0451', 'OSEI YEBOAH ANGEL', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(452, '0452', 'OWUSU DAVIDA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(453, '0453', 'OWUSU VERONICA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(454, '0454', 'TWUMASI BOATEMAA AKUA', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:12:19'),
(455, '0455', 'ADDO OPARE GOODLUCK', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30');
INSERT INTO `students` (`id`, `student_id_number`, `full_name`, `surname`, `date_of_birth`, `gender`, `photo_path`, `current_class_id`, `academic_year_id`, `pin_hash`, `status`, `created_at`) VALUES
(456, '0456', 'ADDO SARKODEE CHRISTOPHER', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(457, '0457', 'AGYEMANG KORANKYE AMEYAW PRINCE', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(458, '0458', 'ALAYERI JOSEPH', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(459, '0459', 'AMISSAH JEPHTA', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(460, '0460', 'APACHEGAWO WEDAM', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(461, '0461', 'ASILIKA EMMANUEL', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(462, '0462', 'ASUBONTENG RICHARD', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(463, '0463', 'BAFFOUR AWUAH JOSHUA', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(464, '0464', 'BOAKYE ANTHONY YIADOM', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(465, '0465', 'BOAKYE DANIEL', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(466, '0466', 'BROBBEY PAPA KWABENA MENSAH', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(467, '0467', 'DJAN HANSON KIKI GLENN', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(468, '0468', 'GYASI AUGUSTINE', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(469, '0469', 'GYIMAH KWADWO FRANCIS', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(470, '0470', 'MENSAH TONY FERDNARD', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(471, '0471', 'NSIAH ACHEAMPONG AUGUSTINE', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(472, '0472', 'OSEI ASAMOAH CHRISTIAN', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(473, '0473', 'OWUSU LORD', NULL, NULL, 'Male', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(474, '0474', 'OFORI BLESSING', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(475, '0475', 'SARPONG DESMOND', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(476, '0476', 'TAKYI KWADWO DUAH PAUL', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(477, '0477', 'TETTEH NARH OSCAR', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(478, '0478', 'DERY JULIAN BOB -N- YAN', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(479, '0479', 'AGBEKO WALTER YAYRA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(480, '0480', 'AGBENATOR GIFTY', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(481, '0481', 'AGYEI BENEDICTA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(482, '0482', 'AKANDOLE LOVIA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(483, '0483', 'ASAMOAH OWUSU AUDREY', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(484, '0484', 'BAYONG HILLARY', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(485, '0485', 'BIYAA AMA FAUSTINA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(486, '0486', 'BOATENG ANNOR VIDA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(487, '0487', 'BOATENG SERWAA MARY', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(488, '0488', 'BONSU ADJEI MARGARET', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(489, '0489', 'DONKOR PRISCILLA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(490, '0490', 'DUGAN PRECIOUS', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(491, '0491', 'DWOMOH TAKYIWAA ADWOA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(492, '0492', 'GYAMENA NTIRIWAA HELENA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(493, '0493', 'KODUA ASANTEWAA PHLIPINA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(494, '0494', 'KRAMPAH AMPONSAH VICENTIA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(495, '0495', 'KWAKYE KYEREWAA PRECIOUS', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(496, '0496', 'MENSAH SERWAA PRINCESS', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(497, '0497', 'NYANTAKYIWAA ADJEI AKOSUA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(498, '0498', 'NYARKO ABRONOMAH JELTA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(499, '0499', 'ODURO DELORIS', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(500, '0500', 'OFORI NANDY', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(501, '0501', 'OKAI NYAMEKYE PRINCESS', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(502, '0502', 'OWUSU BANAHENE BENEDICTA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(503, '0503', 'OWUSU PEGGITA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(504, '0504', 'SARFO ELISA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(505, '0505', 'YEBOAH ABENA OHEMAA', NULL, NULL, 'Female', NULL, 15, 1, NULL, 'active', '2026-03-22 19:12:30'),
(506, '0506', 'ADU GYAMFI FRANK', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(507, '0507', 'ADU OTENG LORD KOFI', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(508, '0508', 'AFRIYIE OPOKU KWAKU', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(509, '0509', 'AGBO NELSON ATTA BRIGHT', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(510, '0510', 'AGYEMANG OWUSU ADUBOFFOUR', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(511, '0511', 'AGYENIM AKWASI BOATENG', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(512, '0512', 'AKANDOLE LOVE', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(513, '0513', 'ASARE LUGARD LORDFRED', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(514, '0514', 'AVOKA CHRISTOPHER', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(515, '0515', 'BAFFOUR AWUAH NYARKO KINGSLEY', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(516, '0516', 'BOAHEN KODUA KIOPAS', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(517, '0517', 'BOATENG KWAME CALEB', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(518, '0518', 'MOHAMMED SHABAN', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(519, '0519', 'ODURO SOLOMON BROBBEY', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(520, '0520', 'OPPONG JOSHUA', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(521, '0521', 'OPPONG SYLVESTER', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(522, '0522', 'OSEI DESHELLE FAITH', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(523, '0523', 'YEBOAH NANA OWUSU BANAHENE', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(524, '0524', 'SARFO KANTANKA ADOMAKO PAPA YAW', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(525, '0525', 'TETTEH NARTEY GODFRED', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(526, '0526', 'TURKSON DESMOND', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(527, '0527', 'AYAREGA ASHFAQ ALI', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(528, '0528', 'NTI CLEMENT', NULL, NULL, 'Male', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(529, '0529', 'AGBO NELSON ATAA BRIDGET', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(530, '0530', 'APPAU MYRA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(531, '0531', 'APPIAH JOKEBED', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(532, '0532', 'ARTHUR DWOBENG JEDIDAH YAA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(533, '0533', 'ASANTE AMA ALLIYIAH ACHIAA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(534, '0534', 'BAALAH LOIS YATIME', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(535, '0535', 'BAFFOUR AWUAH MORONINA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(536, '0536', 'ACHEAMPONG STEPHANIE', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(537, '0537', 'BOAFO VANESSA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(538, '0538', 'BOAKYE ABENA KUSIWAA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(539, '0539', 'BONSU AKOSUA PEARL', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(540, '0540', 'BONSU SERWAA JOSEPHINE', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(541, '0541', 'DONKOR OWUSU LILLIAN', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(542, '0542', 'FORDJOUR OWUSU MIRA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(543, '0543', 'MENSAH ASANTEWAA VICENTIA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(544, '0544', 'MOHAMMED FATIMATU', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(545, '0545', 'NKRUMAH FELICIA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(546, '0546', 'OPPONG NAASAA ELIZABETH', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(547, '0547', 'OSEI ASIBEY BONSU SHABBY', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(548, '0548', 'OSEI POKU CHRISTABEL', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(549, '0549', 'PII BETTY KASANNE', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(550, '0550', 'PREMPEH MARY', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(551, '0551', 'YIDAN MARGRET GUMSENA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(552, '0552', 'ADJEI ADOBEA YVONNE', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:12:35'),
(553, '0553', 'ABONI SHADRACK', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(554, '0554', 'ABORAMPANG M. ALVIN', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(555, '0555', 'ACKOM A. ASEMPA', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(556, '0556', 'ADJEI A. ROONEY', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(557, '0557', 'ADUTWUM A. IMAD', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(558, '0558', 'AGYEMANG BOAFO RICHMOND', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(559, '0559', 'AGYENIM BOATENG BENEDICT', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(560, '0560', 'ALESO K. JOEL', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(561, '0561', 'AMANKWAH ADUSEI JOSHUA', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(562, '0562', 'AMPONG N. ELLIOT', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(563, '0563', 'AMPONSAH LESLIE', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(564, '0564', 'ANSAH O. K. PERCY', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(565, '0565', 'ANTWI AMPIAH ADDISON', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(566, '0566', 'APPIAH BLESSING', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(567, '0567', 'ARHIN O. MIGUEL', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(568, '0568', 'ASANTE EUGENE', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(569, '0569', 'ASUBONTENE EMMANUEL', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(570, '0570', 'AZIAH ADEYENE MANAASSEH', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(571, '0571', 'BALDRICK A. EMMANUEL', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(572, '0572', 'BOATENG ANANE JUNIOR', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(573, '0573', 'BOATENG FRANCIS', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(574, '0574', 'BORDEN AGYEI N. LOUIS', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(575, '0575', 'DAMPRANE YAW AFRIYIE', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(576, '0576', 'DANSO ABU ERNEST', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(577, '0577', 'ESHUN BENEDICT Y.', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:56'),
(578, '0578', 'KOTOKA NICHOLAS SELASIE KOFI', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(579, '0579', 'MENSAH ESHUN RICHARD', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(580, '0580', 'NYAMEKYE DAVID', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(581, '0581', 'OHENE TAKYI LEONARD', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(582, '0582', 'OTENG O. A. CALEB', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(583, '0583', 'SARFO K. EMMANUEL', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(584, '0584', 'SASAH K. EMMANUEL', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(585, '0585', 'TRUMAN JASKING', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(586, '0586', 'ACHEAMPONG TIWAA EMMANUELLA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(587, '0587', 'AFAFA Z. QUEENSTAR', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(588, '0588', 'AKAFO REJOICE', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(589, '0589', 'AMOFA AKUA STELLA BOAKYEWAA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(590, '0590', 'ASARE O. GOLDEN', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(591, '0591', 'ASORE A. WENDY', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(592, '0592', 'BAFFOE ESTHER', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(593, '0593', 'FORDJOUR P. VIVICA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(594, '0594', 'KOLUBILE VICTORIA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(595, '0595', 'KWAKYE CHRISTABEL', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(596, '0596', 'NKUM EMMANUELLA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(597, '0597', 'ODURO B. FRANKLINA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(598, '0598', 'ODURO SHEILA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(599, '0599', 'OHENE SENKYI IDA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(600, '0600', 'OPOKU AFUA KEYRIA .', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(601, '0601', 'OWUSU ACHEAMPOMAA. CLAUDIA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(602, '0602', 'OWUSU AKUA GLORIA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(603, '0603', 'OWUSU GWENDOLYN', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(604, '0604', 'PEPRAH KAREEN', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(605, '0605', 'PINAMANG JACQUELINE', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(606, '0606', 'POKU BREFA TYRA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(607, '0607', 'SARFO EMMANUELLA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(608, '0608', 'SARKODIE AMANDA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(609, '0609', 'YEBOAH FELICIA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(610, '0610', 'FRIMPONG OBENG MICHAEL', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(611, '0611', 'AGYEI AMANKWAA CALEB', NULL, NULL, 'Male', NULL, 5, 1, NULL, 'active', '2026-03-22 19:12:57'),
(612, '0612', 'ACHEAMPONG  ANIMOYAM', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(613, '0613', 'AMOAKO CRAIG', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(614, '0614', 'APPIAH MBIR JAPHTER', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(615, '0615', 'ATOYIRE WILSONWISE  WEDAM', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(616, '0616', 'BOATEN JOHN', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(617, '0617', 'BREMPONG NANA KWAKU OSEI  A.', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(618, '0618', 'DUAH GYEBI MALVIN', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(619, '0619', 'DUAH SAMUEL', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(620, '0620', 'FURLKAN FUSENI', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(621, '0621', 'GYAMFI ADU CHRISTIAN', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(622, '0622', 'KUSI JUDE', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(623, '0623', 'OKYERE  HARRISON', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(624, '0624', 'OTENG ADJEI NANA VILLAREAL', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(625, '0625', 'OWUSU AFRIYIE AWOROH FELIX', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(626, '0626', 'QUANSAH NATHANIEL', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(627, '0627', 'SARFO BOAFO GODWIN', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(628, '0628', 'YEBOAH JALON NANA KWAME', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(629, '0629', 'YEBOAH RENARD PROPHET JUNIOR', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(630, '0630', 'OPOKU ARHIN EUGENE', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(631, '0631', 'AYINE NATHANIEL', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(632, '0632', 'ASEMSURO MAXWELL', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(633, '0633', 'UMAR HONAIS', NULL, NULL, 'Male', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(634, '0634', 'ADDAI  ALBERTA ANIMA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(635, '0635', 'AFRIYIE YAA DELPHINA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(636, '0636', 'AGYEMANG  AKUA NYAMEKYE', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(637, '0637', 'AGYEMANG ROSIDO A', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(638, '0638', 'AMANKWAH  BENEDICTA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(639, '0639', 'BASHIRU RALIYA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(640, '0640', 'BRANTUO NISSI ABUAA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(641, '0641', 'DARKO AKUA  KWARTENG', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(642, '0642', 'DONKOR PRINCESS ACHIAA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(643, '0643', 'DUAH DUFIE ADOM', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(644, '0644', 'FOSUAA AMANKWAA NANA AKOSUA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(645, '0645', 'GYAMFI TAKYIWAA RHODA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(646, '0646', 'MARFO NANA YAA KWAKYEWAA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(647, '0647', 'MENSAH AGYEI WINNIFRED', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(648, '0648', 'OFFIN NANA AMA NHYIRA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(649, '0649', 'OWUSU BREMPOMAA NANA HEMAA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(650, '0650', 'OWUSU CLARA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(651, '0651', 'OWUSU MICHELLE AMA  ARKOA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(652, '0652', 'PANFORD VALENTINA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(653, '0653', 'TAWIAH ABA ANGEL', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:13:17'),
(654, '0654', 'YAKUBU MARDIYA', NULL, NULL, 'Female', NULL, 5, 1, NULL, 'active', '2026-03-22 19:13:17'),
(655, '0655', 'ASOLO GIFTY', NULL, NULL, 'Female', NULL, 14, 1, NULL, 'active', '2026-03-22 19:13:17'),
(656, '0656', 'ANAAFI DANIELLA', NULL, NULL, 'Female', NULL, 16, 1, NULL, 'active', '2026-03-22 19:13:17'),
(657, '0657', 'YAKUBU MARDIYA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:15:14'),
(658, '0658', 'ASOLO GIFTY', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:15:14'),
(659, '0659', 'ANAAFI DANIELLA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:15:14'),
(660, '0660', 'YIADOM BOAKYEWAA Y. DIANA', NULL, NULL, 'Female', NULL, 12, 1, NULL, 'active', '2026-03-22 19:15:14'),
(661, 'TRANS-001', 'Transition Student', '', NULL, '', NULL, 20, 1, NULL, 'inactive', '2026-03-23 10:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `student_aggregates`
--

CREATE TABLE `student_aggregates` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `aggregate_score` decimal(8,2) DEFAULT NULL,
  `class_position` int(10) UNSIGNED DEFAULT NULL,
  `number_of_subjects` tinyint(3) UNSIGNED DEFAULT NULL,
  `computed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `aggregate_grade` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'WAEC aggregate grade (sum of best 6)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_aggregates`
--

INSERT INTO `student_aggregates` (`id`, `student_id`, `class_id`, `term_id`, `aggregate_score`, `class_position`, `number_of_subjects`, `computed_at`, `aggregate_grade`) VALUES
(1, 1, 2, 1, 90.00, 1, 1, '2026-03-21 01:07:47', NULL),
(25, 3, 8, 1, 0.00, 50, 1, '2026-03-22 09:44:46', NULL),
(26, 112, 8, 1, 90.00, 9, 1, '2026-03-31 12:22:17', NULL),
(27, 113, 8, 1, 53.00, 43, 2, '2026-03-31 12:22:17', NULL),
(28, 114, 8, 1, 95.00, 5, 1, '2026-03-31 12:22:17', NULL),
(29, 115, 8, 1, 96.00, 3, 1, '2026-03-31 12:22:17', NULL),
(30, 116, 8, 1, 58.00, 38, 1, '2026-03-31 12:22:17', NULL),
(31, 117, 8, 1, 76.00, 18, 1, '2026-03-31 12:22:17', NULL),
(32, 118, 8, 1, 63.00, 35, 1, '2026-03-31 12:22:17', NULL),
(33, 119, 8, 1, 64.00, 33, 1, '2026-03-31 12:22:17', NULL),
(34, 120, 8, 1, 90.00, 9, 1, '2026-03-31 12:22:17', NULL),
(35, 121, 8, 1, 53.00, 43, 1, '2026-03-31 12:22:17', NULL),
(36, 122, 8, 1, 67.00, 28, 1, '2026-03-31 12:22:17', NULL),
(37, 123, 8, 1, 54.00, 40, 1, '2026-03-31 12:22:17', NULL),
(38, 124, 8, 1, 67.00, 28, 1, '2026-03-31 12:22:17', NULL),
(39, 125, 8, 1, 45.00, 46, 1, '2026-03-31 12:22:17', NULL),
(40, 126, 8, 1, 64.00, 33, 1, '2026-03-31 12:22:17', NULL),
(41, 127, 8, 1, 90.00, 9, 1, '2026-03-31 12:22:17', NULL),
(42, 128, 8, 1, 53.00, 43, 1, '2026-03-31 12:22:17', NULL),
(43, 129, 8, 1, 54.00, 40, 1, '2026-03-31 12:22:17', NULL),
(44, 130, 8, 1, 275.00, 1, 3, '2026-03-31 12:22:17', NULL),
(45, 131, 8, 1, 122.00, 2, 3, '2026-03-31 12:22:17', NULL),
(46, 132, 8, 1, 73.00, 25, 1, '2026-03-31 12:22:17', NULL),
(47, 133, 8, 1, 60.00, 37, 1, '2026-03-31 12:22:17', NULL),
(48, 134, 8, 1, 54.00, 40, 1, '2026-03-31 12:22:17', NULL),
(49, 135, 8, 1, 76.00, 18, 1, '2026-03-31 12:22:17', NULL),
(50, 136, 8, 1, 67.00, 28, 1, '2026-03-31 12:22:17', NULL),
(51, 137, 8, 1, 45.00, 46, 1, '2026-03-31 12:22:17', NULL),
(52, 138, 8, 1, 91.00, 7, 1, '2026-03-31 12:22:17', NULL),
(53, 139, 8, 1, 71.00, 26, 1, '2026-03-31 12:22:17', NULL),
(54, 140, 8, 1, 76.00, 18, 1, '2026-03-31 12:22:17', NULL),
(55, 141, 8, 1, 43.00, 48, 1, '2026-03-31 12:22:17', NULL),
(56, 142, 8, 1, 83.00, 12, 1, '2026-03-31 12:22:17', NULL),
(57, 143, 8, 1, 78.00, 16, 1, '2026-03-31 12:22:17', NULL),
(58, 144, 8, 1, 95.00, 5, 1, '2026-03-31 12:22:17', NULL),
(59, 145, 8, 1, 76.00, 18, 1, '2026-03-31 12:22:17', NULL),
(60, 146, 8, 1, 91.00, 7, 1, '2026-03-31 12:22:17', NULL),
(61, 147, 8, 1, 71.00, 26, 1, '2026-03-31 12:22:17', NULL),
(62, 148, 8, 1, 43.00, 48, 1, '2026-03-31 12:22:17', NULL),
(63, 149, 8, 1, 83.00, 12, 1, '2026-03-31 12:22:17', NULL),
(64, 150, 8, 1, 96.00, 3, 1, '2026-03-31 12:22:17', NULL),
(65, 151, 8, 1, 58.00, 38, 1, '2026-03-31 12:22:17', NULL),
(66, 152, 8, 1, 76.00, 18, 1, '2026-03-31 12:22:17', NULL),
(67, 153, 8, 1, 63.00, 35, 1, '2026-03-31 12:22:17', NULL),
(68, 154, 8, 1, 78.00, 16, 1, '2026-03-31 12:22:17', NULL),
(69, 155, 8, 1, 81.00, 14, 1, '2026-03-31 12:22:17', NULL),
(70, 156, 8, 1, 67.00, 28, 1, '2026-03-31 12:22:17', NULL),
(71, 157, 8, 1, 67.00, 28, 1, '2026-03-31 12:22:17', NULL),
(72, 158, 8, 1, 76.00, 18, 1, '2026-03-31 12:22:17', NULL),
(73, 159, 8, 1, 76.00, 18, 1, '2026-03-31 12:22:17', NULL),
(74, 160, 8, 1, 81.00, 14, 1, '2026-03-31 12:22:17', NULL),
(712, 2, 1, 1, 63.33, 2, 1, '2026-03-22 10:18:46', NULL),
(713, 4, 1, 1, 70.00, 1, 1, '2026-03-22 10:18:46', NULL),
(714, 5, 1, 1, 12.50, 4, 1, '2026-03-22 10:18:46', NULL),
(715, 18, 1, 1, 59.17, 3, 1, '2026-03-22 10:18:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_parents`
--

CREATE TABLE `student_parents` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `parent_user_id` int(10) UNSIGNED NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_parents`
--

INSERT INTO `student_parents` (`id`, `student_id`, `parent_user_id`, `relationship`, `is_primary`) VALUES
(1, 1, 4, 'Parent/Guardian', 1),
(3, 112, 4, 'Parent', 1),
(5, 3, 4, 'Parent', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_promotions`
--

CREATE TABLE `student_promotions` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `auto_promoted` tinyint(1) DEFAULT NULL,
  `manual_override` tinyint(1) DEFAULT 0,
  `promotion_status` enum('pending','promoted','repeated') DEFAULT 'pending',
  `next_class_name` varchar(20) DEFAULT NULL,
  `set_by` int(10) UNSIGNED DEFAULT NULL,
  `set_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_promotions`
--

INSERT INTO `student_promotions` (`id`, `student_id`, `academic_year_id`, `term_id`, `auto_promoted`, `manual_override`, `promotion_status`, `next_class_name`, `set_by`, `set_at`) VALUES
(56, 661, 5, 3, NULL, 0, 'promoted', 'B-Test', NULL, '2026-03-23 10:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `student_remarks`
--

CREATE TABLE `student_remarks` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `conduct_character` tinyint(3) UNSIGNED DEFAULT NULL,
  `attitude` tinyint(3) UNSIGNED DEFAULT NULL,
  `teacher_remark` text DEFAULT NULL,
  `headmaster_remark` text DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_remarks`
--

INSERT INTO `student_remarks` (`id`, `student_id`, `term_id`, `conduct_character`, `attitude`, `teacher_remark`, `headmaster_remark`, `updated_by`, `updated_at`) VALUES
(1, 130, 1, NULL, 1, 'Shows good understanding of classwork. Keep it up.', 'Keep up the good work and remain focused.', 7, '2026-03-25 10:04:17'),
(2, 131, 1, NULL, 3, 'Should pay more maximum attention during lessons.', NULL, 7, '2026-03-23 05:22:15');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `level_id` int(10) UNSIGNED NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `level_id`, `subject_name`, `subject_code`, `sort_order`, `is_active`) VALUES
(6, 1, 'Ghanaian Language', 'GL', 6, 1),
(8, 2, 'English Language', 'ENG', 1, 1),
(10, 2, 'Natural Science', 'SCI', 3, 1),
(11, 2, 'Religious & Moral Edu.', 'RME', 4, 1),
(13, 2, 'Ghanaian Language', 'GL', 6, 1),
(14, 2, 'History', 'HIST', 7, 1),
(15, 2, 'French', 'FR', 8, 1),
(16, 2, 'Computing', 'COMP.', 9, 1),
(17, 2, 'Social Studies', 'SS', 10, 1),
(19, 3, 'Mathematics', 'MATH', 2, 1),
(20, 3, 'Integrated Science', 'ISCI', 3, 1),
(21, 3, 'Social Studies', 'SS', 4, 1),
(22, 3, 'Religious & Moral Edu.', 'RME', 5, 1),
(23, 3, 'French', 'FR', 6, 1),
(24, 3, 'ICT', 'ICT', 7, 1),
(25, 3, 'Ghanaian Language', 'GL', 8, 1),
(26, 3, 'Creative Arts', 'CA', 9, 1),
(28, 1, 'English Language', 'ENG', 1, 1),
(29, 1, 'Mathematics', 'MATH', 2, 1),
(31, 1, 'Religious & Moral Edu.', 'RME', 4, 1),
(32, 1, 'Creative Arts', 'CA', 5, 1),
(34, 1, 'History', 'HIST', 7, 1),
(36, 2, 'Mathematics', 'MATH', 2, 1),
(39, 2, 'Creative Arts', 'CA', 5, 1),
(45, 3, 'English Language', 'ENG', 1, 1),
(46, 3, 'Mathematics', 'MATH', 2, 1),
(47, 3, 'Integrated Science', 'ISCI', 3, 1),
(48, 3, 'Social Studies', 'SS', 4, 1),
(51, 3, 'Computing', 'COMP.', 7, 1),
(54, 3, 'Career Technology', 'CT', 10, 1),
(56, 1, 'French', 'FR', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `term_number` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_school_days` smallint(5) UNSIGNED DEFAULT 60,
  `next_term_begins` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms`
--

INSERT INTO `terms` (`id`, `academic_year_id`, `term_number`, `name`, `start_date`, `end_date`, `total_school_days`, `next_term_begins`, `is_active`, `created_at`) VALUES
(1, 1, 2, 'Term 2', '2026-01-08', '2026-03-31', 60, '2026-04-20', 1, '2026-03-20 18:05:39'),
(3, 5, 1, 'Term 1 Test', NULL, NULL, 60, NULL, 0, '2026-03-23 10:45:24'),
(8, 1, 1, 'Term 1', NULL, NULL, 60, NULL, 0, '2026-03-23 11:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('admin','teacher','parent','student') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `is_active`, `last_login_at`, `created_by`, `created_at`) VALUES
(1, 'Martin Kwame Agbenyenunse', 'agbenyenusemartin@gmail.com', '0557869989', '$2y$12$F4NS64aGrgwfuvFkuZKgv.n.0hgUid/pZC1kKyrRZ6Qkv5N89RGrK', 'teacher', 1, '2026-03-22 10:12:04', NULL, '2026-03-20 15:57:20'),
(2, 'System Administrator', 'admin@uaddara.edu.gh', '0557869989', '$2y$12$F4NS64aGrgwfuvFkuZKgv.n.0hgUid/pZC1kKyrRZ6Qkv5N89RGrK', 'admin', 1, '2026-04-04 22:40:54', NULL, '2026-03-20 16:10:00'),
(3, 'Samuel Obeng', 'samuel@gmail.com', '0557869989', '$2y$12$F4NS64aGrgwfuvFkuZKgv.n.0hgUid/pZC1kKyrRZ6Qkv5N89RGrK', 'teacher', 1, '2026-03-23 16:06:40', NULL, '2026-03-20 22:30:15'),
(4, 'agbenyenuse samuel', NULL, '0557869989', NULL, 'parent', 1, NULL, NULL, '2026-03-21 00:15:18'),
(5, 'Sarkodie ADDO', 'sarkodie@gmail.com', '0502536351', '$2y$10$C06og2XbTLbDyTt.10Hl9.rvA5q4kYlgHGqQazPUxo4iWfxve2NMq', 'teacher', 1, '2026-03-31 12:20:26', NULL, '2026-03-21 08:23:41'),
(6, 'Samuel APPIAH', 'sam@gmail.com', '0240967072', '$2y$10$syA9HLxcKL6mXhv0eXLwnuSZRpcdyNridLzb3mxDgGrrNVjOJr43O', 'teacher', 1, '2026-04-04 20:03:04', NULL, '2026-03-21 09:03:11'),
(7, 'Diana ASIAMAH', 'diana@gmail.com', '0557869989', '$2y$10$7l0h9vpULSaJVR/iwXWfmenb69XTzHP/icbmGro6nJIamGYBOQx7u', 'teacher', 1, '2026-04-04 19:00:46', NULL, '2026-03-21 09:25:21'),
(8, 'Samuel Obeng', NULL, '0501345770', NULL, 'parent', 1, NULL, NULL, '2026-03-22 20:28:37'),
(9, 'Marrion PHILIPS', 'marrion@gmail.com', '0557869989', '$2y$10$IcNiFq9ekcvRq4n44eFk4.81xYEgRjOHtWMQ2vZbcYGIyquHjlLgy', 'teacher', 1, NULL, NULL, '2026-03-23 04:53:49'),
(10, 'Dorcas AFUA OSEI', 'dorcas@gmail.com', '0247568034', '$2y$10$gCLK.Tqf4.yaOzFNAhZ4KOHVplcZb/0cr5u5p/dIotwJIVJDF/Lgm', 'teacher', 1, NULL, NULL, '2026-03-25 14:38:08'),
(11, 'Kennedy SARFO KANKAM', 'ken@gmail.com', '0244455226', '$2y$10$SnuMKnzEYfNPGnwnEofWJekah1wL.t/odwaVhzlL2hGcCydClJ5ey', 'teacher', 1, NULL, NULL, '2026-03-25 14:45:45'),
(12, 'Christiana OBIRI', 'christianaobiri333@gmail.com', '0240071532', '$2y$10$KFkYxM9fOq/vWraPEPXebuk/.ImIuirMSkpZDUUA3laEvbsIxn4wa', 'teacher', 1, '2026-04-04 21:15:48', NULL, '2026-04-04 21:00:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_year_name` (`year_name`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_att` (`student_id`,`term_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_table` (`table_name`),
  ADD KEY `idx_record` (`table_name`,`record_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_class_year` (`class_name`,`section`,`academic_year_id`),
  ADD KEY `level_id` (`level_id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_class_subj_term` (`class_id`,`subject_id`,`term_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `fk_class_subj_term` (`term_id`);

--
-- Indexes for table `class_teachers`
--
ALTER TABLE `class_teachers`
  ADD PRIMARY KEY (`class_id`,`teacher_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `computed_scores`
--
ALTER TABLE `computed_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_comp` (`student_id`,`class_subject_id`,`term_id`),
  ADD KEY `class_subject_id` (`class_subject_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `exam_scores`
--
ALTER TABLE `exam_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_exam` (`student_id`,`class_subject_id`,`term_id`),
  ADD KEY `class_subject_id` (`class_subject_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier` (`identifier`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`);

--
-- Indexes for table `predefined_remarks`
--
ALTER TABLE `predefined_remarks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report_card_locks`
--
ALTER TABLE `report_card_locks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lock` (`class_id`,`term_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `sba_component_scores`
--
ALTER TABLE `sba_component_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_comp` (`student_id`,`class_subject_id`,`term_id`),
  ADD KEY `class_subject_id` (`class_subject_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `school_levels`
--
ALTER TABLE `school_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_code` (`code`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`recipient_phone`),
  ADD KEY `idx_type` (`sms_type`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_id_number` (`student_id_number`),
  ADD KEY `current_class_id` (`current_class_id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Indexes for table `student_aggregates`
--
ALTER TABLE `student_aggregates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_agg` (`student_id`,`class_id`,`term_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_parent` (`student_id`,`parent_user_id`),
  ADD KEY `parent_user_id` (`parent_user_id`);

--
-- Indexes for table `student_promotions`
--
ALTER TABLE `student_promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_promo` (`student_id`,`academic_year_id`,`term_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `student_remarks`
--
ALTER TABLE `student_remarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_remark` (`student_id`,`term_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_year_term` (`academic_year_id`,`term_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `computed_scores`
--
ALTER TABLE `computed_scores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5307;

--
-- AUTO_INCREMENT for table `exam_scores`
--
ALTER TABLE `exam_scores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `predefined_remarks`
--
ALTER TABLE `predefined_remarks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `report_card_locks`
--
ALTER TABLE `report_card_locks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sba_component_scores`
--
ALTER TABLE `sba_component_scores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `school_levels`
--
ALTER TABLE `school_levels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=663;

--
-- AUTO_INCREMENT for table `student_aggregates`
--
ALTER TABLE `student_aggregates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5271;

--
-- AUTO_INCREMENT for table `student_parents`
--
ALTER TABLE `student_parents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_promotions`
--
ALTER TABLE `student_promotions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `student_remarks`
--
ALTER TABLE `student_remarks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`level_id`) REFERENCES `school_levels` (`id`),
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_class_subj_term` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `class_teachers`
--
ALTER TABLE `class_teachers`
  ADD CONSTRAINT `class_teachers_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_teachers_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `computed_scores`
--
ALTER TABLE `computed_scores`
  ADD CONSTRAINT `computed_scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `computed_scores_ibfk_2` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`),
  ADD CONSTRAINT `computed_scores_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `exam_scores`
--
ALTER TABLE `exam_scores`
  ADD CONSTRAINT `exam_scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `exam_scores_ibfk_2` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`),
  ADD CONSTRAINT `exam_scores_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_card_locks`
--
ALTER TABLE `report_card_locks`
  ADD CONSTRAINT `report_card_locks_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `report_card_locks_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `sba_component_scores`
--
ALTER TABLE `sba_component_scores`
  ADD CONSTRAINT `sba_component_scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `sba_component_scores_ibfk_2` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`),
  ADD CONSTRAINT `sba_component_scores_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`current_class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `student_aggregates`
--
ALTER TABLE `student_aggregates`
  ADD CONSTRAINT `student_aggregates_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_aggregates_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `student_aggregates_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD CONSTRAINT `student_parents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_parents_ibfk_2` FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `student_promotions`
--
ALTER TABLE `student_promotions`
  ADD CONSTRAINT `student_promotions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_promotions_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  ADD CONSTRAINT `student_promotions_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `student_remarks`
--
ALTER TABLE `student_remarks`
  ADD CONSTRAINT `student_remarks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`level_id`) REFERENCES `school_levels` (`id`);

--
-- Constraints for table `terms`
--
ALTER TABLE `terms`
  ADD CONSTRAINT `terms_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
