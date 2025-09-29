-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2025 at 08:35 AM
-- Server version: 8.4.2
-- PHP Version: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prototype_nutribot`
--

-- --------------------------------------------------------

--
-- Table structure for table `nutrition_data`
--

CREATE TABLE `nutrition_data` (
  `id` int NOT NULL,
  `fruit` varchar(10) DEFAULT NULL,
  `vegetables` text,
  `carbs` text,
  `dairy` text,
  `protein` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nutrition_data`
--

INSERT INTO `nutrition_data` (`id`, `fruit`, `vegetables`, `carbs`, `dairy`, `protein`, `created_at`) VALUES
(1, '', '', '', '', 'aa', '2025-09-23 11:12:26'),
(2, '4-5', '', 'yes bread', 'no', 'none', '2025-09-23 11:13:05'),
(3, '4-5', '', 'yes bread', 'no', 'none', '2025-09-23 11:15:59'),
(4, '4-5', '', 'yes bread', 'no', 'none', '2025-09-23 11:16:06'),
(5, '2-3', 'aa', 'ss', 'ss', 'ss', '2025-09-23 11:18:53'),
(6, '4-5', 'wortels', 'no', 'yes', '2 eggs', '2025-09-23 11:19:14'),
(7, '0-1', 'avocado', 'idk', 'nee', 'none', '2025-09-23 11:21:49'),
(8, '', '', '', '', '', '2025-09-23 11:23:07'),
(9, '', '', '', '', '', '2025-09-23 11:23:08'),
(10, '', '', '', '', '', '2025-09-23 11:23:09'),
(11, '', '', '', '', '', '2025-09-23 11:23:09'),
(12, '', '', '', '', '', '2025-09-23 11:23:10'),
(13, '', '', '', '', '', '2025-09-23 11:23:10'),
(14, '4-5', 'aa', 'aa', 'aa', 'a', '2025-09-23 11:29:07'),
(15, '5+', 'aa', 'aa', 'aa', 'aa', '2025-09-23 11:33:28'),
(16, '4-5', ',msdas', 'mdmd', 'dd', 'dd', '2025-09-23 11:37:49'),
(17, '4-5', ',msdas', 'mdmd', 'dd', 'dd', '2025-09-25 11:52:49'),
(18, '4-5', 'aa', 'aa', 'aa', 'aa', '2025-09-25 12:45:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nutrition_data`
--
ALTER TABLE `nutrition_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nutrition_data`
--
ALTER TABLE `nutrition_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
