-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 29, 2026 at 05:01 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lab_claim_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_id` int NOT NULL,
  `forenoon_count` int NOT NULL,
  `afternoon_count` int NOT NULL,
  `question_setting` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `claim_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_exam_attendance` (`exam_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `exam_id`, `forenoon_count`, `afternoon_count`, `question_setting`, `created_at`, `claim_status`, `status`) VALUES
(2, 2, 27, 0, 2, '2026-02-17 04:36:52', 'pending', 'pending'),
(4, 9, 23, 0, 2, '2026-02-24 04:25:33', 'approved', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `college_distance`
--

DROP TABLE IF EXISTS `college_distance`;
CREATE TABLE IF NOT EXISTS `college_distance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `college_name` varchar(150) NOT NULL,
  `distance_km` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `college_name` (`college_name`)
) ;

--
-- Dumping data for table `college_distance`
--

INSERT INTO `college_distance` (`id`, `college_name`, `distance_km`, `created_at`) VALUES
(1, 'CMS', 40, '2026-02-05 16:27:39'),
(2, 'KCT', 20, '2026-02-05 16:27:54'),
(3, 'PSG', 24, '2026-02-05 16:28:09'),
(4, 'Rathinam', 26, '2026-02-05 16:28:19'),
(5, 'SNR', 10, '2026-02-05 16:28:32'),
(6, 'SNS', 17, '2026-02-05 16:28:46');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
CREATE TABLE IF NOT EXISTS `exams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stream` varchar(50) NOT NULL,
  `department` int DEFAULT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `subject_name` varchar(150) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `lab_no` varchar(50) NOT NULL,
  `internal_staff_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `external_staff_id` int DEFAULT NULL,
  `session` enum('forenoon','afternoon','forenoon_afternoon') DEFAULT NULL,
  `exam_time` varchar(100) DEFAULT NULL,
  `external_claim_status` varchar(20) DEFAULT NULL,
  `internal_claim_status` varchar(20) DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lab_date` (`lab_no`,`exam_date`),
  UNIQUE KEY `uq_subject_code` (`subject_code`),
  UNIQUE KEY `uq_date_lab` (`exam_date`,`lab_no`),
  UNIQUE KEY `uq_department_date` (`department`,`exam_date`),
  KEY `fk_internal_staff` (`internal_staff_id`),
  KEY `fk_external_staff` (`external_staff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `stream`, `department`, `subject_code`, `subject_name`, `semester`, `exam_date`, `lab_no`, `internal_staff_id`, `created_by`, `created_at`, `external_staff_id`, `session`, `exam_time`, `external_claim_status`, `internal_claim_status`, `reminder_sent`) VALUES
(2, 'Computer', 4, '23UCT1CL', 'C PROGRAMMING-LAB', '1', '2026-02-17', 'Lab 1', 33, 1, '2026-02-16 14:16:50', 37, 'forenoon', NULL, 'pending', 'pending', 1),
(3, 'Science', 7, '23UBT1AA', 'PLANT BIOLOGY LAB', '1', '2026-02-17', 'Botany Lab', 39, 1, '2026-02-17 07:28:43', 37, 'afternoon', NULL, 'pending', 'pending', 1),
(7, 'Computer', 4, '23UCT2CM', 'OBJECT ORIENTED PROGRAMMING WITH C++', '2', '2026-02-19', 'Lab 3', 33, 1, '2026-02-19 02:54:28', 37, 'forenoon', NULL, 'pending', 'pending', 0),
(6, 'Computer', 4, '23UCT4SL', 'DATABASE MANAGEMENT-LAB', '4', '2026-02-18', 'Lab 5', 33, 1, '2026-02-18 07:30:20', 37, 'afternoon', NULL, 'pending', 'pending', 0),
(8, 'Science', 7, '23UBT2BB', 'CELL BIOLOGY', '2', '2026-02-24', 'Physics Lab', 39, NULL, '2026-02-23 17:23:57', 37, 'forenoon', NULL, 'pending', 'pending', 1),
(9, 'Computer', 4, '23UCT5CP', 'PHP PROGRAMMING-LAB', '5', '2026-02-24', 'Lab 3', 33, 1, '2026-02-24 16:39:31', 37, 'forenoon', NULL, 'approved', 'approved', 1),
(10, 'Computer', 4, '23UCT3CN', 'JAVA PROGRAMMING -LAB', '3', '2026-02-25', 'Lab 3', 33, 1, '2026-02-25 06:12:27', 37, 'afternoon', NULL, NULL, NULL, 0),
(11, 'Science', 7, '23UBT4DD', 'PLANT PHYSIOLOGY', '4', '2026-02-26', 'Chemistry Lab', 38, 1, '2026-02-25 12:41:59', 36, 'forenoon', NULL, NULL, NULL, 0),
(12, 'Computer', 4, '23UCT6SM', 'HARDWARE INSTALLATION AND NETWORKING LAB', '6', '2026-03-09', 'Lab 8', 34, 1, '2026-03-09 02:39:52', 26, 'forenoon', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `external_staff`
--

DROP TABLE IF EXISTS `external_staff`;
CREATE TABLE IF NOT EXISTS `external_staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `college_name` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bank_name` varchar(100) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `external_staff`
--

INSERT INTO `external_staff` (`id`, `name`, `designation`, `college_name`, `email`, `phone`, `address`, `bank_account`, `ifsc_code`, `created_at`, `bank_name`, `branch_name`, `status`) VALUES
(35, 'Balaji S', 'Assistant Professor', 'SNS', 'balajisns@gmail.com', '9235678945', 'Coimbatore', '334455667788', 'UTIB0003345', '2026-01-28 11:02:46', 'Axis', 'Singanallur', 'active'),
(36, 'Nandhini P', 'Associate Professor', 'PSG', 'nandhini.psg@gmail.com', '9346789456', 'Coimbatore', '445566778899', 'HDFC0004455', '2026-01-28 11:02:46', 'HDFC', 'Kalapatti', 'active'),
(34, 'Anitha R', 'Associate Professor', 'SNR', 'anitha.snr@gmail.com', '9124567894', 'Coimbatore', '223344556677', 'SBIN0002233', '2026-01-28 11:02:46', 'SBI', 'Vadavalli', 'disabled'),
(33, 'Senthil Kumar', 'Assistant Professor', 'Rathinam', 'senthil.r@gmail.com', '9013456783', 'Coimbatore', '112233445566', 'CNRB0001122', '2026-01-28 11:02:46', 'Canara', 'Kuniyamuthur', 'disabled'),
(31, 'Ramesh Babu', 'Assistant Professor', 'CMS', 'ramesh.cms@gmail.com', '9781234561', 'Coimbatore', '890123456789', 'SBIN0008901', '2026-01-28 11:02:46', 'SBI', 'Saibaba Colony', 'active'),
(32, 'Kavitha M', 'Associate Professor', 'KCT', 'kavittha@gmail.com', '9892345672', 'Coimbatore', '901234567890', 'ICIC0009012', '2026-01-28 11:02:46', 'ICICI', 'Thudiyalur', 'active'),
(30, 'Divya Shree', 'Associate Professor', 'PSG', 'divya.psg@gmail.com', '9678123450', 'Coimbatore', '789012345678', 'HDFC0007890', '2026-01-28 11:02:46', 'HDFC', 'Ukkadam', 'active'),
(14, 'JAYA p', 'Assistant Professor', 'sns', 'abi@gmail.com', '9362908119', '106a2\r\nganapathy', '', '', '2026-01-28 05:19:09', '', '', 'active'),
(37, 'Vignesh K', 'Assistant Professor', 'CMS', 'vignesh@gmail.com', '9457894567', 'Coimbatore', '556677889900', 'ICIC0005566', '2026-01-28 11:02:46', 'ICICI', 'Gandhipuram', 'active'),
(29, 'Karthik Raj', 'Assistant Professor', 'SNS', 'karthik.sns@gmail.com', '9567812340', 'Coimbatore', '678901234567', 'UTIB0006789', '2026-01-28 11:02:46', 'Axis', 'Ganapathy', 'active'),
(28, 'Meena Lakshmi', 'Associate Professor', 'SNR', 'meena.snr@gmail.com', '9456781230', 'Coimbatore', '567890123456', 'SBIN0005678', '2026-01-28 11:02:46', 'SBI', 'Town Hall', 'disabled'),
(27, 'Arun Prasad', 'Assistant Professor', 'Rathinam', 'arun.rathinam@gmail.com', '9345678120', 'Coimbatore', '456789012345', 'ICIC0004567', '2026-01-28 11:02:46', 'ICICI', 'Saravanampatti', 'disabled'),
(25, 'Suresh Kumar', 'Associate Professor', 'CMS', 'suresh.cms@gmail.com', '9123456780', 'Coimbatore', '234567890123', 'SBIN0002345', '2026-01-28 11:02:22', 'SBI', 'RS Puram', 'active'),
(26, 'Priya Devi', 'Associate Professor', 'KCT', 'devi@gmail.com', '9234567810', 'Coimbatore', '345678901234', 'HDFC0003456', '2026-01-28 11:02:46', 'HDFC', 'Peelamedu', 'active'),
(38, 'Revathi S', 'Associate Professor', 'KCT', 'revathi.kct@gmail.com', '9568945678', 'Coimbatore', '667788990011', 'SBIN0006677', '2026-01-28 11:02:46', 'SBI', 'Hope College', 'active'),
(39, 'Manoj Kumar', 'Assistant Professor', 'Rathinam', 'manoj.r@gmail.com', '9679456789', 'Coimbatore', '778899001122', 'UTIB0007788', '2026-01-28 11:02:46', 'Axis', 'Sulur', 'disabled'),
(40, 'Deepa R', 'Associate Professor', 'SNR', 'deepa.snr@gmail.com', '9780567890', 'Coimbatore', '889900112233', 'HDFC0008899', '2026-01-28 11:02:46', 'HDFC', 'Podanur', 'disabled'),
(41, 'Prakash T', 'Assistant Professor', 'SNS', 'prakash.sns@gmail.com', '9891678901', 'Coimbatore', '990011223344', 'SBIN0009900', '2026-01-28 11:02:46', 'SBI', 'Kovaipudur', 'active'),
(42, 'Swathi M', 'Associate Professor', 'PSG', 'swathi.psg@gmail.com', '9002789012', 'Coimbatore', '101112131415', 'ICIC0001011', '2026-01-28 11:02:46', 'ICICI', 'Avinashi Road', 'active'),
(43, 'Gopinath V', 'Assistant Professor', 'CMS', 'gopinath.cms@gmail.com', '9113890123', 'Coimbatore', '121314151617', 'CNRB0001213', '2026-01-28 11:02:46', 'Canara', 'Perur', 'active'),
(44, 'Lakshmi N', 'Associate Professor', 'KCT', 'lakshmi.kct@gmail.com', '9224901234', 'Coimbatore', '141516171819', 'SBIN0001415', '2026-01-28 11:02:46', 'SBI', 'Mettupalayam', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `internal_staff`
--

DROP TABLE IF EXISTS `internal_staff`;
CREATE TABLE IF NOT EXISTS `internal_staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` varchar(50) NOT NULL,
  `department_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `designation` varchar(100) NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_id` (`staff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `internal_staff`
--

INSERT INTO `internal_staff` (`id`, `staff_id`, `department_id`, `name`, `email`, `phone`, `address`, `bank_name`, `account_number`, `branch_name`, `ifsc_code`, `designation`, `created_by`, `created_at`) VALUES
(35, 'UTS101', 4, 'Kannan', 'kanan22@gmail.com', '9876543215', 'Coimbatore', 'ICICI Bank', '678901234567', 'Singanallur', 'ICIC0005566', 'Associate Professor', 0, '2026-02-16 09:55:56'),
(34, 'UTS128', 4, 'Meera', 'meera677@gmail.com', '9876543214', 'Coimbatore', 'Axis Bank', '567890123456', 'Peelamedu', 'UTIB0003344', 'Assistant Professor', 0, '2026-02-16 09:55:56'),
(33, 'UTS126', 4, 'Aathiya', 'aathiya46@gmail.com', '9876543213', 'Coimbatore', 'HDFC Bank', '456789012345', 'Saibaba Colony', 'HDFC0001122', 'Assistant Professor', 0, '2026-02-16 09:55:56'),
(30, 'UTS120', 4, 'Gayathiri', 'gayathiri558@gmail.com', '9876543210', 'Coimbatore', 'SBI', '123456789012', 'Town Hall', 'SBIN0001234', 'Assistant Professor', 0, '2026-02-16 09:55:56'),
(32, 'UTS123', 4, 'Abi', 'abiabi98@gmail.com', '9876543212', 'Coimbatore', 'Canara Bank', '345678901234', 'Gandhipuram', 'CNRB0000789', 'Associate Professor', 0, '2026-02-16 09:55:56'),
(31, 'UTS124', 4, 'Geetha', 'geetha34@gmail.com', '9876543211', 'Coimbatore', 'Indian Bank', '234567890123', 'RS Puram', 'IDIB0000456', 'Assistant Professor', 0, '2026-02-16 09:55:56'),
(23, 'UTS111', 4, 'ragavi', 'ragavi86@gmail.com', '9362901122', 'g.n mills', 'SBI', '456789012388', 'RS Puram', 'ICIC0009013', 'Assistant Professor', 4, '2026-02-16 09:45:28'),
(36, 'BTS101', 7, 'Lavanya', 'bot101@college.ac.in', '9123456780', 'Coimbatore', 'SBI', '789012345678', 'Gandhipuram', 'SBIN0009876', 'Assistant Professor', 0, '2026-02-16 10:56:09'),
(37, 'BTS102', 7, 'Ramesh', 'bot102@college.ac.in', '9123456781', 'Coimbatore', 'Indian Bank', '890123456789', 'Town Hall', 'IDIB0006543', 'Associate Professor', 0, '2026-02-16 10:56:09'),
(38, 'BTS103', 7, 'Divya', 'bot103@college.ac.in', '9123456782', 'Coimbatore', 'Canara Bank', '901234567890', 'RS Puram', 'CNRB0004321', 'Assistant Professor', 0, '2026-02-16 10:56:09'),
(39, 'BTS104', 7, 'Arun', 'arunaruna@gmail.com', '9123456783', 'Coimbatore', 'HDFC Bank', '112233445566', 'Peelamedu', 'HDFC0007788', 'Assistant Professor', 0, '2026-02-16 10:56:09');

-- --------------------------------------------------------

--
-- Table structure for table `rate_settings`
--

DROP TABLE IF EXISTS `rate_settings`;
CREATE TABLE IF NOT EXISTS `rate_settings` (
  `id` int NOT NULL DEFAULT '1',
  `da_per_day` decimal(10,2) NOT NULL,
  `ta_per_km` decimal(10,2) NOT NULL,
  `rate_per_paper` decimal(10,2) NOT NULL,
  `paper_setting_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rate_settings`
--

INSERT INTO `rate_settings` (`id`, `da_per_day`, `ta_per_km`, `rate_per_paper`, `paper_setting_amount`, `updated_at`) VALUES
(1, 250.00, 7.00, 18.00, 70.00, '2026-02-07 10:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(150) NOT NULL,
  `year` int NOT NULL,
  `semester` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `department_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_code_year_sem` (`subject_code`,`year`,`semester`),
  UNIQUE KEY `uq_name_year_sem` (`subject_name`,`year`,`semester`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `year`, `semester`, `created_by`, `created_at`, `department_id`) VALUES
(16, '23UCT3CN', 'JAVA PROGRAMMING -LAB', 2, 3, 0, '2026-02-08 07:08:47', 4),
(15, '23UCT2CM', 'OBJECT ORIENTED PROGRAMMING WITH C++', 1, 2, 0, '2026-02-08 07:07:49', 4),
(14, '23UCT1CL', 'C PROGRAMMING-LAB', 1, 1, 0, '2026-02-08 07:06:31', 4),
(17, '23UCT4CO', '.NET FRAMEWORK', 2, 4, 0, '2026-02-08 07:10:22', 4),
(18, '23UCT4SL', 'DATABASE MANAGEMENT-LAB', 2, 4, 0, '2026-02-08 07:11:00', 4),
(19, '23UCT5CP', 'PHP PROGRAMMING-LAB', 3, 5, 0, '2026-02-08 07:12:33', 4),
(20, '23UCT6CQ', 'PYTHON PROGRAMMING-LAB', 3, 6, 0, '2026-02-08 07:14:09', 4),
(21, '23UCT6SM', 'HARDWARE INSTALLATION AND NETWORKING LAB', 3, 6, 0, '2026-02-08 07:15:14', 4),
(22, '23UBT1AA', 'PLANT BIOLOGY LAB', 1, 1, 0, '2026-02-16 11:04:17', 7),
(23, '23UBT2BB', 'CELL BIOLOGY', 1, 2, 0, '2026-02-16 11:04:49', 7),
(24, '23UBT3CC', 'GENETICS LAB', 2, 3, 0, '2026-02-16 11:05:34', 7),
(25, '23UBT4DD', 'PLANT PHYSIOLOGY', 2, 4, 0, '2026-02-16 11:06:30', 7),
(26, '23UBT5EE', 'ECOLOGY LAB', 3, 5, 0, '2026-02-16 11:07:14', 7);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('department','finance','admin') NOT NULL,
  `stream` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `must_change_password` tinyint(1) DEFAULT '1',
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_2` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `stream`, `created_at`, `must_change_password`, `reset_token`, `reset_expires`, `reset_otp`, `otp_expires`) VALUES
(1, 'Super Admin', 'admin@college.com', '$2y$10$YlpChnEDByou0timNxudyObunqLYFbe6vOtaVkRCyY.V9JqjjE3U.', 'admin', NULL, '2025-12-07 00:47:10', 1, NULL, NULL, NULL, NULL),
(4, 'Computer Technology', 'ctdept4680@gmail.com', '$2y$10$ECJMYcUbtY67aEyXy9nbzOtLzuO9AnVhlC8h0Y8LfKV2J6Nvx/Ci6', 'department', 'Computer', '2026-01-28 11:34:09', 1, '3d9bd633228c8e9f08b167e574e7f4895eaaace65ea42c4fb4886ba85fa85037', '2026-02-08 16:28:14', NULL, NULL),
(7, 'Botany', 'BT2026@gmail.com', '$2y$10$OWWk4u1.u9PBqdr9qT4pNOCTI8aSDziDDzsnFWNBvThSG25zB6ysa', 'department', 'Science', '2026-02-15 08:37:35', 1, NULL, NULL, NULL, NULL),
(8, 'Information Technology', 'IT2026@gmail.com', '$2y$10$iGRi/lzz/qm9jaZn5ddPrO1rKNc/2hsipxvOosLYs8hbrCH9MI5n.', 'department', 'Computer', '2026-02-17 03:42:52', 1, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
