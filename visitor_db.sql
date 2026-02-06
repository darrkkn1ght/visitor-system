-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2026 at 12:13 PM
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
-- Database: `visitor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `user_role` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `change_summary` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `status` enum('SUCCESS','FAILURE') NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `username`, `user_role`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `change_summary`, `ip_address`, `user_agent`, `session_id`, `status`, `error_message`, `created_at`) VALUES
(1, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Account locked due to multiple failed attempts', '2026-01-15 13:32:32'),
(2, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Invalid password', '2026-01-15 13:33:37'),
(3, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Invalid password', '2026-01-15 13:33:49'),
(4, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"nelfund\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Invalid password', '2026-01-15 13:35:00'),
(5, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Account locked due to multiple failed attempts', '2026-01-15 13:35:41'),
(6, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Invalid password', '2026-01-15 13:38:45'),
(7, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Invalid password', '2026-01-15 13:39:45'),
(8, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Invalid password', '2026-01-15 13:40:53'),
(9, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Account locked due to multiple failed attempts', '2026-01-15 13:41:37'),
(10, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Account locked due to multiple failed attempts', '2026-01-15 13:45:21'),
(11, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Account locked due to multiple failed attempts', '2026-01-15 13:45:29'),
(12, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'v99jt8hukaptff1j1tovv25njn', 'FAILURE', 'Invalid password', '2026-01-15 13:52:46'),
(13, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '3elr7pohe22k5pb4te998ucv5a', 'SUCCESS', NULL, '2026-01-15 13:53:33'),
(14, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 't4md0vu3q9el8gude489juu5ga', 'SUCCESS', NULL, '2026-01-15 14:01:55'),
(15, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '53uvgn99t0bjk8qvvaogfrccva', 'SUCCESS', NULL, '2026-01-15 14:03:33'),
(16, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'cmse1mcumvi6oi5cge9u95cnc4', 'SUCCESS', NULL, '2026-01-15 14:13:59'),
(17, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'v4vfe5ubtn089h86gfv7pr9i2d', 'SUCCESS', NULL, '2026-01-15 14:16:37'),
(18, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'i4deafbhamtutsqiu8jbs9026h', 'SUCCESS', NULL, '2026-01-15 14:18:53'),
(19, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'kk16p1sab7kijepad51dft29fn', 'SUCCESS', NULL, '2026-01-15 14:23:56'),
(20, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'sf5jrdnjpb4an3f9i9kvfq2fbn', 'SUCCESS', NULL, '2026-01-15 14:25:37'),
(21, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'uvc35qc6eecn1ccvau7obia2c4', 'SUCCESS', NULL, '2026-01-15 14:41:10'),
(22, 7, 'reception', 'receptionist', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'sf5jrdnjpb4an3f9i9kvfq2fbn', 'FAILURE', 'Invalid password', '2026-01-15 15:31:21'),
(23, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'o2g9na3vcc2653jfuktfp6fhje', 'SUCCESS', NULL, '2026-01-15 15:32:01'),
(24, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'jefuf2chftmg2qp3fudubp0enc', 'SUCCESS', NULL, '2026-01-15 15:32:02'),
(25, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '90nktc6r566ksau0spb6csl18f', 'SUCCESS', NULL, '2026-01-15 15:48:44'),
(26, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '6pc7aabnj91q9k9gjuqq6r1lmb', 'SUCCESS', NULL, '2026-01-15 15:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `range_start` int(11) NOT NULL,
  `range_end` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `name`, `range_start`, `range_end`) VALUES
(1, 'Director\'s Office', 1000, 1020),
(2, 'ITeMS Board Secretariat', 2000, 2020),
(3, 'Gaming Hub', 3000, 3020),
(4, 'NelFund Support', 4000, 4020),
(5, 'Directorate\'s', 5000, 5020),
(19, 'Directorate', 6000, 6020);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `organizer_name` varchar(255) NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `event_date` date NOT NULL,
  `time_slot` enum('morning','afternoon','fullday') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keycards`
--

CREATE TABLE `keycards` (
  `id` int(11) NOT NULL,
  `card_number` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `is_assigned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keycards`
--

INSERT INTO `keycards` (`id`, `card_number`, `destination_id`, `is_assigned`) VALUES
(178, 400, 4, 0),
(179, 401, 4, 0),
(180, 402, 4, 0),
(181, 403, 4, 0),
(182, 404, 4, 0),
(183, 405, 4, 0),
(184, 406, 4, 0),
(185, 407, 4, 0),
(186, 408, 4, 0),
(187, 409, 4, 0),
(188, 410, 4, 0),
(189, 411, 4, 0),
(190, 412, 4, 0),
(191, 413, 4, 0),
(192, 414, 4, 0),
(193, 415, 4, 0),
(194, 416, 4, 0),
(195, 417, 4, 0),
(196, 418, 4, 0),
(197, 419, 4, 0),
(198, 200, 2, 0),
(199, 201, 2, 0),
(200, 202, 2, 0),
(201, 203, 2, 0),
(202, 204, 2, 0),
(203, 205, 2, 0),
(204, 206, 2, 0),
(205, 207, 2, 0),
(206, 208, 2, 0),
(207, 209, 2, 0),
(208, 210, 2, 0),
(209, 211, 2, 0),
(210, 212, 2, 0),
(211, 213, 2, 0),
(212, 214, 2, 0),
(213, 215, 2, 0),
(214, 216, 2, 0),
(215, 217, 2, 0),
(216, 218, 2, 0),
(217, 219, 2, 0),
(218, 100, 1, 1),
(219, 101, 1, 1),
(220, 102, 1, 1),
(221, 103, 1, 0),
(222, 104, 1, 0),
(223, 105, 1, 0),
(224, 106, 1, 0),
(225, 107, 1, 0),
(226, 108, 1, 0),
(227, 109, 1, 0),
(228, 110, 1, 0),
(229, 111, 1, 0),
(230, 112, 1, 0),
(231, 113, 1, 0),
(232, 114, 1, 0),
(233, 115, 1, 0),
(234, 116, 1, 0),
(235, 117, 1, 0),
(236, 118, 1, 0),
(237, 119, 1, 0),
(238, 500, 5, 0),
(239, 501, 5, 0),
(240, 502, 5, 0),
(241, 503, 5, 0),
(242, 504, 5, 0),
(243, 505, 5, 0),
(244, 506, 5, 0),
(245, 507, 5, 0),
(246, 508, 5, 0),
(247, 509, 5, 0),
(248, 510, 5, 0),
(249, 511, 5, 0),
(250, 512, 5, 0),
(251, 513, 5, 0),
(252, 514, 5, 0),
(253, 515, 5, 0),
(254, 516, 5, 0),
(255, 517, 5, 0),
(256, 518, 5, 0),
(257, 519, 5, 0),
(258, 300, 3, 1),
(259, 301, 3, 1),
(260, 302, 3, 0),
(261, 303, 3, 0),
(262, 304, 3, 0),
(263, 305, 3, 0),
(264, 306, 3, 0),
(265, 307, 3, 0),
(266, 308, 3, 0),
(267, 309, 3, 0),
(268, 310, 3, 0),
(269, 311, 3, 0),
(270, 312, 3, 0),
(271, 313, 3, 0),
(272, 314, 3, 0),
(273, 315, 3, 0),
(274, 316, 3, 0),
(275, 317, 3, 0),
(276, 318, 3, 0),
(277, 319, 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `attempt_time`, `success`) VALUES
(1, 'superadmin', '::1', '2026-01-15 14:21:46', 0),
(16, 'superadmin', '::1', '2026-01-15 14:38:45', 0),
(17, 'superadmin', '::1', '2026-01-15 14:38:45', 0),
(18, 'superadmin', '::1', '2026-01-15 14:39:45', 0),
(19, 'superadmin', '::1', '2026-01-15 14:39:45', 0),
(20, 'superadmin', '::1', '2026-01-15 14:40:53', 0),
(21, 'superadmin', '::1', '2026-01-15 14:40:53', 0),
(22, 'superadmin', '::1', '2026-01-15 14:41:37', 0),
(23, 'superadmin', '::1', '2026-01-15 14:45:21', 0),
(24, 'superadmin', '::1', '2026-01-15 14:45:29', 0),
(41, 'reception', '::1', '2026-01-15 16:56:34', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'New visitor: Taiwo heading to your destination.', 0, '2026-01-05 10:56:05'),
(2, 9, 'New visitor: Taiwo heading to your destination.', 0, '2026-01-05 10:56:05'),
(3, 1, 'New visitor: Taiwo heading to your destination.', 0, '2026-01-05 12:15:08'),
(4, 21, 'New visitor: Taiwo heading to your destination.', 0, '2026-01-05 12:15:08'),
(5, 1, 'New visitor: Taiwo heading to your destination.', 0, '2026-01-05 12:16:30'),
(6, 21, 'New visitor: Taiwo heading to your destination.', 0, '2026-01-05 12:16:30'),
(7, 1, 'New visitor: Test User heading to your destination.', 0, '2026-01-05 12:28:50'),
(8, 1, 'New visitor: Taiwo heading to your destination.', 0, '2026-01-08 10:56:33'),
(9, 21, 'New visitor: Taiwo heading to your destination.', 0, '2026-01-08 10:56:33'),
(10, 1, 'New visitor: Deputy Director\'s Test heading to your destination.', 0, '2026-01-15 13:51:18'),
(11, 1, 'New visitor: Peter Adewale Tomiwa heading to your destination.', 0, '2026-01-15 15:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','director','receptionist','destination_admin') NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `must_change_password` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `destination_id`, `must_change_password`) VALUES
(1, 'director', '$2y$10$xJvvXrzAFyviXDzWwsglFuZJ5cgOXsADqI57zEZW/CFIfcuizHGHS', 'director', NULL, 1),
(5, 'superadmin', '$2y$10$oDL4B..8fQ/STSKIFDpwrO/q5v7pZdEpY/W1mbcdADsXw/Ii9IxoS', 'super_admin', NULL, 1),
(7, 'reception', '$2y$10$.NuJqHFbLl4eXBJHmUXLuedcUjt59fRxKMaCZbY3qU8fAvrmRbivK', 'receptionist', NULL, 0),
(9, 'gaminghub', '$2y$10$EuGlMc90zj2H5ZrH34HARuxQK2zW9jbt1aHAVO/KDnx1KjkhhX/Ia', 'destination_admin', 3, 1),
(10, 'nelfund', '$2y$10$MsFFqFCcwVHxXgr9hqg2LeMDpLwxehSbIBwvtPHaZ.AYiKQWYr/tC', 'destination_admin', 4, 1),
(11, 'directorates', '$2y$10$Z52oJ6nzJu7nv/Evp/NUCuR3nHyIv3SoCfSlGbzlIcYRUmK.hmGpW', 'destination_admin', 5, 1),
(17, 'itemsboardsecretariat', '$2y$10$nBlefCaqt29xiV1LTay9nOjpBxWWbC61b3sG0qLYGWD9Gnf5qtJaG', 'destination_admin', 2, 1),
(21, 'directorate_admin', '$2y$10$hd63uO0EcNHje4LL5lD1AOXABXa90tQ7ujUMsE6tWoxEBPbs9LVCK', 'destination_admin', 19, 1);

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `fullname` varchar(100) NOT NULL,
  `visitor_type` varchar(50) NOT NULL,
  `keycard` varchar(50) DEFAULT NULL,
  `faculty_organization` varchar(150) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `time_in` timestamp NOT NULL DEFAULT current_timestamp(),
  `time_out` datetime DEFAULT NULL,
  `date` date DEFAULT NULL,
  `is_alternate` tinyint(1) DEFAULT 0,
  `keycard_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `token`, `expires_at`, `fullname`, `visitor_type`, `keycard`, `faculty_organization`, `phone_number`, `purpose`, `destination`, `time_in`, `time_out`, `date`, `is_alternate`, `keycard_id`, `status`) VALUES
(1, '65a60055bb704993b0c69bffbb6d61b571770d506d8009d06c6f9a4ca5adeb6c', '2026-01-16 14:51:18', 'Deputy Director\'s Test', 'staff', NULL, 'Technology', '081287564646', 'To collect money', '1', '2026-01-15 13:51:18', '2026-01-15 16:00:53', '2026-01-15', 0, 220, 'active'),
(2, '94f9476a2d2cb309b77906334d15c15f3aa3618817b4fe6fd6e4f183f6981c05', '2026-01-16 16:38:21', 'Peter Adewale Tomiwa', 'student', NULL, 'Computing', '08129744447', 'To make money', '1', '2026-01-15 15:38:21', '0000-00-00 00:00:00', '2026-01-15', 0, 220, 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `keycards`
--
ALTER TABLE `keycards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `card_number` (`card_number`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_admin_destination` (`destination_id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `keycard_id` (`keycard_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keycards`
--
ALTER TABLE `keycards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `keycards`
--
ALTER TABLE `keycards`
  ADD CONSTRAINT `keycards_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_admin_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `visitors`
--
ALTER TABLE `visitors`
  ADD CONSTRAINT `visitors_ibfk_1` FOREIGN KEY (`keycard_id`) REFERENCES `keycards` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
