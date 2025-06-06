-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 07:47 AM
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
-- Database: `barberbook`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `AppointmentID` int(11) NOT NULL,
  `StartTime` datetime NOT NULL,
  `EndTime` datetime NOT NULL,
  `Status` varchar(20) NOT NULL,
  `PaymentID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `BarberID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `StartTime`, `EndTime`, `Status`, `PaymentID`, `CustomerID`, `BarberID`) VALUES
(1, '2025-04-24 10:00:00', '2025-04-24 10:30:00', 'Confirmed', 1, 100, 0),
(2, '2025-04-24 11:00:00', '2025-04-24 11:45:00', 'Confirmed', 2, 101, 0),
(3, '2025-04-24 14:00:00', '2025-04-24 14:40:00', 'Confirmed', 3, 102, 0),
(4, '2025-04-25 09:30:00', '2025-04-25 10:00:00', 'Confirmed', 4, 103, 0),
(5, '2025-04-25 13:00:00', '2025-04-25 13:25:00', 'Confirmed', 5, 104, 0),
(6, '2025-04-26 15:00:00', '2025-04-26 16:00:00', 'Confirmed', 6, 105, 0),
(7, '2025-04-26 16:30:00', '2025-04-26 17:15:00', 'Confirmed', 7, 106, 0),
(8, '2025-04-27 10:00:00', '2025-04-27 10:20:00', 'Confirmed', 8, 107, 0),
(9, '2025-04-27 11:30:00', '2025-04-27 12:15:00', 'Confirmed', 9, 108, 0),
(10, '2025-04-28 14:00:00', '2025-04-28 14:30:00', 'Confirmed', 10, 109, 0),
(11, '2025-04-28 16:00:00', '2025-04-28 16:45:00', 'Confirmed', 11, 110, 0),
(12, '2025-04-29 09:00:00', '2025-04-29 09:20:00', 'Confirmed', 12, 111, 0),
(13, '2025-04-29 13:00:00', '2025-04-29 14:00:00', 'Pending', 13, 112, 0),
(14, '2025-04-30 10:30:00', '2025-04-30 10:50:00', 'Pending', 14, 113, 0),
(15, '2025-04-30 15:00:00', '2025-04-30 15:30:00', 'Pending', 15, 114, 0),
(16, '2025-05-01 11:00:00', '2025-05-01 11:45:00', 'Pending', 16, 115, 0),
(17, '2025-05-01 14:00:00', '2025-05-01 14:20:00', 'Confirmed', 17, 116, 0),
(18, '2025-05-02 09:30:00', '2025-05-02 09:50:00', 'Confirmed', 18, 117, 0),
(19, '2025-05-02 13:00:00', '2025-05-02 13:25:00', 'Confirmed', 19, 118, 0),
(20, '2025-05-03 16:00:00', '2025-05-03 16:30:00', 'Confirmed', 20, 119, 0),
(21, '2025-05-02 17:00:00', '2025-05-02 17:45:00', 'scheduled', 21, 120, 0),
(22, '2025-05-04 10:30:00', '2025-05-04 11:30:00', 'scheduled', 22, 121, 0),
(23, '2025-05-05 12:30:00', '2025-05-05 12:50:00', 'scheduled', 23, 121, 0),
(24, '2025-05-07 09:00:00', '2025-05-07 10:00:00', 'scheduled', 24, 121, 0),
(25, '2025-05-07 09:00:00', '2025-05-07 09:30:00', 'Scheduled', 25, 120, 0),
(26, '2025-05-07 10:00:00', '2025-05-07 10:20:00', 'Scheduled', 25, 120, 0),
(27, '2025-05-07 10:00:00', '2025-05-07 10:20:00', 'Scheduled', 26, 120, 0),
(28, '2025-05-07 12:00:00', '2025-05-07 12:20:00', 'Scheduled', 26, 120, 0),
(29, '2025-05-07 11:00:00', '2025-05-07 12:10:00', 'Scheduled', 27, 120, 0),
(30, '2025-05-07 09:00:00', '2025-05-07 10:10:00', 'Scheduled', 28, 120, 0),
(31, '2025-05-07 13:00:00', '2025-05-07 13:20:00', 'Cancelled', 29, 120, 0),
(32, '2025-05-07 13:00:00', '2025-05-07 13:20:00', 'Cancelled', 30, 120, 0),
(33, '2025-05-07 14:00:00', '2025-05-07 14:30:00', 'Cancelled', 30, 120, 0),
(34, '2025-05-07 15:00:00', '2025-05-07 15:20:00', 'Cancelled', 30, 120, 0),
(35, '2025-05-07 14:00:00', '2025-05-07 14:45:00', 'Cancelled', 30, 120, 0),
(36, '2025-05-07 15:00:00', '2025-05-07 15:25:00', 'Cancelled', 30, 120, 0),
(37, '2025-05-07 11:00:00', '2025-05-07 11:30:00', 'Scheduled', 31, 120, 0),
(38, '2025-05-07 12:00:00', '2025-05-07 12:20:00', 'Scheduled', 31, 120, 0),
(39, '2025-05-07 13:00:00', '2025-05-07 13:30:00', 'Scheduled', 32, 121, 0),
(40, '2025-05-07 14:00:00', '2025-05-07 14:20:00', 'Scheduled', 32, 121, 0),
(41, '2025-05-09 10:00:00', '2025-05-09 10:30:00', 'Scheduled', 33, 121, 0),
(42, '2025-05-09 10:00:00', '2025-05-09 10:30:00', 'Scheduled', 34, 121, 0),
(43, '2025-05-09 11:00:00', '2025-05-09 12:00:00', 'Scheduled', 35, 121, 0),
(44, '2025-05-13 12:00:00', '2025-05-13 13:00:00', 'cancelled', 36, 120, 0),
(45, '2025-05-13 12:00:00', '2025-05-13 13:00:00', 'Cancelled', 37, 121, 0),
(46, '2025-05-13 13:00:00', '2025-05-13 14:00:00', 'Cancelled', 38, 121, 0),
(47, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 39, 121, 0),
(48, '2025-05-13 15:00:00', '2025-05-13 16:00:00', 'Cancelled', 40, 121, 0),
(49, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Cancelled', 41, 121, 0),
(50, '2025-05-13 16:00:00', '2025-05-13 17:00:00', 'cancelled', 42, 120, 0),
(51, '2025-05-14 15:00:00', '2025-05-14 16:00:00', 'Cancelled', 43, 120, 0),
(52, '2025-05-14 12:00:00', '2025-05-14 13:00:00', 'Cancelled', 43, 120, 0),
(53, '2025-05-14 11:00:00', '2025-05-14 12:00:00', 'Cancelled', 44, 120, 0),
(54, '2025-05-14 14:00:00', '2025-05-14 15:00:00', 'Cancelled', 45, 120, 0),
(55, '2025-05-15 13:00:00', '2025-05-15 14:00:00', 'Cancelled', 46, 120, 0),
(56, '2025-05-15 14:00:00', '2025-05-15 15:00:00', 'Cancelled', 47, 120, 0),
(57, '2025-05-15 12:00:00', '2025-05-15 13:00:00', 'Cancelled', 48, 120, 0),
(58, '2025-05-15 15:00:00', '2025-05-15 16:00:00', 'Cancelled', 49, 120, 8),
(59, '2025-05-14 13:00:00', '2025-05-14 14:00:00', 'Cancelled', 50, 120, 7),
(60, '2025-05-14 14:00:00', '2025-05-14 15:00:00', 'Cancelled', 50, 120, 3),
(61, '2025-05-14 15:00:00', '2025-05-14 16:00:00', 'Cancelled', 50, 120, 12),
(62, '2025-05-14 16:00:00', '2025-05-14 17:00:00', 'Cancelled', 50, 120, 1),
(63, '2025-05-14 17:00:00', '2025-05-14 18:00:00', 'Cancelled', 50, 120, 8),
(64, '2025-05-14 16:00:00', '2025-05-14 17:00:00', 'Cancelled', 51, 122, 4),
(65, '2025-05-14 11:00:00', '2025-05-14 12:00:00', 'Cancelled', 52, 121, 12),
(66, '2025-05-14 12:00:00', '2025-05-14 13:00:00', 'Cancelled', 52, 121, 12),
(67, '2025-05-14 13:00:00', '2025-05-14 14:00:00', 'Cancelled', 52, 121, 12),
(68, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 53, 122, 3),
(69, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 54, 122, 7),
(70, '2025-05-11 15:00:00', '2025-05-11 16:00:00', 'Cancelled', 55, 122, 3),
(71, '2025-05-11 15:00:00', '2025-05-11 16:00:00', 'Cancelled', 55, 122, 7),
(72, '2025-05-11 16:00:00', '2025-05-11 17:00:00', 'Scheduled', 56, 122, 3),
(73, '2025-05-11 16:00:00', '2025-05-11 17:00:00', 'Scheduled', 56, 122, 7),
(74, '2025-05-11 13:00:00', '2025-05-11 14:00:00', 'Cancelled', 57, 122, 12),
(75, '2025-05-11 13:00:00', '2025-05-11 14:00:00', 'Cancelled', 57, 122, 3),
(77, '2025-05-11 11:00:00', '2025-05-11 12:00:00', 'Cancelled', 59, 122, 1),
(78, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Scheduled', 62, 122, 3),
(81, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Cancelled', 65, 123, 12),
(82, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 66, 123, 7),
(83, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 69, 123, 1),
(84, '2025-05-14 13:00:00', '2025-05-14 14:00:00', 'Cancelled', 77, 123, 12),
(86, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 93, 123, 7),
(87, '2025-05-14 15:00:00', '2025-05-14 16:00:00', 'Cancelled', 98, 123, 12),
(88, '2025-05-13 13:00:00', '2025-05-13 14:00:00', 'Cancelled', 104, 123, 12),
(89, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 106, 123, 12),
(90, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Cancelled', 110, 123, 12),
(91, '2025-05-12 14:00:00', '2025-05-12 15:00:00', 'Cancelled', 111, 123, 12),
(92, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 112, 123, 12),
(114, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 141, 123, 2),
(115, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 141, 123, 2),
(116, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 148, 123, 0),
(117, '2025-05-12 18:00:00', '2025-05-12 19:00:00', 'Cancelled', 148, 123, 0),
(118, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 149, 123, 0),
(119, '2025-05-12 18:00:00', '2025-05-12 19:00:00', 'Cancelled', 150, 123, 0),
(120, '2025-05-12 14:00:00', '2025-05-12 15:00:00', 'Cancelled', 151, 123, 3),
(121, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 152, 123, 8),
(122, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 152, 123, 5),
(123, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Cancelled', 153, 123, 7),
(124, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Cancelled', 154, 123, 2),
(125, '2025-05-13 12:00:00', '2025-05-13 13:00:00', 'Cancelled', 155, 123, 7),
(126, '2025-05-13 13:00:00', '2025-05-13 14:00:00', 'Cancelled', 155, 123, 7),
(127, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Cancelled', 155, 123, 7),
(128, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 156, 123, 2),
(129, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 156, 123, 7),
(130, '2025-05-12 11:00:00', '2025-05-12 12:00:00', 'Cancelled', 157, 123, 7),
(131, '2025-05-12 11:00:00', '2025-05-12 12:00:00', 'Cancelled', 157, 123, 3),
(132, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 158, 123, 7),
(133, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 158, 123, 7),
(134, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 159, 123, 2),
(135, '2025-05-12 12:00:00', '2025-05-12 13:00:00', 'Cancelled', 160, 123, 4),
(136, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 161, 123, 2),
(137, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 161, 123, 7),
(138, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 162, 123, 2),
(139, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 163, 123, 7),
(140, '2025-05-12 14:00:00', '2025-05-12 15:00:00', 'Cancelled', 164, 123, 12),
(141, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 165, 123, 7),
(142, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 166, 123, 3),
(143, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 166, 123, 3),
(144, '2025-05-13 13:00:00', '2025-05-13 14:00:00', 'Cancelled', 167, 123, 7),
(145, '2025-05-13 15:00:00', '2025-05-13 16:00:00', 'Cancelled', 168, 123, 7),
(146, '2025-05-12 13:00:00', '2025-05-12 14:00:00', 'Cancelled', 169, 123, 2),
(147, '2025-05-12 14:00:00', '2025-05-12 15:00:00', 'Cancelled', 169, 123, 7),
(148, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 169, 123, 7),
(149, '2025-05-12 14:00:00', '2025-05-12 15:00:00', 'Cancelled', 170, 123, 7),
(150, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 170, 123, 7),
(151, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 170, 123, 7),
(152, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 171, 123, 4),
(153, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 172, 123, 4),
(154, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 173, 123, 4),
(155, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 174, 123, 2),
(156, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 174, 123, 2),
(157, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 174, 123, 2),
(158, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 175, 123, 2),
(159, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 176, 123, 2),
(160, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 177, 123, 2),
(161, '2025-05-12 14:00:00', '2025-05-12 15:00:00', 'Cancelled', 178, 123, 2),
(162, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 179, 123, 7),
(163, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 180, 123, 2),
(164, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Cancelled', 181, 123, 7),
(165, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 182, 123, 7),
(166, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 183, 123, 7),
(167, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 184, 123, 7),
(168, '2025-05-12 15:00:00', '2025-05-12 16:00:00', 'Cancelled', 185, 123, 7),
(169, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 186, 123, 4),
(170, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 187, 123, 4),
(171, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 188, 123, 2),
(172, '2025-05-12 18:00:00', '2025-05-12 19:00:00', 'Cancelled', 189, 123, 7),
(173, '2025-05-13 12:00:00', '2025-05-13 13:00:00', 'Cancelled', 190, 123, 12),
(174, '2025-05-13 13:00:00', '2025-05-13 14:00:00', 'Cancelled', 191, 123, 12),
(175, '2025-05-15 14:00:00', '2025-05-15 15:00:00', 'Cancelled', 192, 123, 12),
(176, '2025-05-12 20:00:00', '2025-05-12 21:00:00', 'Cancelled', 193, 123, 12),
(177, '2025-05-12 16:00:00', '2025-05-12 17:00:00', 'Cancelled', 194, 123, 12),
(178, '2025-05-14 17:00:00', '2025-05-14 18:00:00', 'Cancelled', 195, 123, 12),
(179, '2025-05-12 20:00:00', '2025-05-12 21:00:00', 'Cancelled', 196, 123, 12),
(180, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Completed', 197, 123, 12),
(181, '2025-05-12 19:00:00', '2025-05-12 20:00:00', 'Completed', 198, 123, 12),
(182, '2025-05-12 20:00:00', '2025-05-12 21:00:00', 'Cancelled', 199, 123, 12),
(183, '2025-05-12 20:00:00', '2025-05-12 21:00:00', 'Cancelled', 200, 123, 12),
(184, '2025-05-12 19:00:00', '2025-05-12 20:00:00', 'Cancelled', 201, 123, 12),
(185, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 202, 123, 12),
(186, '2025-05-12 18:00:00', '2025-05-12 19:00:00', 'Cancelled', 203, 123, 12),
(187, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Completed', 204, 123, 12),
(188, '2025-05-12 18:00:00', '2025-05-12 19:00:00', 'Completed', 205, 123, 12),
(189, '2025-05-12 19:00:00', '2025-05-12 20:00:00', 'Completed', 206, 123, 12),
(190, '2025-05-12 19:00:00', '2025-05-12 20:00:00', 'Completed', 207, 123, 12),
(191, '2025-05-12 18:00:00', '2025-05-12 19:00:00', 'Cancelled', 208, 123, 12),
(192, '2025-05-12 17:00:00', '2025-05-12 18:00:00', 'Cancelled', 209, 123, 12),
(193, '2025-05-13 14:00:00', '2025-05-13 15:00:00', 'Completed', 210, 123, 12),
(194, '2025-05-14 15:00:00', '2025-05-14 16:00:00', 'Cancelled', 211, 123, 12),
(195, '2025-05-13 18:00:00', '2025-05-13 19:00:00', 'Cancelled', 212, 123, 12),
(196, '2025-05-13 17:00:00', '2025-05-13 18:00:00', 'Cancelled', 213, 123, 12),
(197, '2025-05-13 16:00:00', '2025-05-13 17:00:00', 'Cancelled', 214, 123, 12),
(198, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 215, 123, 12),
(199, '2025-05-13 17:00:00', '2025-05-13 18:00:00', 'Cancelled', 216, 123, 12),
(200, '2025-05-13 18:00:00', '2025-05-13 19:00:00', 'Completed', 217, 123, 12),
(201, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 218, 123, 12),
(202, '2025-05-13 18:00:00', '2025-05-13 19:00:00', 'Completed', 219, 123, 12),
(203, '2025-05-13 19:00:00', '2025-05-13 20:00:00', 'Cancelled', 220, 123, 12),
(204, '2025-05-13 19:00:00', '2025-05-13 20:00:00', 'Cancelled', 221, 123, 12),
(205, '2025-05-13 18:00:00', '2025-05-13 19:00:00', 'Cancelled', 222, 123, 12),
(206, '2025-05-13 18:00:00', '2025-05-13 19:00:00', 'Cancelled', 223, 123, 12),
(207, '2025-05-13 19:00:00', '2025-05-13 20:00:00', 'Cancelled', 224, 123, 12),
(213, '2025-05-13 18:00:00', '2025-05-13 19:00:00', 'Cancelled', 230, 123, 12),
(214, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 231, 123, 12),
(215, '2025-05-13 18:00:00', '2025-05-13 19:00:00', 'Completed', 232, 123, 12),
(216, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 233, 123, 12),
(217, '2025-05-13 19:00:00', '2025-05-13 20:00:00', 'Cancelled', 234, 123, 12),
(218, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 235, 123, 12),
(219, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 236, 123, 12),
(220, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 237, 123, 12),
(221, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 238, 123, 12),
(222, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 239, 123, 12),
(223, '2025-05-13 20:00:00', '2025-05-13 21:00:00', 'Cancelled', 240, 123, 12),
(224, '2025-05-13 19:00:00', '2025-05-13 20:00:00', 'Scheduled', 241, 123, 12),
(225, '2025-05-13 18:00:00', '2025-05-13 19:00:00', 'Scheduled', 242, 123, 12),
(226, '2025-05-14 14:00:00', '2025-05-14 15:00:00', 'Cancelled', 243, 123, 12),
(227, '2025-05-14 15:00:00', '2025-05-14 16:00:00', 'Cancelled', 244, 123, 12),
(228, '2025-05-14 18:00:00', '2025-05-14 19:00:00', 'Cancelled', 245, 123, 12),
(229, '2025-05-14 19:00:00', '2025-05-14 20:00:00', 'Cancelled', 246, 123, 12),
(230, '2025-05-14 17:00:00', '2025-05-14 18:00:00', 'Cancelled', 247, 123, 12),
(231, '2025-05-14 18:00:00', '2025-05-14 19:00:00', 'Completed', 248, 123, 12),
(232, '2025-05-14 20:00:00', '2025-05-14 21:00:00', 'Completed', 249, 123, 12),
(233, '2025-05-14 20:00:00', '2025-05-14 21:00:00', 'Completed', 250, 123, 12);

-- --------------------------------------------------------

--
-- Table structure for table `apptcontains`
--

CREATE TABLE `apptcontains` (
  `ServiceID` int(11) NOT NULL,
  `AppointmentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apptcontains`
--

INSERT INTO `apptcontains` (`ServiceID`, `AppointmentID`) VALUES
(1, 1),
(2, 2),
(1, 3),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(2, 9),
(3, 9),
(1, 10),
(2, 11),
(3, 12),
(6, 13),
(4, 14),
(9, 15),
(2, 16),
(9, 17),
(4, 18),
(5, 19),
(10, 20),
(2, 21),
(3, 22),
(8, 22),
(9, 22),
(3, 23),
(3, 24),
(8, 24),
(9, 24),
(1, 25),
(3, 26),
(3, 27),
(4, 28),
(1, 29),
(3, 29),
(4, 29),
(1, 30),
(3, 30),
(4, 30),
(3, 31),
(3, 32),
(1, 33),
(4, 34),
(2, 35),
(5, 36),
(1, 37),
(3, 38),
(1, 39),
(3, 40),
(1, 41),
(1, 42),
(6, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(3, 49),
(1, 50),
(1, 51),
(1, 52),
(1, 53),
(1, 54),
(1, 55),
(1, 56),
(1, 57),
(1, 58),
(1, 59),
(1, 60),
(1, 61),
(4, 62),
(3, 63),
(1, 64),
(4, 65),
(1, 66),
(3, 67),
(1, 68),
(3, 69),
(1, 70),
(4, 71),
(1, 72),
(3, 73),
(3, 74),
(1, 75),
(4, 77),
(1, 78),
(4, 81),
(3, 82),
(1, 83),
(1, 84),
(1, 86),
(4, 87),
(1, 88),
(3, 89),
(1, 90),
(3, 91),
(4, 92),
(1, 114),
(4, 115),
(1, 116),
(1, 117),
(1, 118),
(1, 119),
(1, 120),
(3, 121),
(1, 122),
(1, 123),
(1, 124),
(3, 125),
(1, 126),
(4, 127),
(1, 128),
(3, 129),
(1, 130),
(4, 131),
(1, 132),
(5, 133),
(9, 134),
(4, 135),
(1, 136),
(4, 137),
(1, 138),
(4, 139),
(3, 140),
(3, 141),
(3, 142),
(3, 143),
(1, 144),
(1, 145),
(1, 146),
(1, 147),
(3, 148),
(3, 149),
(10, 150),
(2, 151),
(1, 152),
(1, 153),
(6, 154),
(1, 155),
(6, 156),
(10, 157),
(1, 158),
(6, 159),
(10, 160),
(1, 161),
(1, 162),
(9, 163),
(3, 164),
(1, 165),
(1, 166),
(3, 167),
(2, 168),
(4, 169),
(1, 170),
(1, 171),
(3, 172),
(1, 173),
(4, 174),
(1, 175),
(1, 176),
(1, 177),
(1, 178),
(4, 179),
(1, 180),
(1, 181),
(3, 182),
(3, 183),
(1, 184),
(1, 185),
(4, 186),
(10, 187),
(6, 188),
(1, 189),
(1, 190),
(1, 191),
(1, 192),
(1, 193),
(6, 194),
(1, 195),
(1, 196),
(1, 197),
(6, 198),
(1, 199),
(1, 200),
(10, 201),
(1, 202),
(1, 203),
(8, 204),
(3, 205),
(1, 206),
(1, 207),
(1, 213),
(1, 214),
(1, 215),
(1, 216),
(1, 217),
(1, 218),
(1, 219),
(3, 220),
(4, 221),
(3, 222),
(1, 223),
(1, 224),
(1, 225),
(1, 226),
(3, 227),
(1, 228),
(3, 229),
(1, 230),
(3, 231),
(3, 232),
(3, 233);

-- --------------------------------------------------------

--
-- Table structure for table `barberhas`
--

CREATE TABLE `barberhas` (
  `BarberID` int(11) NOT NULL,
  `AppointmentID` int(11) NOT NULL,
  `bhid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barberhas`
--

INSERT INTO `barberhas` (`BarberID`, `AppointmentID`, `bhid`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 5),
(6, 6, 6),
(7, 7, 7),
(8, 8, 8),
(9, 9, 9),
(10, 10, 10),
(1, 11, 11),
(2, 12, 12),
(3, 13, 13),
(4, 14, 14),
(5, 15, 15),
(6, 16, 16),
(7, 17, 17),
(8, 18, 18),
(9, 19, 19),
(10, 20, 20),
(5, 21, 21),
(11, 22, 22),
(1, 23, 23),
(11, 24, 24),
(9, 25, 25),
(11, 26, 26),
(4, 27, 27),
(7, 28, 28),
(4, 29, 29),
(4, 30, 30),
(4, 31, 31),
(2, 32, 32),
(2, 33, 33),
(2, 34, 34),
(7, 35, 35),
(7, 36, 36),
(11, 37, 37),
(11, 38, 38),
(11, 39, 39),
(8, 40, 40),
(9, 41, 41),
(11, 42, 42),
(4, 43, 43),
(9, 44, 44),
(4, 45, 45),
(9, 46, 46),
(12, 47, 47),
(12, 48, 48),
(7, 49, 49),
(12, 50, 50),
(3, 51, 51),
(1, 52, 52),
(1, 53, 53),
(12, 54, 54),
(2, 55, 55),
(2, 56, 56),
(12, 57, 57),
(8, 58, 58),
(7, 59, 59),
(3, 60, 60),
(12, 61, 61),
(1, 62, 62),
(8, 63, 63),
(4, 64, 64),
(12, 65, 65),
(12, 66, 66),
(12, 67, 67),
(3, 68, 68),
(7, 69, 69),
(3, 70, 70),
(7, 71, 71),
(3, 72, 72),
(7, 73, 73),
(12, 74, 74),
(3, 75, 75),
(1, 77, 76),
(3, 78, 77),
(12, 81, 78),
(7, 82, 79),
(1, 83, 80),
(12, 84, 81),
(7, 86, 82),
(12, 87, 83),
(12, 88, 84),
(12, 89, 85),
(12, 90, 86),
(12, 91, 87),
(12, 92, 88),
(2, 114, 89),
(2, 115, 90),
(12, 116, 91),
(12, 117, 92),
(2, 118, 93),
(2, 119, 94),
(3, 120, 95),
(8, 121, 96),
(5, 122, 97),
(7, 123, 98),
(2, 124, 99),
(7, 125, 100),
(7, 126, 101),
(7, 127, 102),
(2, 128, 103),
(7, 129, 104),
(7, 130, 105),
(3, 131, 106),
(7, 132, 107),
(7, 133, 108),
(2, 134, 109),
(4, 135, 110),
(2, 136, 111),
(7, 137, 112),
(2, 138, 113),
(7, 139, 114),
(12, 140, 115),
(7, 141, 116),
(3, 142, 117),
(3, 143, 118),
(7, 144, 119),
(7, 145, 120),
(2, 146, 121),
(7, 147, 122),
(7, 148, 123),
(7, 149, 124),
(7, 150, 125),
(7, 151, 126),
(4, 152, 127),
(4, 153, 128),
(4, 154, 129),
(2, 155, 130),
(2, 156, 131),
(2, 157, 132),
(2, 158, 133),
(2, 159, 134),
(2, 160, 135),
(2, 161, 136),
(7, 162, 137),
(2, 163, 138),
(7, 164, 139),
(7, 165, 140),
(7, 166, 141),
(7, 167, 142),
(7, 168, 143),
(4, 169, 144),
(4, 170, 145),
(2, 171, 146),
(7, 172, 147),
(12, 173, 148),
(12, 174, 149),
(12, 175, 150),
(12, 176, 151),
(12, 177, 152),
(12, 178, 153),
(12, 179, 154),
(12, 180, 155),
(12, 181, 156),
(12, 182, 157),
(12, 183, 158),
(12, 184, 159),
(12, 185, 160),
(12, 186, 161),
(12, 187, 162),
(12, 188, 163),
(12, 189, 164),
(12, 190, 165),
(12, 191, 166),
(12, 192, 167),
(12, 193, 168),
(12, 194, 169),
(12, 195, 170),
(12, 196, 171),
(12, 197, 179),
(12, 198, 180),
(12, 199, 181),
(12, 200, 182),
(12, 201, 183),
(12, 202, 184),
(12, 203, 185),
(12, 204, 186),
(12, 205, 187),
(12, 206, 188),
(12, 207, 189),
(12, 213, 190),
(12, 214, 191),
(12, 215, 192),
(12, 216, 193),
(12, 217, 194),
(12, 218, 195),
(12, 219, 196),
(12, 220, 197),
(12, 221, 198),
(12, 222, 199),
(12, 223, 200),
(12, 224, 201),
(12, 225, 202),
(12, 226, 203),
(12, 227, 204),
(12, 228, 205),
(12, 229, 206),
(12, 230, 207),
(12, 231, 208),
(12, 232, 209),
(12, 233, 210);

-- --------------------------------------------------------

--
-- Table structure for table `barbers`
--

CREATE TABLE `barbers` (
  `UserID` int(11) NOT NULL,
  `FirstName` varchar(40) NOT NULL,
  `LastName` varchar(40) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(11) NOT NULL,
  `PassHash` varchar(255) NOT NULL,
  `Salary` int(11) NOT NULL,
  `Bio` text DEFAULT NULL,
  `HireDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barbers`
--

INSERT INTO `barbers` (`UserID`, `FirstName`, `LastName`, `Email`, `Phone`, `PassHash`, `Salary`, `Bio`, `HireDate`) VALUES
(1, 'Karim', 'Rahman', 'karim.r@barber.com', '01711223301', '$2y$10$.cOakvhkkKb94t8uZUJtz.znXgbY44AzxKPq4mvRAXielguLrWU7C', 45000, 'Master barber with 10+ years of experience.', '2018-03-15'),
(2, 'Fatima', 'Akter', 'fatima.a@barber.com', '01822334402', '09113d9ff6ec95e5ac6d191983ee0d387b0f6861d781a9d96ffe22ab06489721', 42000, 'Specializing in modern and classic cuts.', '2019-06-22'),
(3, 'Jamal', 'Hossain', 'jamal.h@barber.com', '01933445503', '16351f713fab3aa83077fdd43fe7bc02b9ad7cab80718f2aa691f4b0bc586240', 40000, 'Expert in beard styling and maintenance.', '2020-01-10'),
(4, 'Ayesha', 'Begum', 'ayesha.b@barber.com', '01644556604', '70ce91636986c65e316dded2a86829f9c4a68dc22ac408c8a0845d2a9ce15f07', 44000, 'Trained in modern techniques with expertise in precision cuts.', '2018-11-05'),
(5, 'Rahim', 'Uddin', 'rahim.u@barber.com', '01555667705', '4dd5f2f0525f9d78ccc3d39156ac6e7e97fd0017c56cc9978d30bc67985fffab', 41000, 'Specializes in textured hair and fades.', '2019-08-18'),
(6, 'Sadia', 'Islam', 'sadia.i@barber.com', '01366778806', '9c8f13e344dcd27277170eccf963299c917847946a12160986cf474bc34fba69', 43000, 'Color specialist with salon background.', '2020-02-28'),
(7, 'Hasan', 'Chowdhury', 'hasan.c@barber.com', '01777889907', '55dfec5d23131d60bcbce8795e0e6d0df5aa0d47ffadd8315b50c70ff79e8125', 39000, 'Traditional barbering with modern techniques.', '2021-04-12'),
(8, 'Nadia', 'Khan', 'nadia.k@barber.com', '01888990008', 'daebd223f543e37eb4ce9f8f9cb75e16a0bcea34993ad73dc9fe76efeb564467', 38000, 'Specializes in kids cuts and family styling.', '2021-09-30'),
(9, 'Ahmed', 'Sheikh', 'ahmed.s@barber.com', '01999001109', 'd8f5d4e4380d5b101d0cc3cf550929803c100a3a58861e81ab4898e43ff83826', 46000, 'Award-winning stylist with diverse clientele.', '2017-07-22'),
(10, 'Zainab', 'Parvin', 'zainab.p@barber.com', '01611223310', 'ce9b8b7025d9f4a7a13c75e33e59674febd914b75b62d0bcc2489364308ff44a', 42000, 'Passionate about creating personalized styles.', '2019-05-15'),
(11, 'Rahim', 'Usman', 'rahim.u@gmail.com', '01111111111', '$2y$10$qMi9NBI9gF.vtgNqqcpUHuSgkomeD5PCL/Tm5ru/LlTk3pT8H.wHS', 0, '', '2025-05-01'),
(12, 'Jocelyn', 'Wilkins', 'barber1@gmail.com', '01555704464', '$2y$10$.cOakvhkkKb94t8uZUJtz.znXgbY44AzxKPq4mvRAXielguLrWU7C', 0, NULL, '2025-05-08');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `UserID` int(11) NOT NULL,
  `FirstName` varchar(40) NOT NULL,
  `LastName` varchar(40) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(11) NOT NULL,
  `PassHash` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `UsedReferralCode` varchar(10) DEFAULT NULL,
  `UniqueReferral` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`UserID`, `FirstName`, `LastName`, `Email`, `Phone`, `PassHash`, `Address`, `UsedReferralCode`, `UniqueReferral`) VALUES
(100, 'Rahim', 'Khan', 'rahim.khan@email.com', '01712345601', '0b14d501a594442a01c6859541bcb3e8164d183d32937b851835442f69d5c94e', 'House 10, Road 5, Block B, Banani, Dhaka 1213', NULL, NULL),
(101, 'Sara', 'Ahmed', 'sara.a@email.com', '01823456702', '6cf615d5bcaac778352a8f1f3360d23f02f34ec182e259897fd6ce485d7870d4', 'Holding 45, Road 12, Sector 6, Uttara, Dhaka 1230', NULL, NULL),
(102, 'Robiul', 'Islam', 'robi.i@email.com', '01934567803', '5906ac361a137e2d286465cd6588ebb5ac3f5ae955001100bc41577c3d751764', 'Flat 3A, House 7, Road 2, Dhanmondi R/A, Dhaka 1205', NULL, NULL),
(103, 'Esha', 'Chowdhury', 'esha.c@email.com', '01645678904', 'b97873a40f73abedd8d685a7cd5e5f85e4a9cfb83eac26886640a0813850122b', 'Plot 15, Lane 3, Block C, Gulshan 1, Dhaka 1212', NULL, NULL),
(104, 'Mohammad', 'Haque', 'mohammad.h@email.com', '01556789005', '8b2c86ea9cf2ea4eb517fd1e06b74f399e7fec0fef92e3b482a6cf2e2b092023', '22/B, Nasirabad H/S, CDA Avenue, Chittagong 4000', NULL, NULL),
(105, 'Jesmin', 'Akhtar', 'jesmin.a@email.com', '01367890106', '598a1a400c1dfdf36974e69d7e1bc98593f2e15015eed8e9b7e47a83b31693d5', '101 Shantinagar Road, Paltan, Dhaka 1217', NULL, NULL),
(106, 'Dawud', 'Ali', 'dawud.ali@email.com', '01778901207', '5860836e8f13fc9837539a597d4086bfc0299e54ad92148d54538b5c3feefb7c', '55 Mirpur Road, Section 10, Mirpur, Dhaka 1216', NULL, NULL),
(107, 'Jannat', 'Begum', 'jannat.b@email.com', '01889012308', '57f3ebab63f156fd8f776ba645a55d96360a15eeffc8b0e4afe4c05fa88219aa', '66 Mohakhali C/A, Gulshan, Dhaka 1212', NULL, NULL),
(108, 'Mehedi', 'Hasan', 'mehedi.h@email.com', '01990123409', '9323dd6786ebcbf3ac87357cc78ba1abfda6cf5e55cd01097b90d4a286cac90e', '77 Agrabad C/A, Double Mooring, Chittagong 4100', NULL, NULL),
(109, 'Afroza', 'Sultana', 'afroza.s@email.com', '01611234510', 'aa4a9ea03fcac15b5fc63c949ac34e7b0fd17906716ac3b8e58c599cdc5a52f0', '88 Zindabazar Road, Kotwali, Sylhet 3100', NULL, NULL),
(110, 'Kamrul', 'Hossain', 'kamrul.h@email.com', '01722345611', '53d453b0c08b6b38ae91515dc88d25fbecdd1d6001f022419629df844f8ba433', 'House 111, Road 8, Baridhara DOHS, Dhaka 1206', NULL, NULL),
(111, 'Asma', 'Parveen', 'asma.p@email.com', '01833456712', 'b3d17ebbe4f2b75d27b6309cfaae1487b667301a73951e7d523a039cd2dfe110', 'Flat C2, Plot 99, Khilgaon, Dhaka 1219', NULL, NULL),
(112, 'Arif', 'Mahmud', 'arif.m@email.com', '01944567813', '48caafb68583936afd0d78a7bfd7046d2492fad94f3c485915f74bb60128620d', 'Holding 222, Station Road, Sadar, Mymensingh 2200', NULL, NULL),
(113, 'Maliha', 'Rahman', 'maliha.r@email.com', '01655678914', 'c6863e1db9b396ed31a36988639513a1c73a065fab83681f4b77adb648fac3d6', '333 Shahi Eidgah Road, Ambarkhana, Sylhet 3100', NULL, NULL),
(114, 'Jahid', 'Miah', 'jahid.m@email.com', '01566789015', 'c63c2d34ebe84032ad47b87af194fedd17dacf8222b2ea7f4ebfee3dd6db2dfb', 'House 33, Sector 11, Road 10, Uttara, Dhaka 1230', NULL, NULL),
(115, 'Nusrat', 'Jahan', 'nusrat.j@email.com', '01377890116', '17a3379984b560dc311bb921b7a46b28aa5cb495667382f887a44a7fdbca7a7a', 'Flat 4B, House 55, Road 15, Banani, Dhaka 1213', NULL, NULL),
(116, 'Anisur', 'Rahman', 'anisur.r@email.com', '01788901217', '69bfb918de05145fba9dcee9688dfb23f6115845885e48fa39945eebb99d8527', '666 Motijheel C/A, Dhaka 1000', NULL, NULL),
(117, 'Sumaiya', 'Khatun', 'sumaiya.k@email.com', '01899012318', 'd2042d75a67922194c045da2600e1c92ff6d87e8fb6e0208606665f2d1dfa892', 'House 77, Road 6, Block D, Bashundhara R/A, Dhaka 1229', NULL, NULL),
(118, 'Farhan', 'Ahmed', 'farhan.a@email.com', '01910123419', '5790ac3d0b8ae8afc72c2c6fb97654f2b73651c328de0a3b74854ade562dd17a', '888 Andarkilla Road, Kotwali, Chittagong 4000', NULL, NULL),
(119, 'Lamia', 'Islam', 'lamia.i@email.com', '01621234520', '7535d8f2d8c35d958995610f971287288ab5e8c82a3c4fdc2b6fb5d757a5b9f8', 'House 88, Road 9A, Dhanmondi R/A, Dhaka 1209', NULL, NULL),
(120, 'test', 'name', 'test@test.com', '01111111111', '$2y$10$vjqzc7zwz.MyUU4jLVLUE.mlSKAoBbamXtX68CpiMcQ8hJuaBE20G', 'sample address', NULL, NULL),
(121, 'Tester', 'Profile', 'test2@gmail.com', '01234567890', '$2y$10$wii76OpJGAsbXqmz0XrJVudy./sZoboy.B6NyIvUxzyPLoSey2vS6', '', NULL, NULL),
(122, 'Sebastian', 'Gill', 'user3@gmail.com', '01759137525', '$2y$10$nkk0UPKEqwmcxXsoUD4Z/.41bn04lUTmLggPjAAiWCHvZD0H2lVea', 'Voluptate nobis minu', NULL, NULL),
(123, 'Anisur', 'Rahman', 'user1@gmail.com', '01111111111', '$2y$10$N6x3/IbTcW2waOK6LSgoROhk9xdwYy/a4CFkJ7vmCp8WNxWGSoyg.', 'Jupiter', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int(11) NOT NULL,
  `RecipientEmail` varchar(100) NOT NULL,
  `SentAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `Status` varchar(20) NOT NULL,
  `Subject` varchar(255) NOT NULL,
  `Body` text NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `AppointmentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`NotificationID`, `RecipientEmail`, `SentAt`, `Status`, `Subject`, `Body`, `CustomerID`, `AppointmentID`) VALUES
(162, 'user1@gmail.com', NULL, 'Pending', 'Test Subject 1', 'Testing!', 123, 194),
(163, '', '2025-05-13 16:33:42', 'pending', 'Appointment Cancelled', 'You have cancelled your appointment scheduled for May 13, 2025 5:00 PM', 123, 199),
(164, 'user1@gmail.com', '2025-05-13 16:39:43', 'Read', 'Appointment Completed', 'Your appointment scheduled for May 13, 2025 at 06:00 PM has been marked as completed by Jocelyn Wilkins', 123, 200),
(165, 'user1@gmail.com', '2025-05-13 16:39:43', 'Read', 'Appointment Completed', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been marked as completed by Jocelyn Wilkins', 123, 201),
(166, 'user1@gmail.com', '2025-05-13 16:39:43', 'Read', 'Appointment Completed', 'Your appointment scheduled for May 13, 2025 at 06:00 PM has been marked as completed by Jocelyn Wilkins', 123, 202),
(167, 'user1@gmail.com', '2025-05-13 16:39:43', 'Read', 'Appointment Cancelled', 'Your appointment scheduled for May 13, 2025 at 07:00 PM has been cancelled by Jocelyn Wilkins', 123, 203),
(168, '', '2025-05-13 16:40:01', 'pending', 'Appointment Cancelled', 'You have cancelled your appointment scheduled for May 13, 2025 6:00 PM', 123, 205),
(169, '', '2025-05-13 16:40:04', 'pending', 'Appointment Cancelled', 'You have cancelled your appointment scheduled for May 13, 2025 7:00 PM', 123, 204),
(171, '', '2025-05-13 16:43:59', 'pending', 'Appointment Cancelled', 'You have cancelled your appointment scheduled for May 13, 2025 6:00 PM', 123, 206),
(172, '', '2025-05-13 16:44:01', 'pending', 'Appointment Cancelled', 'You have cancelled your appointment scheduled for May 13, 2025 7:00 PM', 123, 207),
(177, 'barber1@gmail.com', '2025-05-13 16:49:38', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 06:00 PM with Anisur Rahman', 123, 213),
(178, 'user1@gmail.com', '2025-05-13 16:49:38', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 06:00 PM with Jocelyn Wilkins', 123, 213),
(179, '', '2025-05-13 16:50:05', 'pending', 'Appointment Cancelled', 'You have cancelled your appointment scheduled for May 13, 2025 6:00 PM', 123, 213),
(180, 'barber1@gmail.com', '2025-05-13 16:50:12', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 08:00 PM with Anisur Rahman', 123, 214),
(181, 'user1@gmail.com', '2025-05-13 16:50:12', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 214),
(182, 'user1@gmail.com', NULL, 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for 2025-05-13 20:00:00 has been cancelled by Jocelyn Wilkins', 123, 214),
(183, 'barber1@gmail.com', '2025-05-13 16:53:58', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 06:00 PM with Anisur Rahman', 123, 215),
(184, 'user1@gmail.com', '2025-05-13 16:53:58', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 06:00 PM with Jocelyn Wilkins', 123, 215),
(185, 'barber1@gmail.com', '2025-05-13 16:54:46', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 08:00 PM with Anisur Rahman', 123, 216),
(186, 'user1@gmail.com', '2025-05-13 16:54:46', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 216),
(187, 'user1@gmail.com', '2025-05-13 16:59:12', 'Unread', 'Appointment Completed', 'Your appointment scheduled for May 13, 2025 at 06:00 PM has been marked as completed by Jocelyn Wilkins', 123, 215),
(188, 'barber1@gmail.com', '2025-05-13 17:02:07', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 07:00 PM with Anisur Rahman', 123, 217),
(189, 'user1@gmail.com', '2025-05-13 17:02:07', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 07:00 PM with Jocelyn Wilkins', 123, 217),
(190, 'user1@gmail.com', NULL, 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 13, 2025 at 07:00 PM has been cancelled.', 123, 217),
(191, 'user1@gmail.com', NULL, 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been cancelled.', 123, 201),
(192, 'user1@gmail.com', NULL, 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been cancelled.', 123, 216),
(193, 'barber1@gmail.com', '2025-05-13 17:03:08', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 218),
(194, 'user1@gmail.com', '2025-05-13 17:03:08', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 218),
(195, 'user1@gmail.com', NULL, 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been cancelled by Jocelyn Wilkins', 123, 218),
(196, 'barber1@gmail.com', '2025-05-13 17:04:26', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 219),
(197, 'user1@gmail.com', '2025-05-13 17:04:26', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 219),
(198, 'user1@gmail.com', NULL, 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been cancelled.', 123, 219),
(199, 'barber1@gmail.com', NULL, 'Unread', 'Appointment Cancelled by Customer', 'Your appointment with Anisur Rahman scheduled for May 13, 2025 at 08:00 PM has been cancelled by the customer.', 123, 219),
(200, 'barber1@gmail.com', '2025-05-13 17:04:49', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 220),
(201, 'user1@gmail.com', '2025-05-13 17:04:49', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 220),
(202, 'user1@gmail.com', NULL, 'Unread', 'Appointment Cancelled by Barber', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been cancelled by Jocelyn Wilkins.', 123, 220),
(203, 'barber1@gmail.com', '2025-05-13 17:06:49', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 221),
(204, 'user1@gmail.com', '2025-05-13 17:06:49', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 221),
(207, 'user1@gmail.com', '2025-05-13 17:07:18', 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been cancelled.', 123, 221),
(208, 'barber1@gmail.com', '2025-05-13 17:07:18', 'Unread', 'Appointment Cancelled by Customer', 'Your appointment with Anisur Rahman scheduled for May 13, 2025 at 08:00 PM has been cancelled by the customer.', 123, 221),
(209, 'barber1@gmail.com', '2025-05-13 17:07:31', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 222),
(210, 'user1@gmail.com', '2025-05-13 17:07:31', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 222),
(211, 'user1@gmail.com', '2025-05-13 17:07:40', 'Unread', 'Appointment Cancelled by Barber', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been cancelled by Jocelyn Wilkins.', 123, 222),
(212, 'barber1@gmail.com', '2025-05-13 17:16:45', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 223),
(213, 'user1@gmail.com', '2025-05-13 17:16:45', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 223),
(214, 'barber1@gmail.com', '2025-05-13 17:19:55', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 07:00 PM with Jocelyn Wilkins', 123, 224),
(215, 'user1@gmail.com', '2025-05-13 17:19:55', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 07:00 PM with Jocelyn Wilkins', 123, 224),
(216, 'barber1@gmail.com', '2025-05-13 20:32:40', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 13, 2025 at 06:00 PM with Anisur Rahman', 123, 225),
(217, 'user1@gmail.com', '2025-05-13 20:32:40', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 13, 2025 at 06:00 PM with Jocelyn Wilkins', 123, 225),
(218, 'user1@gmail.com', '2025-05-13 20:32:50', 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 13, 2025 at 08:00 PM has been cancelled.', 123, 223),
(219, 'barber1@gmail.com', '2025-05-13 20:32:50', 'Unread', 'Appointment Cancelled by Customer', 'Your appointment with Anisur Rahman scheduled for May 13, 2025 at 08:00 PM has been cancelled by the customer.', 123, 223),
(220, 'barber1@gmail.com', '2025-05-13 20:36:56', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 14, 2025 at 02:00 PM with Jocelyn Wilkins', 123, 226),
(221, 'user1@gmail.com', '2025-05-13 20:36:56', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 14, 2025 at 02:00 PM with Jocelyn Wilkins', 123, 226),
(222, 'barber1@gmail.com', '2025-05-13 20:37:00', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 14, 2025 at 03:00 PM with Jocelyn Wilkins', 123, 227),
(223, 'user1@gmail.com', '2025-05-13 20:37:00', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 14, 2025 at 03:00 PM with Jocelyn Wilkins', 123, 227),
(224, 'user1@gmail.com', '2025-05-13 20:37:44', 'Unread', 'Appointment Completed', 'Your appointment scheduled for May 14, 2025 at 02:00 PM has been marked as completed by Jocelyn Wilkins', 123, 226),
(225, 'user1@gmail.com', '2025-05-13 20:38:09', 'Unread', 'Appointment Cancelled by Barber', 'Your appointment scheduled for May 14, 2025 at 03:00 PM has been cancelled by Jocelyn Wilkins.', 123, 227),
(226, 'barber1@gmail.com', '2025-05-13 20:52:31', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 14, 2025 at 06:00 PM with Jocelyn Wilkins', 123, 228),
(227, 'user1@gmail.com', '2025-05-13 20:52:31', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 14, 2025 at 06:00 PM with Jocelyn Wilkins', 123, 228),
(228, 'barber1@gmail.com', '2025-05-13 20:52:35', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 14, 2025 at 07:00 PM with Jocelyn Wilkins', 123, 229),
(229, 'user1@gmail.com', '2025-05-13 20:52:35', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 14, 2025 at 07:00 PM with Jocelyn Wilkins', 123, 229),
(230, 'user1@gmail.com', NULL, 'pending', 'Appointment Completed', 'Your appointment scheduled for May 14, 2025 at 06:00 PM has been marked as completed.', 123, 228),
(231, 'user1@gmail.com', NULL, 'pending', 'Appointment Cancelled by Barber', 'Your appointment scheduled for May 14, 2025 at 07:00 PM has been cancelled by the barber.', 123, 229),
(232, 'user1@gmail.com', '2025-05-14 05:17:51', 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 14, 2025 at 02:00 PM has been cancelled.', 123, 226),
(233, 'barber1@gmail.com', '2025-05-14 05:17:51', 'Unread', 'Appointment Cancelled by Customer', 'Your appointment with Anisur Rahman scheduled for May 14, 2025 at 02:00 PM has been cancelled by the customer.', 123, 226),
(234, 'user1@gmail.com', '2025-05-14 05:17:58', 'Unread', 'Appointment Cancelled', 'Your appointment scheduled for May 14, 2025 at 06:00 PM has been cancelled.', 123, 228),
(235, 'barber1@gmail.com', '2025-05-14 05:17:58', 'Unread', 'Appointment Cancelled by Customer', 'Your appointment with Anisur Rahman scheduled for May 14, 2025 at 06:00 PM has been cancelled by the customer.', 123, 228),
(236, 'barber1@gmail.com', '2025-05-14 05:18:36', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 14, 2025 at 05:00 PM with Jocelyn Wilkins', 123, 230),
(237, 'user1@gmail.com', '2025-05-14 05:18:36', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 14, 2025 at 05:00 PM with Jocelyn Wilkins', 123, 230),
(238, 'barber1@gmail.com', '2025-05-14 05:18:40', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 14, 2025 at 06:00 PM with Jocelyn Wilkins', 123, 231),
(239, 'user1@gmail.com', '2025-05-14 05:18:40', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 14, 2025 at 06:00 PM with Jocelyn Wilkins', 123, 231),
(240, 'user1@gmail.com', NULL, 'pending', 'Appointment Cancelled', 'Your appointment scheduled for May 14, 2025 at 05:00 PM has been cancelled.', 123, 230),
(241, 'user1@gmail.com', NULL, 'pending', 'Appointment Completed', 'Your appointment scheduled for May 14, 2025 at 06:00 PM has been marked as completed.', 123, 231),
(242, 'barber1@gmail.com', '2025-05-14 05:34:37', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 14, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 232),
(243, 'user1@gmail.com', '2025-05-14 05:34:37', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 14, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 232),
(244, 'user1@gmail.com', NULL, 'pending', 'Appointment Completed', 'Your appointment scheduled for May 14, 2025 at 08:00 PM has been marked as completed.', 123, 232),
(245, 'barber1@gmail.com', '2025-05-14 05:35:49', 'Unread', 'New Appointment', 'You have a new appointment scheduled for May 14, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 233),
(246, 'user1@gmail.com', '2025-05-14 05:35:49', 'Unread', 'Appointment Confirmed', 'Your appointment has been scheduled for May 14, 2025 at 08:00 PM with Jocelyn Wilkins', 123, 233),
(247, 'user1@gmail.com', NULL, 'pending', 'Appointment Completed', 'Your appointment scheduled for May 14, 2025 at 08:00 PM has been marked as completed.', 123, 233);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int(11) NOT NULL,
  `Amount` int(11) NOT NULL,
  `PayMethod` varchar(20) NOT NULL,
  `PayStatus` varchar(20) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `TransactionID` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`PaymentID`, `Amount`, `PayMethod`, `PayStatus`, `CreatedAt`, `UpdatedAt`, `TransactionID`) VALUES
(1, 25, 'Credit Card', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(2, 35, 'Cash', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(3, 40, 'Credit Card', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(4, 25, 'Debit Card', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(5, 30, 'Cash', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(6, 50, 'Credit Card', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(7, 35, 'Bkash', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(8, 18, 'Credit Card', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(9, 45, 'Cash', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(10, 25, 'Debit Card', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(11, 35, 'Credit Card', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(12, 15, 'Bkash', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(13, 65, 'Credit Card', 'Pending', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(14, 25, 'Debit Card', 'Pending', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(15, 40, 'Bkash', 'Pending', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(16, 35, 'Credit Card', 'Pending', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(17, 20, 'Cash', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(18, 25, 'Credit Card', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(19, 30, 'Bkash', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(20, 40, 'Bkash', 'Completed', '2025-04-22 18:45:35', '2025-05-11 13:36:03', ''),
(21, 350, 'credit_card', 'pending', '2025-05-01 11:45:53', '2025-05-11 13:36:03', ''),
(22, 530, 'credit_card', 'pending', '2025-05-03 17:44:41', '2025-05-11 13:36:03', ''),
(23, 150, 'credit_card', 'pending', '2025-05-03 17:47:54', '2025-05-11 13:36:03', ''),
(24, 530, 'credit_card', 'pending', '2025-05-06 16:38:26', '2025-05-11 13:36:03', ''),
(25, 400, 'Online', 'Pending', '2025-05-07 16:13:22', '2025-05-11 13:36:03', ''),
(26, 400, 'Online', 'Pending', '2025-05-07 16:15:11', '2025-05-11 13:36:03', ''),
(27, 650, 'Online', 'Pending', '2025-05-07 16:15:31', '2025-05-11 13:36:03', ''),
(28, 650, 'Online', 'Pending', '2025-05-07 16:15:57', '2025-05-11 13:36:03', ''),
(29, 150, 'Online', 'Pending', '2025-05-07 16:17:19', '2025-05-11 13:36:03', ''),
(30, 1300, 'Online', 'Pending', '2025-05-07 16:22:10', '2025-05-11 13:36:03', ''),
(31, 400, 'Online', 'Pending', '2025-05-07 16:23:02', '2025-05-11 13:36:03', ''),
(32, 400, 'Online', 'Pending', '2025-05-07 16:26:04', '2025-05-11 13:36:03', ''),
(33, 250, 'Online', 'Pending', '2025-05-07 18:55:49', '2025-05-11 13:36:03', ''),
(34, 250, 'Online', 'Pending', '2025-05-07 19:04:04', '2025-05-11 13:36:03', ''),
(35, 650, 'Online', 'Pending', '2025-05-07 19:09:38', '2025-05-11 13:36:03', ''),
(36, 250, 'Online', 'Pending', '2025-05-10 20:04:11', '2025-05-11 13:36:03', ''),
(37, 250, 'Online', 'Pending', '2025-05-10 20:36:28', '2025-05-11 13:36:03', ''),
(38, 250, 'Online', 'Pending', '2025-05-10 20:43:24', '2025-05-11 13:36:03', ''),
(39, 250, 'Online', 'Pending', '2025-05-10 20:43:59', '2025-05-11 13:36:03', ''),
(40, 250, 'Online', 'Pending', '2025-05-10 20:46:19', '2025-05-11 13:36:03', ''),
(41, 150, 'Online', 'Pending', '2025-05-10 20:48:04', '2025-05-11 13:36:03', ''),
(42, 250, 'Online', 'Pending', '2025-05-10 21:08:30', '2025-05-11 13:36:03', ''),
(43, 250, 'Online', 'Pending', '2025-05-10 21:13:38', '2025-05-11 13:36:03', ''),
(44, 250, 'Online', 'Pending', '2025-05-10 21:23:29', '2025-05-11 13:36:03', ''),
(45, 250, 'Online', 'Pending', '2025-05-10 21:23:50', '2025-05-11 13:36:03', ''),
(46, 250, 'Online', 'Pending', '2025-05-10 21:25:19', '2025-05-11 13:36:03', ''),
(47, 250, 'Online', 'Pending', '2025-05-10 21:26:22', '2025-05-11 13:36:03', ''),
(48, 250, 'Online', 'Pending', '2025-05-10 21:27:14', '2025-05-11 13:36:03', ''),
(49, 250, 'Online', 'Pending', '2025-05-10 21:28:37', '2025-05-11 13:36:03', ''),
(50, 150, 'Online', 'Pending', '2025-05-10 21:31:47', '2025-05-11 13:36:03', ''),
(51, 250, 'Online', 'Pending', '2025-05-10 21:42:21', '2025-05-11 13:36:03', ''),
(52, 150, 'Online', 'Pending', '2025-05-10 21:57:42', '2025-05-11 13:36:03', ''),
(53, 250, 'Online', 'Pending', '2025-05-11 10:13:23', '2025-05-11 13:36:03', ''),
(54, 150, 'Online', 'Pending', '2025-05-11 10:13:26', '2025-05-11 13:36:03', ''),
(55, 250, 'Online', 'Pending', '2025-05-11 10:17:01', '2025-05-11 13:36:03', ''),
(56, 150, 'Online', 'Pending', '2025-05-11 10:17:25', '2025-05-11 13:36:03', ''),
(57, 250, 'Online', 'Pending', '2025-05-11 10:18:06', '2025-05-11 13:36:03', ''),
(59, 250, 'Online', 'Pending', '2025-05-11 10:19:12', '2025-05-11 13:36:03', ''),
(62, 250, 'Online', 'Pending', '2025-05-11 10:20:21', '2025-05-11 13:36:03', ''),
(65, 250, 'Online', 'Pending', '2025-05-11 11:09:56', '2025-05-11 13:36:03', ''),
(66, 150, 'Online', 'Pending', '2025-05-11 11:10:31', '2025-05-11 13:36:03', ''),
(69, 250, 'Online', 'Pending', '2025-05-11 11:10:50', '2025-05-11 13:36:03', ''),
(77, 250, 'Online', 'Pending', '2025-05-11 11:14:38', '2025-05-11 13:36:03', ''),
(93, 250, 'Online', 'Pending', '2025-05-11 11:18:14', '2025-05-11 13:36:03', ''),
(98, 250, 'Online', 'Pending', '2025-05-11 11:20:33', '2025-05-11 13:36:03', ''),
(104, 250, 'Online', 'Pending', '2025-05-11 11:25:06', '2025-05-11 13:36:03', ''),
(106, 150, 'Online', 'Pending', '2025-05-11 11:25:23', '2025-05-11 13:36:03', ''),
(110, 250, 'Online', 'Pending', '2025-05-11 11:26:31', '2025-05-11 13:36:03', ''),
(111, 150, 'Online', 'Pending', '2025-05-11 11:26:39', '2025-05-11 13:36:03', ''),
(112, 250, 'Online', 'Pending', '2025-05-11 11:26:42', '2025-05-11 13:36:03', ''),
(141, 250, 'Online', 'Pending', '2025-05-11 11:31:54', '2025-05-11 13:36:03', ''),
(148, 250, 'Online', 'Pending', '2025-05-11 11:37:02', '2025-05-11 13:36:03', ''),
(149, 250, 'Online', 'Pending', '2025-05-11 11:38:06', '2025-05-11 13:36:03', ''),
(150, 250, 'Online', 'Pending', '2025-05-11 11:39:09', '2025-05-11 13:36:03', ''),
(151, 250, 'Online', 'Pending', '2025-05-11 11:40:13', '2025-05-11 13:36:03', ''),
(152, 250, 'Online', 'Pending', '2025-05-11 11:40:31', '2025-05-11 13:36:03', ''),
(153, 250, 'Online', 'Pending', '2025-05-11 11:40:56', '2025-05-11 13:36:03', ''),
(154, 250, 'Online', 'Pending', '2025-05-11 11:41:00', '2025-05-11 13:36:03', ''),
(155, 250, 'Online', 'Pending', '2025-05-11 12:34:25', '2025-05-11 13:36:03', ''),
(156, 150, 'Online', 'Pending', '2025-05-11 12:36:20', '2025-05-11 13:36:03', ''),
(157, 250, 'Online', 'Pending', '2025-05-11 12:37:22', '2025-05-11 13:36:03', ''),
(158, 300, 'Online', 'Pending', '2025-05-11 12:39:44', '2025-05-11 13:36:03', ''),
(159, 200, 'Online', 'Pending', '2025-05-11 12:39:47', '2025-05-11 13:36:03', ''),
(160, 250, 'Online', 'Pending', '2025-05-11 12:39:51', '2025-05-11 13:36:03', ''),
(161, 250, 'Online', 'Pending', '2025-05-11 12:40:53', '2025-05-11 13:36:03', ''),
(162, 250, 'Online', 'Pending', '2025-05-11 12:42:01', '2025-05-11 13:36:03', ''),
(163, 250, 'Online', 'Pending', '2025-05-11 12:42:04', '2025-05-11 13:36:03', ''),
(164, 150, 'Online', 'Pending', '2025-05-11 12:44:39', '2025-05-11 13:36:03', ''),
(165, 150, 'Online', 'Pending', '2025-05-11 12:47:47', '2025-05-11 13:36:03', ''),
(166, 150, 'Online', 'Pending', '2025-05-11 12:50:46', '2025-05-11 13:36:03', ''),
(167, 250, 'Online', 'Pending', '2025-05-11 12:50:56', '2025-05-11 13:36:03', ''),
(168, 250, 'Online', 'Pending', '2025-05-11 12:51:03', '2025-05-11 13:36:03', ''),
(169, 150, 'Online', 'Pending', '2025-05-11 12:55:15', '2025-05-11 13:36:03', ''),
(170, 350, 'Online', 'Pending', '2025-05-11 13:00:51', '2025-05-11 13:36:03', ''),
(171, 250, 'Online', 'Pending', '2025-05-11 13:12:09', '2025-05-11 13:36:03', ''),
(172, 250, 'Online', 'Pending', '2025-05-11 13:12:19', '2025-05-11 13:36:03', ''),
(173, 650, 'Online', 'Pending', '2025-05-11 13:19:45', '2025-05-11 13:36:03', ''),
(174, 400, 'Online', 'Pending', '2025-05-11 13:20:02', '2025-05-11 13:36:03', ''),
(175, 250, 'Online', 'Pending', '2025-05-11 13:21:45', '2025-05-11 13:36:03', ''),
(176, 650, 'Online', 'Pending', '2025-05-11 13:21:45', '2025-05-11 13:36:03', ''),
(177, 400, 'Online', 'Pending', '2025-05-11 13:21:45', '2025-05-11 13:36:03', ''),
(178, 250, 'Online', 'Pending', '2025-05-11 13:25:00', '2025-05-11 13:36:03', ''),
(179, 250, 'Online', 'Pending', '2025-05-11 13:25:00', '2025-05-11 13:36:03', ''),
(180, 200, 'Online', 'Pending', '2025-05-11 13:25:00', '2025-05-11 13:36:03', ''),
(181, 150, 'Online', 'Pending', '2025-05-11 13:25:14', '2025-05-11 13:36:03', ''),
(182, 250, 'Bkash', 'Completed', '2025-05-11 13:39:37', '2025-05-11 13:39:37', '11111111111'),
(183, 250, 'Bkash', 'Completed', '2025-05-11 13:42:00', '2025-05-11 13:42:00', '11111111111'),
(184, 150, 'Bkash', 'Completed', '2025-05-11 13:42:00', '2025-05-11 13:42:00', '11111111111'),
(185, 350, 'Bkash', 'Pending', '2025-05-11 13:43:12', '2025-05-11 13:43:12', '11111111122'),
(186, 250, 'Cash', 'Pending', '2025-05-11 15:30:41', '2025-05-11 15:30:41', ''),
(187, 250, 'Cash', 'Pending', '2025-05-11 15:30:52', '2025-05-11 15:30:52', ''),
(188, 250, 'Bkash', 'Pending', '2025-05-11 15:31:39', '2025-05-11 15:31:39', '11111111111'),
(189, 150, 'Bkash', 'Pending', '2025-05-11 15:31:39', '2025-05-11 15:31:39', '11111111111'),
(190, 250, 'Cash', 'Pending', '2025-05-12 14:11:07', '2025-05-12 14:11:07', ''),
(191, 250, 'Cash', 'Pending', '2025-05-12 14:12:47', '2025-05-12 14:12:47', ''),
(192, 250, 'Bkash', 'Pending', '2025-05-12 14:13:04', '2025-05-12 14:13:04', '11111111111'),
(193, 250, 'Cash', 'Pending', '2025-05-12 14:17:38', '2025-05-12 14:17:38', ''),
(194, 250, 'Cash', 'Pending', '2025-05-12 14:19:37', '2025-05-12 14:19:37', ''),
(195, 250, 'Nagad', 'Pending', '2025-05-12 14:19:47', '2025-05-12 14:19:47', '11111111111'),
(196, 250, 'Cash', 'Pending', '2025-05-12 14:35:09', '2025-05-12 14:35:09', ''),
(197, 250, 'Cash', 'Pending', '2025-05-12 14:39:59', '2025-05-12 14:39:59', ''),
(198, 250, 'Cash', 'Pending', '2025-05-12 15:35:00', '2025-05-12 15:35:00', ''),
(199, 150, 'Rocket', 'Completed', '2025-05-12 15:37:09', '2025-05-12 15:43:21', '11111111111'),
(200, 150, 'Rocket', 'Pending', '2025-05-12 15:50:13', '2025-05-12 15:50:13', '11111111122'),
(201, 250, 'Rocket', 'Pending', '2025-05-12 15:50:13', '2025-05-12 15:50:13', '11111111122'),
(202, 250, 'Rocket', 'Pending', '2025-05-12 15:51:39', '2025-05-12 15:51:39', '11111111122'),
(203, 250, 'Rocket', 'Pending', '2025-05-12 15:51:39', '2025-05-12 15:51:39', '11111111122'),
(204, 400, 'Nagad', 'Completed', '2025-05-12 15:52:59', '2025-05-12 15:53:26', '11111111111'),
(205, 650, 'Nagad', 'Completed', '2025-05-12 15:52:59', '2025-05-12 15:53:29', '11111111111'),
(206, 250, 'Nagad', 'Completed', '2025-05-12 15:52:59', '2025-05-12 15:53:33', '11111111111'),
(207, 250, 'Rocket', 'Completed', '2025-05-12 16:41:58', '2025-05-12 16:45:14', '12345678910'),
(208, 250, 'Cash', 'Pending', '2025-05-12 16:42:39', '2025-05-12 16:42:39', ''),
(209, 250, 'Rocket', 'Pending', '2025-05-12 16:47:38', '2025-05-12 16:47:38', '12345678900'),
(210, 250, 'Cash', 'Completed', '2025-05-13 13:28:29', '2025-05-13 14:39:17', ''),
(211, 650, 'Cash', 'Pending', '2025-05-13 13:28:43', '2025-05-13 13:28:43', ''),
(212, 250, 'Cash', 'Completed', '2025-05-13 15:04:09', '2025-05-13 15:04:27', ''),
(213, 250, 'Cash', 'Pending', '2025-05-13 15:41:42', '2025-05-13 15:41:42', ''),
(214, 250, 'Cash', 'Pending', '2025-05-13 16:01:40', '2025-05-13 16:01:40', ''),
(215, 650, 'Cash', 'Pending', '2025-05-13 16:02:31', '2025-05-13 16:02:31', ''),
(216, 250, 'Cash', 'Pending', '2025-05-13 16:31:37', '2025-05-13 16:31:37', ''),
(217, 250, 'Cash', 'Completed', '2025-05-13 16:33:53', '2025-05-13 16:35:03', ''),
(218, 400, 'Cash', 'Completed', '2025-05-13 16:35:26', '2025-05-13 16:35:43', ''),
(219, 250, 'Cash', 'Completed', '2025-05-13 16:36:39', '2025-05-13 16:36:42', ''),
(220, 250, 'Cash', 'Pending', '2025-05-13 16:37:03', '2025-05-13 16:37:03', ''),
(221, 180, 'Cash', 'Pending', '2025-05-13 16:38:41', '2025-05-13 16:38:41', ''),
(222, 150, 'Cash', 'Pending', '2025-05-13 16:39:50', '2025-05-13 16:39:50', ''),
(223, 250, 'Cash', 'Pending', '2025-05-13 16:41:02', '2025-05-13 16:41:02', ''),
(224, 250, 'Cash', 'Pending', '2025-05-13 16:42:14', '2025-05-13 16:42:14', ''),
(230, 250, 'Cash', 'Pending', '2025-05-13 16:49:38', '2025-05-13 16:49:38', ''),
(231, 250, 'Cash', 'Pending', '2025-05-13 16:50:12', '2025-05-13 16:50:12', ''),
(232, 250, 'Cash', 'Completed', '2025-05-13 16:53:58', '2025-05-13 16:59:12', ''),
(233, 250, 'Cash', 'Pending', '2025-05-13 16:54:46', '2025-05-13 16:54:46', ''),
(234, 250, 'Rocket', 'Pending', '2025-05-13 17:02:07', '2025-05-13 17:02:07', '11111111111'),
(235, 250, 'Cash', 'Pending', '2025-05-13 17:03:08', '2025-05-13 17:03:08', ''),
(236, 250, 'Cash', 'Pending', '2025-05-13 17:04:26', '2025-05-13 17:04:26', ''),
(237, 150, 'Cash', 'Pending', '2025-05-13 17:04:49', '2025-05-13 17:04:49', ''),
(238, 250, 'Cash', 'Pending', '2025-05-13 17:06:49', '2025-05-13 17:06:49', ''),
(239, 150, 'Cash', 'Pending', '2025-05-13 17:07:31', '2025-05-13 17:07:31', ''),
(240, 250, 'Cash', 'Pending', '2025-05-13 17:16:45', '2025-05-13 17:16:45', ''),
(241, 250, 'Cash', 'Pending', '2025-05-13 17:19:55', '2025-05-13 17:19:55', ''),
(242, 250, 'Cash', 'Pending', '2025-05-13 20:32:40', '2025-05-13 20:32:40', ''),
(243, 250, 'Cash', 'Completed', '2025-05-13 20:36:56', '2025-05-13 20:37:44', ''),
(244, 150, 'Cash', 'Pending', '2025-05-13 20:36:56', '2025-05-13 20:36:56', ''),
(245, 250, 'Cash', 'Pending', '2025-05-13 20:52:31', '2025-05-13 20:52:31', ''),
(246, 150, 'Cash', 'Pending', '2025-05-13 20:52:31', '2025-05-13 20:52:31', ''),
(247, 250, 'Rocket', 'Pending', '2025-05-14 05:18:36', '2025-05-14 05:18:36', '11111111111'),
(248, 150, 'Rocket', 'Pending', '2025-05-14 05:18:36', '2025-05-14 05:18:36', '11111111111'),
(249, 150, 'Nagad', 'Pending', '2025-05-14 05:34:37', '2025-05-14 05:34:37', '12345678911'),
(250, 150, 'Nagad', 'Pending', '2025-05-14 05:35:49', '2025-05-14 05:35:49', '12345555555');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `ReferrerID` int(11) NOT NULL,
  `ReferredID` int(11) NOT NULL,
  `ReferralCode` varchar(50) NOT NULL,
  `ExpiresAt` date DEFAULT NULL,
  `Reward` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`ReferrerID`, `ReferredID`, `ReferralCode`, `ExpiresAt`, `Reward`) VALUES
(100, 105, 'REF100RAHIM', '2025-06-30', 10),
(101, 107, 'REF101SARA', '2025-06-30', 10),
(102, 109, 'REF102ROBIUL', '2025-06-30', 10),
(103, 112, 'REF103ESHA', '2025-06-30', 10),
(104, 115, 'REF104MOHAMMAD', '2025-06-30', 10);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `ReviewID` int(11) NOT NULL,
  `Rating` int(11) NOT NULL,
  `Comments` text DEFAULT NULL,
  `BarberID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`ReviewID`, `Rating`, `Comments`, `BarberID`, `CustomerID`) VALUES
(1, 5, 'Karim did an amazing job with my haircut!', 1, 100),
(2, 4, 'Great service, very professional.', 2, 101),
(3, 5, 'Jamal is the best beard stylist in town.', 3, 102),
(4, 4, 'Very relaxing facial, will come back.', 4, 103),
(5, 5, 'Rahim always knows exactly what I want.', 5, 104),
(6, 4, 'Sadia did a great job with my hair color.', 6, 105),
(7, 5, 'Hasan is incredibly skilled and friendly.', 7, 106),
(8, 4, 'My son loves getting his haircut from Nadia.', 8, 107),
(9, 5, 'Ahmed is truly a master of his craft.', 9, 108),
(10, 5, 'Zainab gave me the best haircut I have ever had.', 10, 109),
(11, 4, 'Karim is consistent and professional.', 1, 110),
(12, 5, 'Fatima understood exactly what I wanted.', 2, 111),
(13, 4, 'Good service but had to wait a bit.', 3, 112),
(14, 5, 'Ayesha is amazing! Best facial treatment.', 4, 113),
(15, 4, 'Rahim is very talented with fades.', 5, 114),
(16, 4, 'good fades', 2, 122),
(17, 3, 'bad haircuts', 1, 122),
(18, 4, '!', 6, 122),
(19, 4, 'A', 9, 122),
(20, 5, 'Really good trims!', 12, 123),
(21, 4, 'Talks a lot', 12, 123),
(22, 4, 'Really good', 4, 123),
(23, 3, 'Decent cuts', 12, 123);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `ServiceID` int(11) NOT NULL,
  `Name` varchar(60) NOT NULL,
  `Description` text DEFAULT NULL,
  `Duration` int(11) NOT NULL,
  `Price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`ServiceID`, `Name`, `Description`, `Duration`, `Price`) VALUES
(1, 'Basic Haircut', 'Standard haircut with scissors or clippers', 60, 250),
(2, 'Premium Haircut', 'Haircut with styling and detailed finishing', 60, 350),
(3, 'Beard Trim', 'Shape and trim facial hair', 60, 150),
(4, 'Facial', 'Deep cleansing facial treatment', 60, 250),
(5, 'Hot Towel Shave', 'Traditional straight razor shave with hot towel', 60, 300),
(6, 'Hair Coloring', 'Professional hair dyeing service', 60, 650),
(7, 'Hair & Beard Combo', 'Haircut with beard trimming', 60, 350),
(8, 'Kids Haircut', 'Haircut for children under 12', 60, 180),
(9, 'Head Shave', 'Complete head shave with razor', 60, 200),
(10, 'Scalp Treatment', 'Deep conditioning scalp treatment', 60, 400);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('7tR899QjWFbUJ64SxvGm6urDc9FEe3o4aTF9Ycnz', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMWpUdjBHeHBsTTFtVHNPRXRjaXVYZ3BQa0ZXR2d2ZURBZEZxOG1VTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1746091918);

-- --------------------------------------------------------

--
-- Table structure for table `slots`
--

CREATE TABLE `slots` (
  `SlotID` int(11) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `Time` datetime NOT NULL,
  `BarberID` int(11) NOT NULL,
  `AppointmentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slots`
--

INSERT INTO `slots` (`SlotID`, `Status`, `Time`, `BarberID`, `AppointmentID`) VALUES
(1, 'Booked', '2025-04-24 10:00:00', 1, 1),
(2, 'Booked', '2025-04-24 11:00:00', 2, 2),
(3, 'Booked', '2025-04-24 14:00:00', 3, 3),
(4, 'Booked', '2025-04-25 09:30:00', 4, 4),
(5, 'Booked', '2025-04-25 13:00:00', 5, 5),
(6, 'Booked', '2025-04-26 15:00:00', 6, 6),
(7, 'Booked', '2025-04-26 16:30:00', 7, 7),
(8, 'Booked', '2025-04-27 10:00:00', 8, 8),
(9, 'Booked', '2025-04-27 11:30:00', 9, 9),
(10, 'Booked', '2025-04-28 14:00:00', 10, 10),
(11, 'Booked', '2025-04-28 16:00:00', 1, 11),
(12, 'Booked', '2025-04-29 09:00:00', 2, 12),
(13, 'Pending', '2025-04-29 13:00:00', 3, 13),
(14, 'Pending', '2025-04-30 10:30:00', 4, 14),
(15, 'Pending', '2025-04-30 15:00:00', 5, 15),
(16, 'Pending', '2025-05-01 11:00:00', 6, 16),
(17, 'Booked', '2025-05-01 14:00:00', 7, 17),
(18, 'Booked', '2025-05-02 09:30:00', 8, 18),
(19, 'Booked', '2025-05-02 13:00:00', 9, 19),
(20, 'Booked', '2025-05-03 16:00:00', 10, 20),
(21, 'booked', '2025-05-02 17:00:00', 5, 21),
(22, 'booked', '2025-05-04 10:30:00', 11, 22),
(23, 'booked', '2025-05-05 12:30:00', 1, 23),
(24, 'booked', '2025-05-07 09:00:00', 11, 24),
(25, 'Booked', '2025-05-07 09:00:00', 9, 25),
(26, 'Booked', '2025-05-07 10:00:00', 11, 26),
(27, 'Booked', '2025-05-07 10:00:00', 4, 27),
(28, 'Booked', '2025-05-07 12:00:00', 7, 28),
(29, 'Booked', '2025-05-07 11:00:00', 4, 29),
(30, 'Booked', '2025-05-07 09:00:00', 4, 30),
(31, 'Booked', '2025-05-07 13:00:00', 4, 31),
(32, 'Booked', '2025-05-07 13:00:00', 2, 32),
(33, 'Booked', '2025-05-07 14:00:00', 2, 33),
(34, 'Booked', '2025-05-07 15:00:00', 2, 34),
(35, 'Booked', '2025-05-07 14:00:00', 7, 35),
(36, 'Booked', '2025-05-07 15:00:00', 7, 36),
(37, 'Booked', '2025-05-07 11:00:00', 11, 37),
(38, 'Booked', '2025-05-07 12:00:00', 11, 38),
(39, 'Booked', '2025-05-07 13:00:00', 11, 39),
(40, 'Booked', '2025-05-07 14:00:00', 8, 40),
(41, 'Booked', '2025-05-09 10:00:00', 9, 41),
(42, 'Booked', '2025-05-09 10:00:00', 11, 42),
(43, 'Booked', '2025-05-09 11:00:00', 4, 43),
(44, 'Booked', '2025-05-13 12:00:00', 9, 44),
(45, 'Booked', '2025-05-13 12:00:00', 4, 45),
(46, 'Booked', '2025-05-13 13:00:00', 9, 46),
(47, 'Available', '2025-05-12 12:00:00', 12, 47),
(48, 'Available', '2025-05-13 15:00:00', 12, 48),
(49, 'Booked', '2025-05-13 14:00:00', 7, 49),
(50, 'Booked', '2025-05-13 16:00:00', 12, 50),
(51, 'Booked', '2025-05-14 15:00:00', 3, 51),
(52, 'Booked', '2025-05-14 12:00:00', 1, 52),
(53, 'Booked', '2025-05-14 11:00:00', 1, 53),
(54, 'Booked', '2025-05-14 14:00:00', 12, 54),
(55, 'Booked', '2025-05-15 13:00:00', 2, 55),
(56, 'Booked', '2025-05-15 14:00:00', 2, 56),
(57, 'Booked', '2025-05-15 12:00:00', 12, 57),
(58, 'Booked', '2025-05-15 15:00:00', 8, 58),
(59, 'Booked', '2025-05-14 13:00:00', 7, 59),
(60, 'Booked', '2025-05-14 14:00:00', 3, 60),
(61, 'Booked', '2025-05-14 15:00:00', 12, 61),
(62, 'Booked', '2025-05-14 16:00:00', 1, 62),
(63, 'Booked', '2025-05-14 17:00:00', 8, 63),
(64, 'Booked', '2025-05-14 16:00:00', 4, 64),
(65, 'Booked', '2025-05-14 11:00:00', 12, 65),
(66, 'Available', '2025-05-14 12:00:00', 12, 66),
(67, 'Available', '2025-05-14 13:00:00', 12, 67),
(68, 'Booked', '2025-05-12 12:00:00', 3, 68),
(69, 'Booked', '2025-05-12 12:00:00', 7, 69),
(70, 'Booked', '2025-05-11 15:00:00', 3, 70),
(71, 'Booked', '2025-05-11 15:00:00', 7, 71),
(72, 'Booked', '2025-05-11 16:00:00', 3, 72),
(73, 'Booked', '2025-05-11 16:00:00', 7, 73),
(74, 'Booked', '2025-05-11 13:00:00', 12, 74),
(75, 'Booked', '2025-05-11 13:00:00', 3, 75),
(77, 'Booked', '2025-05-11 11:00:00', 1, 77),
(78, 'Booked', '2025-05-13 14:00:00', 3, 78),
(81, 'Booked', '2025-05-13 14:00:00', 12, 81),
(82, 'Booked', '2025-05-12 15:00:00', 7, 82),
(83, 'Booked', '2025-05-12 12:00:00', 1, 83),
(84, 'Booked', '2025-05-14 13:00:00', 12, 84),
(86, 'Booked', '2025-05-12 17:00:00', 7, 86),
(87, 'Booked', '2025-05-14 15:00:00', 12, 87),
(88, 'Booked', '2025-05-13 13:00:00', 12, 88),
(89, 'Booked', '2025-05-12 13:00:00', 12, 89),
(90, 'Booked', '2025-05-13 14:00:00', 12, 90),
(91, 'Booked', '2025-05-12 14:00:00', 12, 91),
(92, 'Booked', '2025-05-12 12:00:00', 12, 92),
(114, 'Booked', '2025-05-12 15:00:00', 2, 114),
(115, 'Booked', '2025-05-12 17:00:00', 2, 115),
(116, 'Booked', '2025-05-12 17:00:00', 12, 116),
(117, 'Booked', '2025-05-12 18:00:00', 12, 117),
(118, 'Booked', '2025-05-12 16:00:00', 2, 118),
(119, 'Booked', '2025-05-12 18:00:00', 2, 119),
(120, 'Booked', '2025-05-12 14:00:00', 3, 120),
(121, 'Booked', '2025-05-12 12:00:00', 8, 121),
(122, 'Booked', '2025-05-12 13:00:00', 5, 122),
(123, 'Booked', '2025-05-13 14:00:00', 7, 123),
(124, 'Booked', '2025-05-13 14:00:00', 2, 124),
(125, 'Booked', '2025-05-13 12:00:00', 7, 125),
(126, 'Booked', '2025-05-13 13:00:00', 7, 126),
(127, 'Booked', '2025-05-13 14:00:00', 7, 127),
(128, 'Booked', '2025-05-12 13:00:00', 2, 128),
(129, 'Booked', '2025-05-12 13:00:00', 7, 129),
(130, 'Booked', '2025-05-12 11:00:00', 7, 130),
(131, 'Booked', '2025-05-12 11:00:00', 3, 131),
(132, 'Booked', '2025-05-12 12:00:00', 7, 132),
(133, 'Booked', '2025-05-12 13:00:00', 7, 133),
(134, 'Booked', '2025-05-12 12:00:00', 2, 134),
(135, 'Booked', '2025-05-12 12:00:00', 4, 135),
(136, 'Booked', '2025-05-12 13:00:00', 2, 136),
(137, 'Booked', '2025-05-12 13:00:00', 7, 137),
(138, 'Booked', '2025-05-12 13:00:00', 2, 138),
(139, 'Booked', '2025-05-12 13:00:00', 7, 139),
(140, 'Booked', '2025-05-12 14:00:00', 12, 140),
(141, 'Booked', '2025-05-12 15:00:00', 7, 141),
(142, 'Booked', '2025-05-12 16:00:00', 3, 142),
(143, 'Booked', '2025-05-12 17:00:00', 3, 143),
(144, 'Booked', '2025-05-13 13:00:00', 7, 144),
(145, 'Booked', '2025-05-13 15:00:00', 7, 145),
(146, 'Booked', '2025-05-12 13:00:00', 2, 146),
(147, 'Booked', '2025-05-12 14:00:00', 7, 147),
(148, 'Booked', '2025-05-12 15:00:00', 7, 148),
(149, 'Booked', '2025-05-12 14:00:00', 7, 149),
(150, 'Booked', '2025-05-12 15:00:00', 7, 150),
(151, 'Booked', '2025-05-12 16:00:00', 7, 151),
(152, 'Booked', '2025-05-12 15:00:00', 4, 152),
(153, 'Booked', '2025-05-12 16:00:00', 4, 153),
(154, 'Booked', '2025-05-12 17:00:00', 4, 154),
(155, 'Booked', '2025-05-12 15:00:00', 2, 155),
(156, 'Booked', '2025-05-12 16:00:00', 2, 156),
(157, 'Booked', '2025-05-12 17:00:00', 2, 157),
(158, 'Booked', '2025-05-12 17:00:00', 2, 158),
(159, 'Booked', '2025-05-12 16:00:00', 2, 159),
(160, 'Booked', '2025-05-12 15:00:00', 2, 160),
(161, 'Booked', '2025-05-12 14:00:00', 2, 161),
(162, 'Booked', '2025-05-12 15:00:00', 7, 162),
(163, 'Booked', '2025-05-12 16:00:00', 2, 163),
(164, 'Booked', '2025-05-13 14:00:00', 7, 164),
(165, 'Booked', '2025-05-12 16:00:00', 7, 165),
(166, 'Booked', '2025-05-12 16:00:00', 7, 166),
(167, 'Booked', '2025-05-12 17:00:00', 7, 167),
(168, 'Booked', '2025-05-12 15:00:00', 7, 168),
(169, 'Booked', '2025-05-12 16:00:00', 4, 169),
(170, 'Booked', '2025-05-12 16:00:00', 4, 170),
(171, 'Booked', '2025-05-12 17:00:00', 2, 171),
(172, 'Booked', '2025-05-12 18:00:00', 7, 172),
(173, 'Available', '2025-05-13 12:00:00', 12, 173),
(174, 'Available', '2025-05-13 13:00:00', 12, 174),
(175, 'Booked', '2025-05-15 14:00:00', 12, 175),
(176, 'Available', '2025-05-12 20:00:00', 12, 176),
(177, 'Booked', '2025-05-12 16:00:00', 12, 177),
(178, 'Available', '2025-05-14 17:00:00', 12, 178),
(179, 'Booked', '2025-05-12 20:00:00', 12, 179),
(180, 'Booked', '2025-05-12 17:00:00', 12, 180),
(181, 'Booked', '2025-05-12 19:00:00', 12, 181),
(182, 'Booked', '2025-05-12 20:00:00', 12, 182),
(183, 'Booked', '2025-05-12 20:00:00', 12, 183),
(184, 'Booked', '2025-05-12 19:00:00', 12, 184),
(185, 'Booked', '2025-05-12 17:00:00', 12, 185),
(186, 'Booked', '2025-05-12 18:00:00', 12, 186),
(187, 'Booked', '2025-05-12 17:00:00', 12, 187),
(188, 'Booked', '2025-05-12 18:00:00', 12, 188),
(189, 'Booked', '2025-05-12 19:00:00', 12, 189),
(190, 'Booked', '2025-05-12 19:00:00', 12, 190),
(191, 'Booked', '2025-05-12 18:00:00', 12, 191),
(192, 'Available', '2025-05-12 17:00:00', 12, 192),
(193, 'Booked', '2025-05-13 14:00:00', 12, 193),
(194, 'Booked', '2025-05-14 15:00:00', 12, 194),
(195, 'Booked', '2025-05-13 18:00:00', 12, 195),
(196, 'Booked', '2025-05-13 17:00:00', 12, 196),
(197, 'Booked', '2025-05-13 16:00:00', 12, 197),
(198, 'Booked', '2025-05-13 20:00:00', 12, 198),
(199, 'Booked', '2025-05-13 17:00:00', 12, 199),
(200, 'Booked', '2025-05-13 18:00:00', 12, 200),
(201, 'Booked', '2025-05-13 20:00:00', 12, 201),
(202, 'Booked', '2025-05-13 18:00:00', 12, 202),
(203, 'Available', '2025-05-13 19:00:00', 12, 203),
(204, 'Booked', '2025-05-13 19:00:00', 12, 204),
(205, 'Booked', '2025-05-13 18:00:00', 12, 205),
(206, 'Booked', '2025-05-13 18:00:00', 12, 206),
(207, 'Booked', '2025-05-13 19:00:00', 12, 207),
(208, 'Booked', '2025-05-13 18:00:00', 12, 213),
(209, 'Available', '2025-05-13 20:00:00', 12, 214),
(210, 'Booked', '2025-05-13 18:00:00', 12, 215),
(211, 'Booked', '2025-05-13 20:00:00', 12, 216),
(212, 'Booked', '2025-05-13 19:00:00', 12, 217),
(213, 'Available', '2025-05-13 20:00:00', 12, 218),
(214, 'Booked', '2025-05-13 20:00:00', 12, 219),
(215, 'Available', '2025-05-13 20:00:00', 12, 220),
(216, 'Booked', '2025-05-13 20:00:00', 12, 221),
(217, 'Available', '2025-05-13 20:00:00', 12, 222),
(218, 'Booked', '2025-05-13 20:00:00', 12, 223),
(219, 'Booked', '2025-05-13 19:00:00', 12, 224),
(220, 'Booked', '2025-05-13 18:00:00', 12, 225),
(221, 'Booked', '2025-05-14 14:00:00', 12, 226),
(222, 'Available', '2025-05-14 15:00:00', 12, 227),
(223, 'Booked', '2025-05-14 18:00:00', 12, 228),
(224, 'Booked', '2025-05-14 19:00:00', 12, 229),
(225, 'Booked', '2025-05-14 17:00:00', 12, 230),
(226, 'Booked', '2025-05-14 18:00:00', 12, 231),
(227, 'Booked', '2025-05-14 20:00:00', 12, 232),
(228, 'Booked', '2025-05-14 20:00:00', 12, 233);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'test', 'test@test.com', NULL, '$2y$12$6MAozSFMp2eIbjSjVCz8V.ObR/aXSuGxLfjqhkqfS5QItWXRp.fqC', NULL, '2025-05-01 03:22:19', '2025-05-01 03:22:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD KEY `PaymentID` (`PaymentID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `apptcontains`
--
ALTER TABLE `apptcontains`
  ADD KEY `ServiceID` (`ServiceID`),
  ADD KEY `AppointmentID` (`AppointmentID`);

--
-- Indexes for table `barberhas`
--
ALTER TABLE `barberhas`
  ADD PRIMARY KEY (`bhid`),
  ADD KEY `BarberID` (`BarberID`),
  ADD KEY `AppointmentID` (`AppointmentID`);

--
-- Indexes for table `barbers`
--
ALTER TABLE `barbers`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `AppointmentID` (`AppointmentID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PaymentID`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`ReferrerID`,`ReferredID`),
  ADD KEY `ReferredID` (`ReferredID`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `BarberID` (`BarberID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`ServiceID`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `slots`
--
ALTER TABLE `slots`
  ADD PRIMARY KEY (`SlotID`),
  ADD KEY `BarberID` (`BarberID`),
  ADD KEY `AppointmentID` (`AppointmentID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `barberhas`
--
ALTER TABLE `barberhas`
  MODIFY `bhid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `barbers`
--
ALTER TABLE `barbers`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `ReviewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `slots`
--
ALTER TABLE `slots`
  MODIFY `SlotID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`PaymentID`) REFERENCES `payments` (`PaymentID`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`UserID`);

--
-- Constraints for table `apptcontains`
--
ALTER TABLE `apptcontains`
  ADD CONSTRAINT `apptcontains_ibfk_1` FOREIGN KEY (`ServiceID`) REFERENCES `services` (`ServiceID`),
  ADD CONSTRAINT `apptcontains_ibfk_2` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`);

--
-- Constraints for table `barberhas`
--
ALTER TABLE `barberhas`
  ADD CONSTRAINT `barberhas_ibfk_1` FOREIGN KEY (`BarberID`) REFERENCES `barbers` (`UserID`),
  ADD CONSTRAINT `barberhas_ibfk_2` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `AppointmentID` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`),
  ADD CONSTRAINT `CustomerID` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`UserID`);

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`ReferrerID`) REFERENCES `customers` (`UserID`),
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`ReferredID`) REFERENCES `customers` (`UserID`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`BarberID`) REFERENCES `barbers` (`UserID`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`UserID`);

--
-- Constraints for table `slots`
--
ALTER TABLE `slots`
  ADD CONSTRAINT `slots_ibfk_1` FOREIGN KEY (`BarberID`) REFERENCES `barbers` (`UserID`),
  ADD CONSTRAINT `slots_ibfk_2` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
