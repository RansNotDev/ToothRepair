-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2025 at 01:24 PM
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

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `service_id`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`, `cancel_reason`, `cancelled_at`) VALUES
(53, 55, 15, '2025-02-22', '14:30:00', 'Pending', NULL, '2025-02-16 15:36:51', NULL, NULL);

--
-- Triggers `appointments`
--
DELIMITER $$
CREATE TRIGGER `after_appointment_complete` AFTER UPDATE ON `appointments` FOR EACH ROW BEGIN
    -- Only proceed if status changed to completed and record doesn't exist
    IF NEW.status = 'completed' AND OLD.status != 'completed' AND 
       NOT EXISTS (SELECT 1 FROM appointment_records WHERE appointment_id = NEW.appointment_id) THEN
        
        INSERT INTO appointment_records (
            appointment_id,
            user_id,
            service_id,
            appointment_date,
            appointment_time,
            service_name,
            status,
            completion_date
        )
        SELECT 
            a.appointment_id,
            a.user_id,
            a.service_id,
            a.appointment_date,
            a.appointment_time,
            s.service_name,
            'completed',
            NOW()
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        WHERE a.appointment_id = NEW.appointment_id;
        
    END IF;
END
$$
DELIMITER ;

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
(10, 47, 'asdasd', '2025-02-13 11:32:18', '2025-02-13 03:32:18');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_records`
--

CREATE TABLE `appointment_records` (
  `record_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `completion_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `availability_tb`
--

CREATE TABLE `availability_tb` (
  `id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `max_daily_appointments` int(11) NOT NULL DEFAULT 8
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availability_tb`
--

INSERT INTO `availability_tb` (`id`, `available_date`, `time_start`, `time_end`, `max_daily_appointments`) VALUES
(1, '2025-02-21', '08:00:00', '16:00:00', 8),
(2, '2025-02-22', '08:00:00', '16:00:00', 9);

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
(1, 18, '09635963243', 'admin@gmail.com', 'malasiqui', 'uploads/default_logo.png', 'uploads/default_cover.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `feedback_text` text DEFAULT NULL,
  `satisfaction_level` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `rating`, `feedback_text`, `satisfaction_level`, `created_at`) VALUES
(16, 55, 5, 'asdasd', 'Very Satisfied', '2025-02-16 08:53:41');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `price`, `image`) VALUES
(15, 'Tooth Extraction', 'Safe and professional removal of damaged or decayed teeth to relieve pain and prevent further oral health issues', 500.00, NULL),
(16, 'Wisdom Tooth Extraction', 'Surgical removal of impacted or problematic wisdom teeth to prevent pain, infection, and alignment issues.', 1000.00, NULL),
(17, 'Dental Restoration', 'Repair of decayed, chipped, or damaged teeth using high-quality dental materials to restore function and aesthetics.', 500.00, NULL),
(18, 'Oral Prophylaxis', 'Professional teeth cleaning to remove plaque, tartar, and stains, ensuring optimal oral hygiene and fresher breath.', 500.00, NULL),
(19, 'Braces', 'Orthodontic treatment using metal braces to gradually straighten teeth and improve bite alignment.', 35000.00, NULL),
(20, 'Fixed Bridge Plastic', 'A permanent solution to replace missing teeth, restoring both function and aesthetics.', 3000.00, NULL),
(21, 'Fixed Bridge Porcelain', 'A permanent solution to replace missing teeth, restoring both function and aesthetics.', 5000.00, NULL),
(22, 'Dentures (Full Upper & Lower) China Brand', 'Custom-made removable dentures designed for comfort and durability to restore your smile and chewing ability.', 10000.00, NULL),
(23, 'Dentures (Full Upper & Lower) Japan Brand', 'Custom-made removable dentures designed for comfort and durability to restore your smile and chewing ability.', 12000.00, NULL),
(24, 'Dentures (Full Upper & Lower) US Brand', 'Custom-made removable dentures designed for comfort and durability to restore your smile and chewing ability.', 15000.00, NULL);

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
(55, 'Rany Boy Templado', 'ranney.templado20@gmail.com', '$2y$10$WmsxjU5gcynM78Fo5IRsaezG5GeFCc03tAnDCNgzGyHf7ljwHVzXi', '09123456789', 'Montemayor Malasiqui', '2025-02-13 14:22:34', NULL, NULL);

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
-- Indexes for table `appointment_records`
--
ALTER TABLE `appointment_records`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `idx_appointment_id` (`appointment_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_completion_date` (`completion_date`);

--
-- Indexes for table `availability_tb`
--
ALTER TABLE `availability_tb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`available_date`);

--
-- Indexes for table `clinic_settings`
--
ALTER TABLE `clinic_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `appointment_deletions`
--
ALTER TABLE `appointment_deletions`
  MODIFY `deletion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `appointment_records`
--
ALTER TABLE `appointment_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `availability_tb`
--
ALTER TABLE `availability_tb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clinic_settings`
--
ALTER TABLE `clinic_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `appointment_records`
--
ALTER TABLE `appointment_records`
  ADD CONSTRAINT `appointment_records_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `appointment_records_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointment_records_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
