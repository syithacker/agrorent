-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 07:51 PM
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
-- Database: `agrorent`
--

-- --------------------------------------------------------

--
-- Table structure for table `lands`
--

CREATE TABLE `lands` (
  `land_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `size` varchar(100) NOT NULL,
  `price_per_month` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT 'https://images.unsplash.com/photo-1599849581337-33b00f685c2b',
  `document_url` varchar(255) DEFAULT NULL,
  `status` enum('pending_approval','available','booked','rejected') NOT NULL DEFAULT 'pending_approval'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lands`
--

INSERT INTO `lands` (`land_id`, `owner_id`, `title`, `location`, `size`, `price_per_month`, `description`, `image_url`, `document_url`, `status`) VALUES
(1, 1, 'kudal agro', 'maharashtra', '10 Acres', 15000.00, 'Fertile land perfect for rice farming.', 'https://imgs.search.brave.com/1taf4d7zXrxblC4seNgokh4FU5lJs3Pw2XNTbYJmnRs/rs:fit:500:0:1:0/g:ce/aHR0cHM6Ly9zdGF0/aWMzLmJpZ3N0b2Nr/cGhvdG8uY29tLzMv/NS8yL2xhcmdlMi8y/NTM5MDY3MDguanBn', NULL, 'booked'),
(2, 1, 'sawantwadi agro', 'Riverbend, wadi', '5 Acres', 9500.00, 'Rich soil ideal for vegetables and seasonal crops. Located near the main river for easy water access.', 'https://imgs.search.brave.com/mPhGfQLXa0xOFGpamjXVR04m5uG39BzehfNcfeNUFig/rs:fit:500:0:1:0/g:ce/aHR0cHM6Ly81Lmlt/aW1nLmNvbS9kYXRh/NS9aRC9YWC9WVy9B/TkRST0lELTMwOTI1/MDQ1LzE1NjE0MDM1/OTY0NTAtanBnLTEw/MDB4MTAwMC5qcGc', NULL, 'booked'),
(3, 1, 'dhuri farm', 'Greenwood, vengurla', '20 Acres', 22000.00, 'Expansive plot suitable for large-scale farming. Includes a small barn for storage. Soil tested and approved.', 'https://imgs.search.brave.com/r_HKzOhqhW70di0IQymU73tN3Od-8n0elofVf6Ppk0g/rs:fit:500:0:1:0/g:ce/aHR0cHM6Ly9nZW51/aW5lcGxvdHMuY29t/L2ltYWdlcy9wcm9w/ZXJ0eV9pbWFnZXMv/S29rYW53YWRpLU5B/LUJ1bmdsb3ctUGxv/dHMuanBn', NULL, 'available'),
(4, 4, 'Zarap farmhouse', 'A/P zaprap near zarap patradevi highway', '0.10 acres', 1000.00, 'best land main roadtouch best for vegetable farming', 'uploads/img_68de8eb1cbcb26.31139755.jpg', 'uploads/doc_68de8eb1cc2f68.31500959.pdf', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `land_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','active','completed') NOT NULL DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notification_seen_by_farmer` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `land_id`, `farmer_id`, `owner_id`, `status`, `request_date`, `notification_seen_by_farmer`) VALUES
(1, 1, 2, 1, 'approved', '2025-09-27 17:40:06', 0),
(2, 2, 5, 1, 'rejected', '2025-09-27 18:03:01', 0),
(3, 1, 5, 1, 'approved', '2025-09-29 14:46:11', 1),
(4, 1, 5, 1, 'approved', '2025-09-29 15:19:25', 1),
(5, 2, 5, 1, 'rejected', '2025-09-29 15:21:42', 0),
(6, 2, 5, 1, 'rejected', '2025-09-30 03:18:04', 0),
(7, 2, 5, 1, 'rejected', '2025-10-01 09:08:40', 0),
(8, 2, 4, 1, 'approved', '2025-10-02 15:13:37', 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(3, 'admin'),
(1, 'farmer'),
(2, 'owner');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `address`) VALUES
(1, 'Atharva Dhuri', 'admin@agr.com', '$2y$10$GLjY5y./h8418L1s72osN.2zE7s9V8y34qY94fbsIICGzF.9.sQeC', '1234567890', '123 Green Valley, Farmville'),
(2, 'admin', 'atharvadhuri0082@gmail.com', '$2y$10$HcTJl5SmldRc9jyBIuBFoe5PxmV7BhdERm9uFOE06UGtAxc0.4t9K', '09405247422', '988 kumbharli malgaon 416510'),
(3, 'atharva', 'demo@gmail.com', '$2y$10$OpPIMQytaqx4LRfmuNcOe.VbgIxJTcnI/csymdO/2uMz.A5a4IlOG', '9421264832', 'sawantwadi'),
(4, 'atharva', 'a@gmail.com', '$2y$10$4S8Wi/NaR5TAFVEtkFoFWuUQaBuUjoz7SyCQvGyfckRtx4Y4.j.4m', '09405247422', '988 kumbharli malgaon 416510'),
(5, 'farmer', 'farmer@gmail.com', '$2y$10$b2O3EHQmbzIEpE65.eTU3.ldZZSupoO4V7y0YyTWx.5yBrXnRtYx2', '09405247422', 'malgaon'),
(6, 'owner', 'owner@gmail.com', '$2y$10$4MSa24J3aNebnejxy/fxLekmesijOJj703IzIEkIcw9Sb98FK875y', '09405247422', 'sawantwadi'),
(7, 'user101', 'u@gmail.com', '$2y$10$j2/QhCh9k.W1F9OwQ6lSCO6HW.aQGH4H0u0YC5a0UCT.HQKhaBhHq', '8989898989', 'stakeholder'),
(8, 'vedika', 'vedika@gmail.com', '$2y$10$nrQG5gerSEim5etUbffBtO7ixkSoUk1dZuTGkCW/fpzTP51uuBgM2', '9494949494', 'swd');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 2, 3),
(5, 3, 2),
(6, 4, 1),
(7, 2, 3),
(8, 5, 1),
(9, 6, 2),
(10, 7, 1),
(11, 7, 2),
(12, 8, 1),
(13, 8, 2),
(14, 2, 2),
(15, 4, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lands`
--
ALTER TABLE `lands`
  ADD PRIMARY KEY (`land_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `land_id` (`land_id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lands`
--
ALTER TABLE `lands`
  MODIFY `land_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lands`
--
ALTER TABLE `lands`
  ADD CONSTRAINT `lands_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`land_id`) REFERENCES `lands` (`land_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
