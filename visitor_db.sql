-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2026 at 06:36 PM
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
-- Table structure for table `admin_availability_log`
--

CREATE TABLE `admin_availability_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_status` enum('available','busy','unavailable','away') DEFAULT NULL,
  `new_status` enum('available','busy','unavailable','away') NOT NULL,
  `status_message` varchar(255) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_availability_log`
--

INSERT INTO `admin_availability_log` (`id`, `user_id`, `old_status`, `new_status`, `status_message`, `changed_at`, `ip_address`, `user_agent`) VALUES
(1, 5, 'available', 'unavailable', '', '2026-01-29 09:13:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(2, 5, 'unavailable', 'unavailable', '', '2026-01-29 09:13:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(3, 5, 'unavailable', 'busy', '', '2026-01-29 09:15:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(4, 5, 'busy', 'away', '', '2026-01-29 09:15:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(5, 5, 'away', 'busy', '', '2026-01-29 09:15:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(6, 5, 'busy', 'away', '', '2026-01-29 09:15:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(7, 5, 'away', 'available', '', '2026-01-29 09:15:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(8, 5, 'available', 'available', '', '2026-01-29 09:15:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(9, 5, 'available', 'busy', '', '2026-01-29 09:15:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(10, 5, 'busy', 'away', '', '2026-01-29 09:15:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(11, 1, 'available', 'away', '', '2026-01-29 09:18:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(12, 1, 'away', 'unavailable', '', '2026-01-29 09:18:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(13, 1, 'unavailable', 'available', '', '2026-01-29 09:34:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(14, 7, 'available', 'busy', '', '2026-01-29 10:00:26', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(15, 1, 'available', 'busy', '', '2026-01-29 10:06:17', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(16, 1, 'busy', 'busy', '', '2026-01-29 10:11:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(17, 1, 'busy', 'unavailable', '', '2026-02-06 09:27:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(18, 1, 'unavailable', 'unavailable', 'in a meeting', '2026-02-06 09:27:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(19, 1, 'unavailable', 'available', 'in a meeting', '2026-02-06 09:31:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(20, 1, 'available', 'away', 'in a meeting', '2026-02-06 11:07:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(21, 1, 'away', 'away', 'in a meetback by 2pming', '2026-02-06 11:07:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(22, 1, 'away', 'away', 'in a meetback by 2pm000ing', '2026-02-06 11:07:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(23, 1, 'away', 'away', 'back by 2pm', '2026-02-06 11:08:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(24, 7, 'busy', 'available', '', '2026-02-06 12:16:44', '192.168.3.135', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(25, 7, 'available', 'busy', '', '2026-02-06 12:17:12', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(26, 7, 'busy', 'available', '', '2026-02-06 12:17:16', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(27, 7, 'available', 'busy', '', '2026-02-06 12:17:35', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(28, 7, 'busy', 'available', '', '2026-02-06 12:17:36', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(29, 7, 'available', 'unavailable', '', '2026-02-06 12:18:36', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(30, 7, 'unavailable', 'available', '', '2026-02-06 12:18:38', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(31, 7, 'available', 'available', 'hhh', '2026-02-06 12:56:10', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(32, 7, 'available', 'busy', 'hhh', '2026-02-06 12:56:15', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(33, 7, 'busy', 'busy', 'hhhj', '2026-02-06 12:56:20', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(34, 1, 'away', 'unavailable', 'back by 2pm', '2026-02-06 12:59:21', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(35, 7, 'busy', 'available', 'hhhj', '2026-03-18 09:33:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
(36, 1, 'unavailable', 'available', 'back by 2pm', '2026-03-18 09:34:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
(37, 1, 'available', 'busy', 'back by 2pm', '2026-03-18 13:19:39', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(38, 1, 'busy', 'busy', 'back by', '2026-03-18 13:19:41', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(39, 1, 'busy', 'busy', '', '2026-03-18 13:19:43', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(40, 1, 'busy', 'busy', 'i am going to be busy for a while', '2026-03-18 13:19:59', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(41, 1, 'busy', 'available', 'i am going to be busy for a while', '2026-03-18 13:23:44', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(42, 1, 'available', 'available', 'i am going to be busy for a whileavailab', '2026-03-18 13:23:53', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(43, 1, 'available', 'available', 'available', '2026-03-18 13:23:56', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(44, 1, 'available', 'available', 'available now', '2026-03-18 13:24:03', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(45, 1, 'available', 'busy', 'available now', '2026-03-18 13:27:34', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(46, 1, 'busy', 'busy', '', '2026-03-18 13:27:41', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'),
(47, 7, 'available', 'busy', '', '2026-04-17 14:47:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0'),
(48, 7, 'busy', 'available', '', '2026-04-17 14:47:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0');

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
(26, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '6pc7aabnj91q9k9gjuqq6r1lmb', 'SUCCESS', NULL, '2026-01-15 15:56:34'),
(27, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'ep36bnasdmgrtlmlag84dk8qpi', 'FAILURE', 'Invalid password', '2026-01-19 11:19:35'),
(28, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'ep36bnasdmgrtlmlag84dk8qpi', 'FAILURE', 'Invalid password', '2026-01-19 11:19:44'),
(29, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'ep36bnasdmgrtlmlag84dk8qpi', 'FAILURE', 'Invalid password', '2026-01-19 11:20:23'),
(30, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'ep36bnasdmgrtlmlag84dk8qpi', 'FAILURE', 'Account locked due to multiple failed attempts', '2026-01-19 11:20:35'),
(31, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'cp8but9iqqvh90e4m84iba4p7t', 'SUCCESS', NULL, '2026-01-19 11:22:21'),
(32, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '192.168.3.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '87spcr9ehpah1cumlkvvetf8a6', 'SUCCESS', NULL, '2026-01-19 12:43:46'),
(33, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'i9rgo8vmt1f7ldbibjrctdt1j8', 'SUCCESS', NULL, '2026-01-19 12:50:05'),
(34, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'nrlvt289lirf80u965o751313v', 'SUCCESS', NULL, '2026-01-19 13:53:52'),
(35, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '192.168.3.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', 'diqqr73n4q76fnpvkd6e52aa60', 'SUCCESS', NULL, '2026-01-19 13:57:21'),
(36, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '4nkt9dl23h35l2flests034rih', 'SUCCESS', NULL, '2026-01-19 13:58:45'),
(37, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'gc8l820l78lnh9n7193amncp5q', 'SUCCESS', NULL, '2026-01-19 14:03:57'),
(38, 7, 'reception', 'receptionist', 'SECURITY_UNAUTHORIZED_EXPORT', NULL, NULL, NULL, '{\"user\":\"reception\",\"severity\":\"MEDIUM\"}', 'Security_unauthorized_export', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'gc8l820l78lnh9n7193amncp5q', 'SUCCESS', NULL, '2026-01-19 14:04:39'),
(39, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '8rr4cbobg75csas57bbfma5c27', 'SUCCESS', NULL, '2026-01-20 09:20:06'),
(40, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '0cutmv8366ps5nsho29ngh0shf', 'SUCCESS', NULL, '2026-01-20 10:31:37'),
(41, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'luo94uhjhieo3l72tbk6g2jgh0', 'SUCCESS', NULL, '2026-01-20 10:31:50'),
(42, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'cpn8irshclpjuo4cqk3hcr2p4n', 'SUCCESS', NULL, '2026-01-20 13:21:05'),
(43, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'lvhijl7k3v23bl5v1d06b4ajvg', 'SUCCESS', NULL, '2026-01-20 14:54:37'),
(44, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'uv7go0t7fss6sl8jgc3dq6gq57', 'SUCCESS', NULL, '2026-01-20 16:37:45'),
(45, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '3j1m2jflq9cilsl8s7ao1vhcp1', 'SUCCESS', NULL, '2026-01-23 07:28:49'),
(46, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'v2vi8vbpqdgj38i2f380c0582o', 'SUCCESS', NULL, '2026-01-23 08:24:30'),
(47, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 't899a7i0k8ik9108uerv0j8qep', 'SUCCESS', NULL, '2026-01-23 08:37:35'),
(48, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'kqsb4a670oq3g1jkkh11462p0q', 'SUCCESS', NULL, '2026-01-23 08:52:00'),
(49, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'kg4qna9e3vtv2hn2mho1thuco2', 'FAILURE', 'Invalid password', '2026-01-23 08:55:12'),
(50, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '0tmt8m8651r3polkkspd24uiok', 'SUCCESS', NULL, '2026-01-23 08:55:27'),
(51, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'fs0ed56jk1ioqqrgcuemk4gdl1', 'SUCCESS', NULL, '2026-01-23 08:58:47'),
(52, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'urh9uubo83bnqb1eq9mk8etj6h', 'SUCCESS', NULL, '2026-01-23 09:05:25'),
(53, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '1mo13vs1dhfs0t5ii2nr5bjuss', 'SUCCESS', NULL, '2026-01-23 10:46:27'),
(54, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"director\"}', 'Login attempt failed', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '6u1hb5brp15gfoa243ibik078b', 'FAILURE', 'Invalid password', '2026-01-23 11:16:44'),
(55, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'ue59c08uqr85435r5vludgonfi', 'SUCCESS', NULL, '2026-01-23 11:16:54'),
(56, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'lmkn91v05sh72cilkf3mvj9lv6', 'SUCCESS', NULL, '2026-01-23 11:23:10'),
(57, 5, 'superadmin', 'super_admin', 'EXPORT', 'export', NULL, NULL, '{\"export_type\":\"csv\",\"record_count\":11,\"filters\":{\"start_date\":\"2026-01-15\",\"end_date\":\"2026-01-23\"}}', 'Exported 11 records as csv', '192.168.3.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'lmkn91v05sh72cilkf3mvj9lv6', 'SUCCESS', NULL, '2026-01-23 11:25:37'),
(58, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '0l67585hifrq9td82i73pt7gcq', 'SUCCESS', NULL, '2026-01-29 09:09:15'),
(59, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '6ntaqps5tmoj4jon72boa5hn0j', 'SUCCESS', NULL, '2026-01-29 09:13:19'),
(60, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '3mirlap3pi6bt40de03l3d4f5i', 'SUCCESS', NULL, '2026-01-29 09:15:59'),
(61, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '5gpr5d2rlupv0sufkc3ubqo86a', 'SUCCESS', NULL, '2026-01-29 09:17:03'),
(62, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"admin\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'c0kil3jjt4191mqna80p7dnuco', 'FAILURE', 'User not found', '2026-01-29 09:17:16'),
(63, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'nig3u7jp3n5jb1r6a1ougvnd3c', 'SUCCESS', NULL, '2026-01-29 09:17:46'),
(64, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '0r23kqorjuhm7hvasvimdjbjqs', 'SUCCESS', NULL, '2026-01-29 09:33:22'),
(65, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'r006jbpvg3u0dro14b91i1iupo', 'SUCCESS', NULL, '2026-01-29 09:33:50'),
(66, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '9gisith960p5d40sv8tb0mmbhl', 'SUCCESS', NULL, '2026-01-29 09:59:57'),
(67, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'm3t38cg1up2c613rsi5etlcvcf', 'SUCCESS', NULL, '2026-01-29 10:02:03'),
(68, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"directorates\"}', 'Login attempt failed', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'br6sc797llugh717hbje20lj8f', 'FAILURE', 'Invalid password', '2026-01-29 10:02:47'),
(69, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"directorate\"}', 'Login attempt failed', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'br6sc797llugh717hbje20lj8f', 'FAILURE', 'User not found', '2026-01-29 10:02:59'),
(70, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '4sr6f6vkhq8mi5rm17u8i9e9m7', 'SUCCESS', NULL, '2026-01-29 10:03:38'),
(71, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.71', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'u7tufg05ccsk1a6cjjdu33fqh4', 'SUCCESS', NULL, '2026-01-29 10:05:35'),
(72, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'cfn4vldf623mmo0g9iauem23oj', 'SUCCESS', NULL, '2026-02-02 13:50:58'),
(73, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'bnoot3lsvoers6gm4f0s6sl687', 'SUCCESS', NULL, '2026-02-02 15:33:47'),
(74, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'm4g3memnov9i414ciulovuun3f', 'SUCCESS', NULL, '2026-02-06 09:08:35'),
(75, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '4itk69h7qjcdu88rhjolu12tp5', 'SUCCESS', NULL, '2026-02-06 11:08:42'),
(76, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.135', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'meg8j2nvoch8cibtkvube30ite', 'SUCCESS', NULL, '2026-02-06 12:16:31'),
(77, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'i9juk33tb104mclqd4ih3qv0uq', 'SUCCESS', NULL, '2026-02-06 12:17:03'),
(78, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'bet7586beb124r6dc64cnftb9s', 'SUCCESS', NULL, '2026-02-06 12:57:50'),
(79, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '01klitmspj8hp1j46622qfkjk4', 'SUCCESS', NULL, '2026-02-06 14:08:08'),
(80, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '0urbkgr7q9tqmhm1dgkevfdbuh', 'SUCCESS', NULL, '2026-03-18 09:18:08'),
(81, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"director\"}', 'Login attempt failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '4ld0sn58k43ia5sdq11ivbivpc', 'FAILURE', 'Invalid password', '2026-03-18 09:34:01'),
(82, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'n9s2580ud7nnu48st25ampb0o5', 'SUCCESS', NULL, '2026-03-18 09:34:08'),
(83, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'i29cql39ljnlcdijjt26voavef', 'SUCCESS', NULL, '2026-03-18 10:31:04'),
(84, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'q8od8pbnveft1jmv1079k3lbpa', 'SUCCESS', NULL, '2026-03-18 10:33:52'),
(85, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '192.168.3.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'jvm1ouu5tklt9irbgvab69b4f3', 'SUCCESS', NULL, '2026-03-18 10:34:14'),
(86, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '34tdrsg59vhupg7dr57ppa8tsl', 'SUCCESS', NULL, '2026-03-18 10:34:34'),
(87, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"directory\"}', 'Login attempt failed', '192.168.3.251', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', 'q9misd4ep9r4nupu3h5bh2vtoi', 'FAILURE', 'User not found', '2026-03-18 11:57:41'),
(88, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"director\"}', 'Login attempt failed', '192.168.3.251', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', 'q9misd4ep9r4nupu3h5bh2vtoi', 'FAILURE', 'Invalid password', '2026-03-18 11:58:03'),
(89, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.251', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', 'o759onrqbe5gbpqcrqchs3t60q', 'SUCCESS', NULL, '2026-03-18 11:58:23'),
(90, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'a1uf9o7vntl3qt80bnibfvhu7k', 'SUCCESS', NULL, '2026-03-18 12:00:24'),
(91, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'hhn9v6qki4ac9e7o7g7au30bs0', 'SUCCESS', NULL, '2026-03-18 12:27:01'),
(92, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'ihmflltimfvp0nk335lmpu57in', 'SUCCESS', NULL, '2026-03-18 12:48:08'),
(93, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'coqaokmno1jtrbvhs35of1ll4l', 'SUCCESS', NULL, '2026-03-18 13:19:24'),
(94, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '38aqibrer0kgkc9u1676tfc4km', 'SUCCESS', NULL, '2026-03-18 13:27:11'),
(95, NULL, 'SYSTEM', 'UNKNOWN', 'LOGIN_FAILED', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'Login attempt failed', '192.168.3.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'r44obc322h77popls5rja9sjhf', 'FAILURE', 'Invalid password', '2026-03-18 13:34:39'),
(96, 1, 'director', 'director', 'EXPORT', 'export', NULL, NULL, '{\"export_type\":\"csv\",\"record_count\":13,\"filters\":{\"start_date\":\"2026-03-17\",\"end_date\":\"2026-03-18\"}}', 'Exported 13 records as csv', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '38aqibrer0kgkc9u1676tfc4km', 'SUCCESS', NULL, '2026-03-18 13:35:25'),
(97, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'p2mh2og5tma767on4s4pi839vk', 'SUCCESS', NULL, '2026-03-18 13:37:02'),
(98, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '192.168.3.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'tn3uktbnpj52vurf4eq6g9f7u2', 'SUCCESS', NULL, '2026-03-18 13:44:56'),
(99, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'n1i2hvhv6li4nr4fc0s3b5tqip', 'SUCCESS', NULL, '2026-03-26 12:10:49'),
(100, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'kvholfeb5sfq42auldb02d5vd6', 'SUCCESS', NULL, '2026-03-26 12:10:59'),
(101, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '1dq5mmbige3vvridp0s39s8hpe', 'SUCCESS', NULL, '2026-03-26 12:13:20'),
(102, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'ucr365kamr5lfps3v5foj10q84', 'SUCCESS', NULL, '2026-03-26 12:13:37'),
(103, 5, 'superadmin', 'super_admin', 'AUDIT_LOG_VIEW', NULL, NULL, NULL, '{\"viewer\":\"superadmin\"}', 'Audit_log_view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'ucr365kamr5lfps3v5foj10q84', 'SUCCESS', NULL, '2026-03-26 12:20:41'),
(104, 5, 'superadmin', 'super_admin', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"superadmin\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'ssit2qtouhis44mi7cv1avjm0h', 'SUCCESS', NULL, '2026-03-27 09:17:17'),
(105, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '40ggce04bnliegln5hsqvo7laa', 'SUCCESS', NULL, '2026-04-17 10:25:09'),
(106, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', 'ojs2eagjq313iqpr9ddj4dio4t', 'SUCCESS', NULL, '2026-04-17 14:46:30'),
(107, 7, 'reception', 'receptionist', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"reception\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', 'sq1d75bsjrjnl5jialnmgugib8', 'SUCCESS', NULL, '2026-04-17 14:46:45'),
(108, 1, 'director', 'director', 'LOGIN', 'users', NULL, NULL, '{\"username\":\"director\"}', 'User login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', 'bbkcs7shujinel4hf8g4u1ff4f', 'SUCCESS', NULL, '2026-04-17 14:48:46');

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
(4, 'NelFund Support', 4000, 4020);

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

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_title`, `organizer_name`, `venue`, `event_date`, `time_slot`, `description`, `created_at`) VALUES
(1, 'Students conference', 'Students association', 'Conference room', '2026-01-23', 'afternoon', '', '2026-01-19 14:02:52'),
(2, 'Presentation', 'HR Department', 'Conference Room', '2026-01-24', 'afternoon', 'Blah Blh', '2026-01-23 11:24:33'),
(3, 'Seminar', 'director', 'Conference Room A', '2026-03-18', 'fullday', 'It will be holding for two days', '2026-03-18 13:34:03');

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
(198, 200, 2, 1),
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
(221, 103, 1, 1),
(222, 104, 1, 1),
(223, 105, 1, 1),
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
(238, 500, 1, 0),
(239, 501, 1, 0),
(240, 502, 1, 0),
(241, 503, 1, 0),
(242, 504, 1, 0),
(243, 505, 1, 0),
(244, 506, 1, 0),
(245, 507, 1, 0),
(246, 508, 1, 0),
(247, 509, 1, 0),
(248, 510, 1, 0),
(249, 511, 1, 0),
(250, 512, 1, 0),
(251, 513, 1, 0),
(252, 514, 1, 0),
(253, 515, 1, 0),
(254, 516, 1, 0),
(255, 517, 1, 0),
(256, 518, 1, 0),
(257, 519, 1, 0),
(258, 300, 3, 1),
(259, 301, 3, 1),
(260, 302, 3, 1),
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
(80, 'admin', '::1', '2026-01-29 10:17:16', 0),
(81, 'admin', '::1', '2026-01-29 10:17:16', 0),
(87, 'directorates', '192.168.3.71', '2026-01-29 11:02:47', 0),
(88, 'directorates', '192.168.3.71', '2026-01-29 11:02:47', 0),
(89, 'directorate', '192.168.3.71', '2026-01-29 11:02:59', 0),
(90, 'directorate', '192.168.3.71', '2026-01-29 11:02:59', 0),
(109, 'directory', '192.168.3.251', '2026-03-18 12:57:41', 0),
(110, 'directory', '192.168.3.251', '2026-03-18 12:57:41', 0),
(127, 'superadmin', '::1', '2026-03-27 10:17:17', 1),
(130, 'reception', '::1', '2026-04-17 15:46:45', 1),
(131, 'director', '::1', '2026-04-17 15:48:46', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `notification_type` enum('visitor_arrival','system','alert','checkout') DEFAULT 'visitor_arrival',
  `visitor_id` int(11) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `notification_type`, `visitor_id`, `action_url`, `priority`, `read_at`, `created_at`) VALUES
(1, 1, 'New visitor: Taiwo heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-05 10:56:05'),
(2, 9, 'New visitor: Taiwo heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-05 10:56:05'),
(3, 1, 'New visitor: Taiwo heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-05 12:15:08'),
(4, 21, 'New visitor: Taiwo heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-05 12:15:08'),
(5, 1, 'New visitor: Taiwo heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-05 12:16:30'),
(6, 21, 'New visitor: Taiwo heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-05 12:16:30'),
(7, 1, 'New visitor: Test User heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-05 12:28:50'),
(8, 1, 'New visitor: Taiwo heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-08 10:56:33'),
(9, 21, 'New visitor: Taiwo heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-08 10:56:33'),
(10, 1, 'New visitor: Deputy Director\'s Test heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-15 13:51:18'),
(11, 1, 'New visitor: Peter Adewale Tomiwa heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-15 15:38:21'),
(12, 1, 'New visitor: Fakunle Olufemi heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 12:59:15'),
(13, 11, 'New visitor: Fakunle Olufemi heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 12:59:15'),
(14, 1, 'New visitor: Peter Adewale heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 12:59:52'),
(15, 21, 'New visitor: Peter Adewale heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 12:59:52'),
(16, 1, 'New visitor: Kunle Ibidun heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 13:00:42'),
(17, 9, 'New visitor: Kunle Ibidun heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 13:00:42'),
(18, 1, 'New visitor: Testing testing heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 13:03:27'),
(19, 21, 'New visitor: Testing testing heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 13:03:27'),
(20, 1, 'New visitor: Taiwo heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 13:50:32'),
(21, 9, 'New visitor: Taiwo heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-19 13:50:32'),
(22, 1, 'Message from Samuel Babatunde: I would love to make enquiries about certain th...', 1, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-23 08:22:56'),
(23, 1, 'New visitor: Peter Tomiwa heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', '2026-01-29 09:34:07', '2026-01-23 10:43:17'),
(24, 10, 'New visitor: Peter Tomiwa heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-23 10:43:17'),
(25, 1, 'New visitor: Peter Tomira heading to your destination.', 1, 'visitor_arrival', NULL, NULL, 'normal', '2026-01-29 09:34:05', '2026-01-23 10:45:34'),
(26, 10, 'New visitor: Peter Tomira heading to your destination.', 0, 'visitor_arrival', NULL, NULL, 'normal', NULL, '2026-01-23 10:45:34'),
(27, 1, 'Message from Peter Tomba: to get something', 1, 'visitor_arrival', NULL, NULL, 'normal', '2026-01-29 09:34:02', '2026-01-23 11:16:10'),
(28, 1, 'New visitor: Taiwo heading to your destination.', 1, 'visitor_arrival', 12, NULL, 'high', '2026-01-29 10:06:53', '2026-01-29 10:01:46'),
(29, 21, 'New visitor: Taiwo heading to your destination.', 0, 'visitor_arrival', 12, NULL, 'high', NULL, '2026-01-29 10:01:46'),
(30, 1, 'New visitor: Andrew Timmy heading to your destination.', 1, 'visitor_arrival', 13, NULL, 'high', '2026-03-18 12:30:20', '2026-03-18 09:33:18'),
(31, 9, 'New visitor: Andrew Timmy heading to your destination.', 0, 'visitor_arrival', 13, NULL, 'high', NULL, '2026-03-18 09:33:18'),
(32, 1, 'New visitor: Peter Adewale Tomiwa heading to your destination.', 1, 'visitor_arrival', 14, NULL, 'high', '2026-03-18 12:30:17', '2026-03-18 11:17:04'),
(33, 9, 'New visitor: Peter Adewale Tomiwa heading to your destination.', 0, 'visitor_arrival', 14, NULL, 'high', NULL, '2026-03-18 11:17:04'),
(34, 1, 'New visitor: Anderson Peter heading to your destination.', 1, 'visitor_arrival', 15, NULL, 'high', '2026-03-18 12:29:06', '2026-03-18 11:59:33'),
(35, 1, 'New visitor: Peter Anderson heading to your destination.', 1, 'visitor_arrival', 16, NULL, 'high', '2026-03-18 12:29:05', '2026-03-18 12:01:34'),
(36, 1, 'New visitor: Patricia D. Burley heading to your destination.', 1, 'visitor_arrival', 17, NULL, 'high', '2026-03-18 12:29:04', '2026-03-18 12:20:23'),
(37, 1, 'New visitor: Patricia D. Burley heading to your destination.', 1, 'visitor_arrival', 18, NULL, 'high', '2026-03-18 12:29:03', '2026-03-18 12:20:57'),
(38, 1, 'New visitor: Patricia D. Burley heading to your destination.', 1, 'visitor_arrival', 19, NULL, 'high', '2026-03-18 12:28:35', '2026-03-18 12:21:29'),
(39, 17, 'New visitor: Patricia D. Burley heading to your destination.', 0, 'visitor_arrival', 19, NULL, 'high', NULL, '2026-03-18 12:21:29'),
(40, 1, 'Message from Taiwo Aderibigbe: sssssssssssssssss sssssssssssssssssssssssssss', 1, '', 20, NULL, 'low', '2026-03-18 12:29:59', '2026-03-18 12:29:48'),
(41, 1, 'New visitor: Suberu Afganistan heading to your destination.', 1, 'visitor_arrival', 21, NULL, 'high', '2026-03-18 12:55:42', '2026-03-18 12:49:12'),
(42, 1, 'New visitor: Bruce Wayne heading to your destination.', 1, 'visitor_arrival', 22, NULL, 'high', '2026-04-17 14:50:29', '2026-03-18 12:57:48'),
(43, 1, 'Message from Raphael Olayinka: Bringing food to peter', 1, '', 23, NULL, 'low', '2026-03-18 13:22:38', '2026-03-18 13:21:57'),
(44, 1, 'New visitor: Olayinka Gbede heading to your destination.', 1, 'visitor_arrival', 24, NULL, 'high', '2026-04-17 14:50:27', '2026-03-18 13:30:21'),
(45, 1, 'Message from Olayinka Gbadamosi: TO fihcaknvasvadsbdb', 1, '', 25, NULL, 'low', '2026-04-17 14:50:14', '2026-03-18 13:31:45'),
(46, 1, 'New visitor: Peter Adewale heading to your destination.', 1, 'visitor_arrival', 26, NULL, 'high', '2026-04-17 14:50:24', '2026-04-17 14:46:14'),
(47, 9, 'New visitor: Peter Adewale heading to your destination.', 0, 'visitor_arrival', 26, NULL, 'high', NULL, '2026-04-17 14:46:14');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `must_change_password` tinyint(1) DEFAULT 1,
  `availability_status` enum('available','busy','unavailable','away') DEFAULT 'available',
  `status_message` varchar(255) DEFAULT NULL,
  `last_status_change` timestamp NULL DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `destination_id`, `must_change_password`, `availability_status`, `status_message`, `last_status_change`, `profile_photo`, `phone_number`, `email`, `notification_preferences`) VALUES
(1, 'director', '$2y$10$a/Ivqv3G9qDMeAV.ZOExieqqiUZuf9FZ2/Fse8O1eKGOW/iEbZ5i6', 'director', 1, 0, 'busy', '', '2026-03-18 13:27:41', NULL, NULL, NULL, NULL),
(5, 'superadmin', '$2y$10$iN/nEDMu1hTmF4Np3ZGkyeL6HodxUU1q0IRsqjgN.XjWeNDTkExK.', 'super_admin', NULL, 0, 'away', '', '2026-01-29 09:15:54', NULL, NULL, NULL, NULL),
(7, 'reception', '$2y$10$.NuJqHFbLl4eXBJHmUXLuedcUjt59fRxKMaCZbY3qU8fAvrmRbivK', 'receptionist', NULL, 0, 'available', '', '2026-04-17 14:47:25', NULL, NULL, NULL, NULL),
(9, 'gaminghub', '$2y$10$EuGlMc90zj2H5ZrH34HARuxQK2zW9jbt1aHAVO/KDnx1KjkhhX/Ia', 'destination_admin', 3, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'nelfund', '$2y$10$MsFFqFCcwVHxXgr9hqg2LeMDpLwxehSbIBwvtPHaZ.AYiKQWYr/tC', 'destination_admin', 4, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'directorates', '$2y$10$Z52oJ6nzJu7nv/Evp/NUCuR3nHyIv3SoCfSlGbzlIcYRUmK.hmGpW', 'destination_admin', NULL, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'itemsboardsecretariat', '$2y$10$nBlefCaqt29xiV1LTay9nOjpBxWWbC61b3sG0qLYGWD9Gnf5qtJaG', 'destination_admin', 2, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'directorate_admin', '$2y$10$hd63uO0EcNHje4LL5lD1AOXABXa90tQ7ujUMsE6tWoxEBPbs9LVCK', 'destination_admin', NULL, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL);

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
  `visitor_message` text DEFAULT NULL,
  `preferred_return` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `availability_snapshot` enum('available','busy','unavailable','away') DEFAULT NULL,
  `status_message_snapshot` varchar(255) DEFAULT NULL,
  `queue_status` enum('pending','resolved','scheduled') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
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

INSERT INTO `visitors` (`id`, `token`, `expires_at`, `fullname`, `visitor_type`, `keycard`, `faculty_organization`, `phone_number`, `purpose`, `visitor_message`, `preferred_return`, `admin_id`, `availability_snapshot`, `status_message_snapshot`, `queue_status`, `admin_notes`, `resolved_at`, `destination`, `time_in`, `time_out`, `date`, `is_alternate`, `keycard_id`, `status`) VALUES
(1, '65a60055bb704993b0c69bffbb6d61b571770d506d8009d06c6f9a4ca5adeb6c', '2026-01-16 14:51:18', 'Deputy Director\'s Test', 'staff', NULL, 'Technology', '081287564646', 'To collect money', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '1', '2026-01-15 13:51:18', '2026-01-15 16:00:53', '2026-01-15', 0, 220, 'active'),
(2, '94f9476a2d2cb309b77906334d15c15f3aa3618817b4fe6fd6e4f183f6981c05', '2026-01-16 16:38:21', 'Peter Adewale Tomiwa', 'student', NULL, 'Computing', '08129744447', 'To make money', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '1', '2026-01-15 15:38:21', '2026-03-18 11:38:12', '2026-01-15', 0, 220, 'active'),
(3, '42163cac2310a70e2c979eb7b871dfab03ed75c9ada915d8e878a98a3bacafbb', '2026-01-20 13:59:15', 'Fakunle Olufemi', 'student', NULL, 'Faculty of Technology', '08129744447', 'the purpose of visit is money', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '1', '2026-01-19 12:59:15', '2026-03-18 11:38:17', '2026-01-19', 0, 238, 'active'),
(4, 'fc07eab7e00e332b07be2dcfa6920b279679daa7eebed717b0ca8abdd30767e7', '2026-01-20 13:59:52', 'Peter Adewale', 'staff', NULL, 'Faculty of Technology', '08129744447', 'To get somethign', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '1', '2026-01-19 12:59:52', '2026-01-19 14:55:36', '2026-01-19', 0, NULL, 'active'),
(5, '526553bd779054c9eefb1d3b4ae6329374619b774d443c275c88f7a4e3e68fe0', '2026-01-20 14:00:42', 'Kunle Ibidun', 'staff', NULL, 'Faculty of The Social Sciences', '08148779810', 'To see the games', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '3', '2026-01-19 13:00:42', '2026-03-18 11:38:27', '2026-01-19', 0, 260, 'active'),
(6, '9e2c53e7e6fe3e6050fa0124f52878a8844abb054d2635a0126b7c3f7cdb291d', '2026-01-20 14:03:27', 'Testing testing', 'staff', NULL, 'Faculty of Technology', '08181721020', 'Offical purpose', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '1', '2026-01-19 13:03:27', '2026-01-19 14:55:20', '2026-01-19', 0, NULL, 'active'),
(7, 'dc023d2dcce2a97d8434b553db43bc7c9df3242c472a029d62d50371d7bc5c3f', '2026-01-20 14:50:32', 'Taiwo', 'student', NULL, 'Faculty of The Social Sciences', '08085978755', 'official purpose', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '3', '2026-01-19 13:50:32', '2026-01-19 14:56:58', '2026-01-19', 0, 261, 'active'),
(8, '83cca0e3afd0d2914fcd66f780fb1be18e26a6a10a77982e8944802807df0f07', '2026-01-24 09:22:56', 'Samuel Babatunde', 'staff', NULL, 'Faculty of Veterinary Medicine', '08134736529', 'I would love to make enquiries about certain things', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '1', '2026-01-23 08:22:56', '2026-03-18 11:38:28', '2026-01-23', 1, NULL, 'active'),
(9, 'aa6a3f8202b279d4f7cebfaab8264fee163a036104f7b5e1aa00c4fafd749440', '2026-01-24 11:43:17', 'Peter Tomiwa', 'student', NULL, 'Technology', '09035647382', 'To make enquiries about nelfund support', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '4', '2026-01-23 10:43:17', '2026-01-23 11:48:36', '2026-01-23', 0, 178, 'active'),
(10, '7005512d4e30120be64a0ef11c16f6b9efba90ed84dc44c9c546cd9c1aa70b9b', '2026-01-24 11:45:34', 'Peter Tomira', 'student', NULL, 'Faculty of Technology', '09035647362', 'To make enquiries about nelfund support', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '4', '2026-01-23 10:45:34', '2026-03-18 11:38:23', '2026-01-23', 0, 179, 'active'),
(11, '7935b0a8192f5e78edbe87e52209ad8c17d967555ebf1d271da7be0698be1161', '2026-01-24 12:16:10', 'Peter Tomba', 'staff', NULL, 'Faculty of Technology', '09035647365', 'to get something', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '1', '2026-01-23 11:16:10', '2026-03-18 11:38:25', '2026-01-23', 1, NULL, 'active'),
(12, '960c78ba472dd949c862404b32b3e044d39e6657090b66cad58e80a5ae293dc5', '2026-01-30 11:01:46', 'Taiwo', 'student', NULL, 'Faculty of Engineering', '08085978755', 'helloo fffffffffffffffffffff', NULL, NULL, NULL, NULL, NULL, 'resolved', NULL, NULL, '1', '2026-01-29 10:01:46', '2026-03-18 11:38:20', '2026-01-29', 0, NULL, 'active'),
(13, 'ad18b5ab7a30b044d779e3865f2ca68022b5e2bba07a85b7db31978a1a1b3040', '2026-03-19 10:33:18', 'Andrew Timmy', 'student', NULL, 'Faculty of Engineering', '08128547658', 'To play some more games', '', NULL, 9, 'available', '', NULL, NULL, NULL, '3', '2026-03-18 09:33:18', '2026-03-18 11:38:21', '2026-03-18', 0, 261, 'active'),
(14, '86982bc19f656d406ecd5210fc21c8f3d097e9139fd96c4abdec7dc3634efd35', '2026-03-19 12:17:04', 'Peter Adewale Tomiwa', 'staff', NULL, 'Faculty of Veterinary Medicine', '09045673897', 'TO play some games and love', NULL, NULL, 9, 'available', '', NULL, NULL, NULL, '3', '2026-03-18 11:17:04', '0000-00-00 00:00:00', '2026-03-18', 0, 260, 'active'),
(15, '8fc1fb01c9cbddc22848f92ee0a526f47141424d7f982b4781694713d43cbe6e', '2026-03-19 12:59:33', 'Anderson Peter', 'staff', NULL, 'Faculty of Technology', '08128774447', 'to slap you', NULL, NULL, 1, 'available', 'back by 2pm', NULL, NULL, NULL, '1', '2026-03-18 11:59:33', '0000-00-00 00:00:00', '2026-03-18', 0, 220, 'active'),
(16, '4dbcda2c8025706a1c7f5889b958e74c7363d1872c98611042e4a918b734cf67', '2026-03-19 13:01:34', 'Peter Anderson', 'staff', NULL, 'Faculty of The Social Sciences', '09087653438', 'To evolve basically', NULL, NULL, 1, 'available', 'back by 2pm', NULL, NULL, NULL, '1', '2026-03-18 12:01:34', '0000-00-00 00:00:00', '2026-03-18', 0, 221, 'active'),
(17, '856ad07adec9a1883d095ecaa4f2fd31b6139aff302eb8f2c7361dec91a2fc16', '2026-03-19 13:20:23', 'Patricia D. Burley', 'student', NULL, 'Faculty of The Social Sciences', '5709285689', 'sgggggggggggg gh', NULL, NULL, 1, 'available', 'back by 2pm', NULL, NULL, NULL, '1', '2026-03-18 12:20:23', '0000-00-00 00:00:00', '2026-03-18', 0, 222, 'active'),
(18, '1b9f1c57b10bb5bd3dae02c09370f99ef616b8cd743ed20f4fc19150355c156b', '2026-03-19 13:20:57', 'Patricia D. Burley', 'guest', NULL, 'Faculty of Engineering', '5709285689', 'ssssssssss sssssssssss', NULL, NULL, 1, 'available', 'back by 2pm', NULL, NULL, NULL, '1', '2026-03-18 12:20:57', '0000-00-00 00:00:00', '2026-03-18', 0, 223, 'active'),
(19, '4f7dbf92e47ed2c3cb2365f92a8ef2691be7f30923cde62f25a52aca0c6deda9', '2026-03-19 13:21:29', 'Patricia D. Burley', 'guest', NULL, 'Faculty of The Social Sciences', '5709285689', 'sssggggg jjjjjjjj', NULL, NULL, 17, 'available', '', NULL, NULL, NULL, '2', '2026-03-18 12:21:29', '0000-00-00 00:00:00', '2026-03-18', 0, 198, 'active'),
(20, 'ce283e265e5afb48357c7c4510db63fdadf898a5c8a5e2f9b87948df3bc519a4', '2026-03-19 13:29:48', 'Taiwo Aderibigbe', 'guest', NULL, 'Faculty of Engineering', '08085978751', 'sssssssssssssssss sssssssssssssssssssssssssss', NULL, NULL, 1, 'available', 'back by 2pm', NULL, NULL, NULL, '1', '2026-03-18 12:29:48', '2026-03-18 14:39:20', '2026-03-18', 1, NULL, 'active'),
(21, '8d73734eab290ed3d657d686fa95c9beb9e401cf28386b67b1e5211d69f3770a', '2026-03-19 13:49:12', 'Suberu Afganistan', 'staff', NULL, 'Faculty of Basic Medical Sciences', '09047392747', 'The purpose is to fly and soar and win', NULL, NULL, 1, 'available', 'back by 2pm', NULL, NULL, NULL, '1', '2026-03-18 12:49:12', '2026-03-18 13:57:09', '2026-03-18', 0, 224, 'active'),
(22, '31d23722798d945541f00899ac1ec978054855991d6739a320eac3648653aa5b', '2026-03-19 13:57:48', 'Bruce Wayne', 'student', NULL, 'Faculty of Computing', '09135649876', 'To press computers and make money', NULL, NULL, 1, 'available', 'back by 2pm', NULL, NULL, NULL, '1', '2026-03-18 12:57:48', '2026-03-18 14:38:38', '2026-03-18', 0, 224, 'active'),
(23, '6a90514249095f37ea297905626aaeef49b875539a4fa65fbc4e920bd596f881', '2026-03-19 14:21:57', 'Raphael Olayinka', 'staff', NULL, 'Faculty of Technology', '09164783647', 'Bringing food to peter', NULL, NULL, 1, 'busy', 'i am going to be busy for a while', NULL, NULL, NULL, '1', '2026-03-18 13:21:57', '2026-03-18 14:38:29', '2026-03-18', 1, NULL, 'active'),
(24, '2acb4de2d14e6cd3f9d0e8221117bbd7ede13bb3ffe4b7a4e40ac19c2bc57fcf', '2026-03-19 14:30:21', 'Olayinka Gbede', 'staff', NULL, 'Eko Hotels', '07036748756', 'I am bringing a million dollars into the building', NULL, NULL, 1, 'busy', '', NULL, NULL, NULL, '1', '2026-03-18 13:30:21', '2026-03-18 14:37:31', '2026-03-18', 0, 225, 'active'),
(25, 'e9fb1eaeb7e92ede09a6ff10099255b7b66bf9b3b2398b18bee244137f6f5e5a', '2026-03-19 14:31:45', 'Olayinka Gbadamosi', 'staff', NULL, 'Eko Hotels', '09056748362', 'TO fihcaknvasvadsbdb', NULL, NULL, 1, 'busy', '', NULL, NULL, NULL, '1', '2026-03-18 13:31:45', '2026-03-18 14:38:23', '2026-03-18', 1, NULL, 'active'),
(26, 'bae21c91d0a75bf0bcde0df004d5feb931d375eb118722ee344c5f1a11df6e25', '2026-04-18 16:46:14', 'Peter Adewale', 'student', NULL, 'Faculty of Technology', '08129744449', 'to do something', NULL, NULL, 9, 'available', '', NULL, NULL, NULL, '3', '2026-04-17 15:46:14', '2026-04-17 16:46:56', '2026-04-17', 0, 261, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `websocket_sessions`
--

CREATE TABLE `websocket_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(64) NOT NULL,
  `connection_id` varchar(128) DEFAULT NULL,
  `connected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_ping` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_availability_log`
--
ALTER TABLE `admin_availability_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`changed_at`),
  ADD KEY `idx_status` (`new_status`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_notification_visitor` (`visitor_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_type` (`notification_type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_admin_destination` (`destination_id`),
  ADD KEY `idx_availability` (`availability_status`,`destination_id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `keycard_id` (`keycard_id`);

--
-- Indexes for table `websocket_sessions`
--
ALTER TABLE `websocket_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_last_ping` (`last_ping`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_availability_log`
--
ALTER TABLE `admin_availability_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `keycards`
--
ALTER TABLE `keycards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `websocket_sessions`
--
ALTER TABLE `websocket_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_availability_log`
--
ALTER TABLE `admin_availability_log`
  ADD CONSTRAINT `admin_availability_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `keycards`
--
ALTER TABLE `keycards`
  ADD CONSTRAINT `keycards_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_visitor` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `websocket_sessions`
--
ALTER TABLE `websocket_sessions`
  ADD CONSTRAINT `websocket_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
