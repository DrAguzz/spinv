-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 02:29 PM
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
-- Database: `spinv`
--

-- --------------------------------------------------------

--
-- Table structure for table `marble_type`
--

CREATE TABLE `marble_type` (
  `type_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(300) NOT NULL,
  `finish_type` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marble_type`
--

INSERT INTO `marble_type` (`type_id`, `code`, `name`, `finish_type`) VALUES
(1, 'XYZ123', 'EXOTIC', 'POLISHED'),
(2, 'XYZ456', 'GRANITE', 'POLISED');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(200) NOT NULL,
  `password` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`, `password`) VALUES
(1, 'Accountant', '$2y$10$hawOc3c/9ybXhrA//IpNSufZpvh4EL5iCHv63xvRbI/4rWjWQ2uuC'),
(2, 'Production', '$2y$10$mcNYtH63W5xG3by.uKcFluxiAALXiJql/k1nyv/ovpG/uyGE/nOC.'),
(3, 'Purchasing', '$2y$10$dLYBxHQKwhFDEAqxkJawLOnjosizRJZWzRs8Khy0OtDAURVApJgye');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `stock_id` varchar(10) NOT NULL,
  `description` varchar(300) NOT NULL,
  `type_id` int(11) NOT NULL,
  `length` double NOT NULL,
  `width` double NOT NULL,
  `quantity` double NOT NULL,
  `total_area` double NOT NULL,
  `cost_per_m2` double NOT NULL,
  `total_amount` double NOT NULL,
  `image` varchar(900) NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `stock_id`, `description`, `type_id`, `length`, `width`, `quantity`, `total_area`, `cost_per_m2`, `total_amount`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'RG01', 'Royal Gri (GL-911)', 1, 2960, 2030, 1, 4.48, 202, 904.35, '1763273901_CopyofAHLIMAJLISMESYUARAT.png', 1, '2025-12-03 12:26:01', '2025-12-04 09:49:23'),
(2, 'RG02', 'Royal Gri (GL-911)', 1, 2960, 2030, 1, 4.48, 202, 904.35, '1763274126_WhatsAppImage2025-09-25at08.11.34_30046751.jpg', 1, '2025-12-03 12:26:01', '2025-12-04 09:49:29'),
(3, 'RG03', 'Royal Gri (GL-911)', 1, 2, 2, 1, 4.48, 202, 904.35, '1763275289_Hentikanbuli.png', 3, '2025-12-03 12:26:01', '2025-12-16 00:59:19'),
(4, 'RG04', 'Royal Gri (GL-912)', 1, 2, 2, 1, 4.48, 202, 904.35, '1763277417_posterKiar.jpg', 0, '2025-12-03 12:26:01', '2025-12-03 12:26:01'),
(5, 'RG05', 'Royal Gri (GL-912)', 1, 2, 2, 1, 4.48, 202, 904.35, '1763425713_WhatsAppImage2025-09-25at08.11.34_30046751.jpg', 0, '2025-12-03 12:26:01', '2025-12-03 12:26:01'),
(6, 'EX231', 'Mkan', 1, 2960, 2030, 1, 4.48, 202, 8990, '1764866567_6931ba0711220.jpg', 0, '2025-12-04 16:42:47', '2025-12-04 16:42:47'),
(7, 'STK001', 'EXOTIC Premium Slab', 1, 240, 120, 1, 2.88, 150.5, 433.44, 'STK001_1765364865.jpg', 1, '2025-12-10 11:07:45', '2025-12-10 11:07:45'),
(8, 'STK002', 'EXOTIC Premium Slab', 1, 2500, 2980, 1.5, 1117.5, 80, 89400, 'STK002_1765364866.jpg', 1, '2025-12-10 11:07:46', '2025-12-10 11:07:46'),
(9, 'STK005', 'EXOTIC Premium Slab', 1, 2500, 2980, 1.5, 1117.5, 80, 89400, 'STK005_1765364983.jpg', 1, '2025-12-10 11:09:43', '2025-12-10 11:09:43'),
(10, 'BMI-001', 'EXOTIC Premium Slab', 1, 240, 120, 1, 2.88, 150.5, 433.44, 'BMI-001_1766007548.jpg', 1, '2025-12-17 21:39:08', '2025-12-17 21:39:08'),
(11, 'BMI-002', 'GRANITE Premium Slab', 2, 240, 120, 1, 2.88, 150.5, 433.44, 'BMI-002_1766007548.jpg', 1, '2025-12-17 21:39:08', '2025-12-17 21:39:08'),
(12, 'BMI-004', 'GRANITE', 2, 250, 120, 1, 3, 160, 480, 'BMI-004_1766007549.jpg', 1, '2025-12-17 21:39:09', '2025-12-17 21:39:09'),
(13, 'LMN-001', 'EXOTIC Premium Slab', 1, 240, 120, 1, 2.88, 150.5, 433.44, 'LMN-001_1766024210.jpg', 1, '2025-12-18 02:16:50', '2025-12-18 02:16:50'),
(14, 'LMK-002', 'GRANITE Premium Slab', 2, 240, 120, 1, 2.88, 150.5, 433.44, 'LMK-002_1766024211.jpg', 1, '2025-12-18 02:16:51', '2025-12-18 02:16:51');

-- --------------------------------------------------------

--
-- Table structure for table `stock_record`
--

CREATE TABLE `stock_record` (
  `record_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(300) NOT NULL,
  `action_date` date NOT NULL,
  `qty_change` double NOT NULL,
  `note` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_record`
--

INSERT INTO `stock_record` (`record_id`, `stock_id`, `user_id`, `action_type`, `action_date`, `qty_change`, `note`) VALUES
(1, 1, 1, 'approved', '2025-12-04', 0, 'Product approved by accountant'),
(2, 2, 1, 'approved', '2025-12-04', 0, 'Product approved by accountant'),
(3, 6, 1, 'add', '2025-12-05', 1.28, 'Product added successfully'),
(4, 7, 1, 'STOCK_IN', '2025-12-10', 1, 'Bulk import via CSV'),
(5, 8, 1, 'STOCK_IN', '2025-12-10', 1.5, 'Bulk import via CSV'),
(6, 9, 1, 'STOCK_IN', '2025-12-10', 1.5, 'Bulk import via CSV'),
(7, 10, 1, 'STOCK_IN', '2025-12-17', 1, 'Bulk import via CSV'),
(8, 11, 1, 'STOCK_IN', '2025-12-17', 1, 'Bulk import via CSV'),
(9, 12, 1, 'STOCK_IN', '2025-12-17', 1, 'Bulk import via CSV'),
(10, 13, 1, 'STOCK_IN', '2025-12-18', 1, 'Bulk import via CSV'),
(11, 14, 1, 'STOCK_IN', '2025-12-18', 1, 'Bulk import via CSV');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `image` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `role_id`, `status`, `image`) VALUES
(1, 'Zack', 'z@gmail.com', 1, 1, '1764081370_WhatsApp Image 2024-09-10 at 23.52.40_da6b03f3.jpg'),
(2, 'Aish Haikal', 'ahahh@gail.coi', 2, 1, '1764064503_WIN_20240902_11_07_13_Pro.jpg'),
(16, 'pawiy', 'pawi@gmail', 2, 1, '1764064428_WhatsApp Image 2024-09-10 at 23.50.10_f6841808.jpg'),
(17, 'Mxtdan', 'mxtdan@gmail.com', 2, 1, '1764083815_WhatsApp Image 2024-09-10 at 09.20.49_2d988a65.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `marble_type`
--
ALTER TABLE `marble_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `stock_record`
--
ALTER TABLE `stock_record`
  ADD PRIMARY KEY (`record_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `marble_type`
--
ALTER TABLE `marble_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `stock_record`
--
ALTER TABLE `stock_record`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
