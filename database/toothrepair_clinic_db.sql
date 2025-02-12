-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2025 at 02:09 AM
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
-- Database: `toothrepair_clinic_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `picture` varchar(255) DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`, `name`, `picture`) VALUES
(2, 'admin', '$2y$10$P.lOtt2q9Uy.rdy1sruR0O.yQTaFuJ2yMEzSA3A63nh2HqKc2yM1W', 'administrator', 'default.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Booked','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `cancel_reason` text DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_deletions`
--

CREATE TABLE `appointment_deletions` (
  `deletion_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `delete_reason` text NOT NULL,
  `deleted_at` datetime NOT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_deletions`
--

INSERT INTO `appointment_deletions` (`deletion_id`, `appointment_id`, `delete_reason`, `deleted_at`, `CREATED_AT`) VALUES
(4, 39, 'test to delete', '2025-02-11 17:47:06', '2025-02-11 09:47:06'),
(5, 40, 'test delete', '2025-02-11 17:53:33', '2025-02-11 09:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `availability_tb`
--

CREATE TABLE `availability_tb` (
  `id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availability_tb`
--

INSERT INTO `availability_tb` (`id`, `available_date`, `time_start`, `time_end`, `created_at`, `is_active`) VALUES
(182, '2025-02-12', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(183, '2025-02-13', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(184, '2025-02-14', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(185, '2025-02-15', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(186, '2025-02-16', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(187, '2025-02-17', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(188, '2025-02-18', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(189, '2025-02-19', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(190, '2025-02-24', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1),
(191, '2025-02-25', '08:00:00', '16:00:00', '2025-02-12 00:49:30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `clinic_settings`
--

CREATE TABLE `clinic_settings` (
  `setting_id` int(11) NOT NULL,
  `max_daily_appointments` int(11) DEFAULT 10,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinic_settings`
--

INSERT INTO `clinic_settings` (`setting_id`, `max_daily_appointments`, `contact_number`, `email`, `address`, `logo`, `cover`) VALUES
(1, 16, '09635963243', 'admin@gmail.com', 'malasiqui', 'uploads/default_logo.png', 'uploads/default_cover.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `price`, `image_url`) VALUES
(1, 'Tooth Extraction', 'Removal of a damaged or decayed tooth to prevent further complications.', 1500.00, NULL),
(2, 'Dental Cleaning', 'Professional cleaning to remove plaque, tartar, and stains for better oral health.', 1200.00, NULL),
(3, 'Tooth Filling', 'Restoration of decayed or damaged teeth using dental fillings.', 1800.00, NULL),
(4, 'Root Canal Treatment', 'Procedure to save an infected tooth by removing the pulp and sealing it.', 5000.00, NULL),
(5, 'Dental Braces', 'Orthodontic treatment to correct misaligned teeth and improve bite.', 25000.00, NULL),
(6, 'Teeth Whitening', 'Cosmetic procedure to lighten and brighten stained teeth.', 3000.00, NULL),
(7, 'Dental Crowns', 'Caps placed over damaged teeth to restore shape, size, and strength.', 8000.00, NULL),
(8, 'Dentures', 'Removable replacements for missing teeth.', 15000.00, NULL),
(9, 'Gum Treatment', 'Treatment for gum diseases like gingivitis and periodontitis.', 2000.00, NULL),
(10, 'Dental Implants', 'Surgical placement of artificial tooth roots for missing teeth replacement.', 35000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `email`, `password`, `contact_number`, `address`, `created_at`, `reset_token`, `reset_expiry`) VALUES
(50, 'Rany boy Templado', 'ranney.templado20@gmail.com', '$2y$10$QY9rT3ICVfjdmhqw7bPy0e3XO3l/sO3hM13nHCQS8eUFKIEptYVYq', '09635963243', 'malasiqui', '2025-02-11 13:47:07', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_service` (`service_id`);

--
-- Indexes for table `appointment_deletions`
--
ALTER TABLE `appointment_deletions`
  ADD PRIMARY KEY (`deletion_id`);

--
-- Indexes for table `availability_tb`
--
ALTER TABLE `availability_tb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `available_date` (`available_date`),
  ADD UNIQUE KEY `idx_available_date` (`available_date`);

--
-- Indexes for table `clinic_settings`
--
ALTER TABLE `clinic_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `appointment_deletions`
--
ALTER TABLE `appointment_deletions`
  MODIFY `deletion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `availability_tb`
--
ALTER TABLE `availability_tb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=192;

--
-- AUTO_INCREMENT for table `clinic_settings`
--
ALTER TABLE `clinic_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`),
  ADD CONSTRAINT `fk_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
