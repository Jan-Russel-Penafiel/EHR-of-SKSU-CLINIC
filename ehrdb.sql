-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2025 at 02:00 PM
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
-- Database: `ehrdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `Num` int(11) NOT NULL,
  `IDNumber` varchar(20) NOT NULL,
  `GmailAccount` varchar(90) NOT NULL,
  `password` varchar(255) NOT NULL,
  `years` year(4) DEFAULT year(curdate()),
  `months` varchar(20) DEFAULT monthname(curdate()),
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`Num`, `IDNumber`, `GmailAccount`, `password`, `years`, `months`, `otp`, `otp_expiry`) VALUES
(1, 'N/A', 'admin@sksu.edu.ph', '0922', '0000', 'November', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `Num` int(11) NOT NULL,
  `GmailAccount` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`Num`, `GmailAccount`, `password`) VALUES
(1, 'admin', '0922');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `Num` int(11) NOT NULL,
  `IDNumber` varchar(20) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Course` varchar(50) NOT NULL,
  `Yr` int(20) NOT NULL,
  `Section` varchar(20) NOT NULL,
  `Appointment_Date` date NOT NULL,
  `Appointment_Time` time NOT NULL,
  `Reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `Num` int(11) NOT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `event_location` varchar(50) NOT NULL,
  `event_details` varchar(500) NOT NULL,
  `status` enum('pending','sent') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `Num` int(11) NOT NULL,
  `IDNumber` varchar(50) NOT NULL,
  `Rank` varchar(20) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `GmailAccount` varchar(100) NOT NULL,
  `Department` varchar(50) NOT NULL,
  `Position` varchar(50) NOT NULL,
  `Complains` text NOT NULL,
  `MedName` varchar(255) NOT NULL,
  `Temperature` decimal(5,0) NOT NULL,
  `BloodPressure` varchar(20) NOT NULL,
  `HeartRate` int(20) NOT NULL,
  `RespiratoryRate` int(250) NOT NULL,
  `Height` int(100) NOT NULL,
  `Weight` int(100) NOT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL,
  `years` year(4) DEFAULT year(curdate()),
  `AppointmentDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `months` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `alert_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `faculty`
--
DELIMITER $$
CREATE TRIGGER `before_insert_faculty` BEFORE INSERT ON `faculty` FOR EACH ROW BEGIN
    SET NEW.months = DATE_FORMAT(CURRENT_TIMESTAMP(), '%M');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_accounts`
--

CREATE TABLE `faculty_accounts` (
  `Num` int(11) NOT NULL,
  `IDNumber` varchar(50) NOT NULL,
  `GmailAccount` varchar(50) NOT NULL,
  `Password` varchar(50) NOT NULL,
  `years` year(4) NOT NULL DEFAULT current_timestamp(),
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_appointments`
--

CREATE TABLE `faculty_appointments` (
  `Num` int(11) NOT NULL,
  `IDNumber` int(10) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Department` varchar(100) NOT NULL,
  `Position` varchar(50) NOT NULL,
  `Appointment_Date` date NOT NULL,
  `Appointment_Time` time NOT NULL,
  `Reason` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_resources`
--

CREATE TABLE `health_resources` (
  `Num` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `resource_type` enum('article','video','quiz') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `illmed`
--

CREATE TABLE `illmed` (
  `Num` int(11) NOT NULL,
  `IDNumber` int(20) NOT NULL,
  `IllName` varchar(255) NOT NULL,
  `MedName` varchar(255) NOT NULL,
  `Temperature` decimal(5,2) NOT NULL,
  `BloodPressure` varchar(10) NOT NULL,
  `Status` varchar(50) DEFAULT 'Pending',
  `Appointment_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `years` year(4) DEFAULT year(curdate()),
  `alert_sent` tinyint(1) DEFAULT 0,
  `Prescription` text NOT NULL,
  `months` varchar(20) DEFAULT monthname(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intv`
--

CREATE TABLE `intv` (
  `Num` int(11) NOT NULL,
  `IDNumber` int(20) NOT NULL,
  `what_you_do` varchar(50) NOT NULL,
  `what_is_your_existing_desease` varchar(50) DEFAULT NULL,
  `have_you_a_family_history_desease` varchar(50) DEFAULT NULL,
  `have_you_a_allergy` varchar(50) DEFAULT NULL,
  `years` year(4) DEFAULT year(curdate()),
  `months` varchar(20) DEFAULT monthname(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `Num` int(11) NOT NULL,
  `MedName` varchar(255) NOT NULL,
  `SupplierName` varchar(255) DEFAULT NULL,
  `StockQuantity` int(11) DEFAULT NULL,
  `months` varchar(20) DEFAULT monthname(curdate()),
  `years` int(11) DEFAULT year(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`Num`, `MedName`, `SupplierName`, `StockQuantity`, `months`, `years`) VALUES
(6, 'Antacid', 'SKSU MAIN CAMPUS', 25, 'November', 2024),
(5, 'Ceterizine', 'SKSU MAIN CAMPUS', 30, 'November', 2024),
(2, 'Ibuprofen', 'SKSU MAIN CAMPUS', 45, 'November', 2024),
(4, 'Lagundi', 'SKSU MAIN CAMPUS', 35, 'November', 2024),
(7, 'Loperamide', 'SKSU MAIN CAMPUS', 20, 'November', 2024),
(3, 'Mefenamic', 'SKSU MAIN CAMPUS', 40, 'November', 2024),
(1, 'Paracetamol', 'SKSU MAIN CAMPUS', 14, 'November', 2024),
(8, 'Warm Compress', 'SKSU MAIN CAMPUS', 15, 'November', 2024);

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `Num` int(11) NOT NULL,
  `height` decimal(5,0) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `heartrate` int(11) NOT NULL,
  `bloodpressure` varchar(7) NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `years` int(11) NOT NULL,
  `months` varchar(20) DEFAULT monthname(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nurse_schedule`
--

CREATE TABLE `nurse_schedule` (
  `Num` int(11) NOT NULL,
  `reason` text NOT NULL,
  `unavailable_date` date DEFAULT NULL,
  `unavailable_end_time` time DEFAULT NULL,
  `unavailable_start_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_info`
--

CREATE TABLE `personal_info` (
  `Num` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `IDNumber` int(20) NOT NULL,
  `GmailAccount` varchar(50) NOT NULL,
  `Birthdate` date NOT NULL,
  `Age` int(2) NOT NULL,
  `Gender` varchar(20) NOT NULL,
  `Course` varchar(50) NOT NULL,
  `Yr` int(5) NOT NULL,
  `Section` varchar(20) NOT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL,
  `years` year(4) DEFAULT year(curdate()),
  `months` varchar(20) DEFAULT monthname(curdate()),
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_info_accounts`
--

CREATE TABLE `personal_info_accounts` (
  `Num` int(11) NOT NULL,
  `IDNumber` int(20) NOT NULL,
  `GmailAccount` varchar(50) NOT NULL,
  `Password` varchar(50) NOT NULL,
  `years` year(4) NOT NULL DEFAULT current_timestamp(),
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `symptom_analysis`
--

CREATE TABLE `symptom_analysis` (
  `Num` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `symptoms` text NOT NULL,
  `suggestions` text NOT NULL,
  `medications` text NOT NULL,
  `analysis_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`Num`) USING BTREE;

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`Num`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`Num`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`Num`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`Num`),
  ADD KEY `Num` (`Num`);

--
-- Indexes for table `faculty_accounts`
--
ALTER TABLE `faculty_accounts`
  ADD PRIMARY KEY (`Num`);

--
-- Indexes for table `faculty_appointments`
--
ALTER TABLE `faculty_appointments`
  ADD PRIMARY KEY (`Num`);

--
-- Indexes for table `health_resources`
--
ALTER TABLE `health_resources`
  ADD PRIMARY KEY (`Num`);

--
-- Indexes for table `illmed`
--
ALTER TABLE `illmed`
  ADD PRIMARY KEY (`Num`) USING BTREE;

--
-- Indexes for table `intv`
--
ALTER TABLE `intv`
  ADD PRIMARY KEY (`Num`) USING BTREE;

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`MedName`),
  ADD UNIQUE KEY `Num` (`Num`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`Num`),
  ADD KEY `Num_2` (`Num`);

--
-- Indexes for table `nurse_schedule`
--
ALTER TABLE `nurse_schedule`
  ADD PRIMARY KEY (`Num`);

--
-- Indexes for table `personal_info`
--
ALTER TABLE `personal_info`
  ADD PRIMARY KEY (`Num`) USING BTREE,
  ADD UNIQUE KEY `GmailAccount` (`GmailAccount`);

--
-- Indexes for table `personal_info_accounts`
--
ALTER TABLE `personal_info_accounts`
  ADD PRIMARY KEY (`Num`);

--
-- Indexes for table `symptom_analysis`
--
ALTER TABLE `symptom_analysis`
  ADD PRIMARY KEY (`Num`),
  ADD UNIQUE KEY `Num` (`Num`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=531;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT for table `faculty_accounts`
--
ALTER TABLE `faculty_accounts`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `faculty_appointments`
--
ALTER TABLE `faculty_appointments`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `health_resources`
--
ALTER TABLE `health_resources`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `illmed`
--
ALTER TABLE `illmed`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=531;

--
-- AUTO_INCREMENT for table `intv`
--
ALTER TABLE `intv`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `nurse_schedule`
--
ALTER TABLE `nurse_schedule`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `personal_info`
--
ALTER TABLE `personal_info`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `personal_info_accounts`
--
ALTER TABLE `personal_info_accounts`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `symptom_analysis`
--
ALTER TABLE `symptom_analysis`
  MODIFY `Num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
