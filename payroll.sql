-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 13, 2022 at 02:27 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payroll`
--

-- --------------------------------------------------------

--
-- Table structure for table `allowances`
--

CREATE TABLE `allowances` (
  `id` int(30) NOT NULL,
  `allowance` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `allowances`
--

INSERT INTO `allowances` (`id`, `allowance`, `description`) VALUES
(1, 'Sample', 'Sample Allowance'),
(2, 'Phone', 'Phone Allowance'),
(3, 'Rice', 'Rice Allowance'),
(4, 'House', 'House Allowance'),
(5, 'New Allowances UPDATED', 'sadeaewqe UPDATED'),
(6, 'New Allowances', 'SDFSDF');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(20) NOT NULL,
  `log_type` tinyint(1) NOT NULL COMMENT '1 = AM IN,2 = AM out, 3= PM IN, 4= PM out\r\n',
  `datetime_log` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `type` int(1) NOT NULL DEFAULT 1 COMMENT '1=manual;2=auto'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `log_type`, `datetime_log`, `date_updated`, `type`) VALUES
(616, 13, 1, '2021-02-05 17:40:09', '2021-02-05 09:44:06', 2),
(617, 13, 1, '2021-02-09 11:21:06', '2021-02-09 11:21:16', 2),
(618, 13, 4, '2021-02-09 12:24:43', '2021-02-09 12:25:15', 2),
(619, 13, 4, '2021-02-09 12:25:05', '2021-02-09 12:25:15', 2),
(620, 23, 1, '2021-02-09 12:25:35', '2021-02-09 12:25:45', 2),
(621, 23, 4, '2021-02-09 12:27:05', '2021-02-09 12:27:15', 2),
(622, 13, 1, '2021-02-10 11:01:41', '2021-02-10 11:02:22', 2),
(623, 13, 4, '2021-02-10 11:01:54', '2021-02-10 11:02:22', 2),
(624, 23, 1, '2021-02-10 11:02:03', '2021-02-10 11:02:22', 2),
(625, 13, 1, '2021-02-11 22:26:05', '2021-02-11 14:26:41', 2),
(626, 23, 1, '2021-02-11 22:26:13', '2021-02-11 14:26:41', 2),
(627, 13, 4, '2021-02-11 22:26:16', '2021-02-11 14:26:41', 2),
(628, 23, 4, '2021-02-11 22:26:18', '2021-02-11 14:26:41', 2),
(634, 13, 1, '2021-03-09 20:53:37', '2021-03-09 12:53:50', 2),
(635, 13, 4, '2021-03-09 20:53:39', '2021-03-09 12:53:50', 2),
(636, 13, 4, '2021-03-09 20:53:41', '2021-03-09 12:53:50', 2),
(637, 23, 1, '2021-03-09 20:54:11', '2021-03-09 12:54:22', 2),
(638, 23, 4, '2021-03-09 20:54:14', '2021-03-09 12:54:22', 2),
(639, 25, 1, '2021-03-09 20:56:02', '2021-03-09 12:56:10', 2),
(640, 25, 4, '2021-03-09 20:56:22', '2021-03-09 12:56:31', 2),
(641, 24, 1, '2021-03-09 20:57:21', '2021-03-09 12:57:29', 2),
(642, 13, 1, '2021-07-19 10:57:00', '2021-07-19 10:59:23', 1),
(643, 13, 1, '2021-07-20 01:35:00', '2021-07-20 11:35:53', 1),
(644, 13, 4, '2021-07-21 11:35:00', '2021-07-20 11:36:08', 1),
(645, 13, 4, '2021-07-17 11:36:00', '2021-07-20 11:36:30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `contributions`
--

CREATE TABLE `contributions` (
  `id` int(11) NOT NULL,
  `contribution` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `contributions`
--

INSERT INTO `contributions` (`id`, `contribution`) VALUES
(1, 'SSS'),
(2, 'PH'),
(3, 'HDMF');

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `id` int(30) NOT NULL,
  `deduction` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `deductions`
--

INSERT INTO `deductions` (`id`, `deduction`, `description`) VALUES
(1, 'Cash Advance', 'Cash Advance'),
(3, 'Sample', 'Sample Deduction'),
(6, 'Philhealth', 'dsad'),
(8, '', 'test'),
(9, 'test deduction1', 'test');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `name`) VALUES
(1, 'IT Department '),
(2, 'HR Department Update11'),
(3, 'Accounting and Finance Department'),
(4, 'HR'),
(5, 'Developer Departments'),
(6, 'IT Department update103'),
(8, 'IT Department 1111'),
(9, 'New Department 101'),
(10, 'SSS'),
(15, 'Deduction1'),
(16, 'deductions 2');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(20) NOT NULL,
  `employee_no` varchar(100) NOT NULL,
  `employee_code` varchar(10) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(20) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `department_id` int(30) NOT NULL,
  `position_id` int(30) NOT NULL,
  `salary` double NOT NULL,
  `time_in` varchar(10) NOT NULL,
  `time_out` varchar(10) NOT NULL,
  `status` int(1) NOT NULL DEFAULT 1 COMMENT '0=inactive;1=active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id`, `employee_no`, `employee_code`, `firstname`, `middlename`, `lastname`, `department_id`, `position_id`, `salary`, `time_in`, `time_out`, `status`) VALUES
(13, '2021-7027', '1234', 'Niel', 'M', 'Daculan ', 1, 7, 380, '08:00', '17:00', 1),
(23, '2021-4424', '1239', 'Jean', 'F', 'Doe', 1, 1, 800, '08:00', '05:00', 1),
(24, '2021-7307', '1357', 'David', 'D', 'Maureal', 1, 7, 400, '08:00', '17:00', 1),
(25, '2021-6942', '1111', 'Lotta', 'D', 'Maureal', 1, 1, 500, '08:00', '17:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `employee_allowances`
--

CREATE TABLE `employee_allowances` (
  `id` int(30) NOT NULL,
  `employee_id` int(30) NOT NULL,
  `allowance_id` int(30) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1 = Monthly, 2= Semi-Montly, 3 = once',
  `amount` float NOT NULL,
  `effective_date` date NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `employee_allowances`
--

INSERT INTO `employee_allowances` (`id`, `employee_id`, `allowance_id`, `type`, `amount`, `effective_date`, `date_created`) VALUES
(20, 13, 2, 1, 500, '2021-01-04', '2021-01-04 21:41:36'),
(21, 13, 3, 3, 500, '2021-01-08', '2021-01-08 12:02:01'),
(22, 23, 5, 2, 300, '2021-04-01', '2021-04-01 17:09:57');

-- --------------------------------------------------------

--
-- Table structure for table `employee_contributions`
--

CREATE TABLE `employee_contributions` (
  `id` int(11) NOT NULL,
  `employee_id` int(30) NOT NULL,
  `contribution_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `payroll_type` int(1) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `employee_contributions`
--

INSERT INTO `employee_contributions` (`id`, `employee_id`, `contribution_id`, `amount`, `payroll_type`) VALUES
(14, 23, 3, 300, 1),
(15, 23, 1, 346, 2);

-- --------------------------------------------------------

--
-- Table structure for table `employee_deductions`
--

CREATE TABLE `employee_deductions` (
  `id` int(30) NOT NULL,
  `employee_id` int(30) NOT NULL,
  `deduction_id` int(30) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1 = Monthly, 2= Semi-Montly, 3 = once',
  `amount` float NOT NULL,
  `effective_date` date NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `employee_deductions`
--

INSERT INTO `employee_deductions` (`id`, `employee_id`, `deduction_id`, `type`, `amount`, `effective_date`, `date_created`) VALUES
(12, 9, 7, 2, 100, '0000-00-00', '2020-12-30 12:39:57'),
(13, 9, 5, 2, 800, '0000-00-00', '2021-01-01 19:40:05'),
(14, 9, 5, 2, 200, '0000-00-00', '2021-01-01 21:35:21'),
(15, 9, 7, 2, 200, '0000-00-00', '2021-01-01 21:39:06'),
(17, 13, 1, 3, 100, '0000-00-00', '2021-01-02 12:18:00'),
(18, 13, 7, 3, 500, '0000-00-00', '2021-01-08 12:01:39'),
(19, 25, 5, 2, 100, '0000-00-00', '2021-03-09 13:05:39');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(30) NOT NULL,
  `ref_no` text NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1 = monthly ,2 semi-monthly',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 =New,1 = computed',
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `ref_no`, `date_from`, `date_to`, `type`, `status`, `date_created`) VALUES
(22, '2021-4371', '2021-03-01', '2021-03-15', 2, 1, '2021-03-09 12:58:56'),
(23, '2021-5337', '2021-04-01', '2021-04-15', 2, 1, '2021-04-01 17:11:22'),
(24, '2021-8813', '2021-03-01', '2021-04-15', 2, 1, '2021-04-01 17:23:10'),
(25, '2021-4985', '2021-04-01', '2021-04-15', 2, 0, '2021-04-01 17:23:26'),
(26, '2021-2510', '2021-04-01', '2021-04-15', 1, 1, '2021-04-01 17:23:37'),
(27, '2021-2369', '2021-07-16', '2021-07-30', 2, 1, '2021-07-20 11:30:44');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_items`
--

CREATE TABLE `payroll_items` (
  `id` int(30) NOT NULL,
  `payroll_id` int(30) NOT NULL,
  `employee_id` int(30) NOT NULL,
  `present` int(30) NOT NULL,
  `absent` int(10) NOT NULL,
  `under_time` double NOT NULL,
  `late` double NOT NULL,
  `salary` double NOT NULL,
  `allowance_amount` double NOT NULL,
  `allowances` text NOT NULL,
  `deduction_amount` double NOT NULL,
  `contribute_amount` double NOT NULL,
  `deductions` text NOT NULL,
  `contributions` text NOT NULL,
  `time_logs` text NOT NULL,
  `time_log_amount` double NOT NULL,
  `net` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payroll_items`
--

INSERT INTO `payroll_items` (`id`, `payroll_id`, `employee_id`, `present`, `absent`, `under_time`, `late`, `salary`, `allowance_amount`, `allowances`, `deduction_amount`, `contribute_amount`, `deductions`, `contributions`, `time_logs`, `time_log_amount`, `net`, `date_created`) VALUES
(183, 16, 13, 4, 7, 31.666666666667, 31.666666666667, 380, 0, '[]', 0, 0, '[]', '[]', '', 0, 1488, '2021-01-05 17:39:20'),
(184, 19, 13, 4, 7, 31.666666666667, 31.666666666667, 380, 0, '[]', 0, 0, '[]', '[]', '{\"13\":[{\"tid\":\"3\",\"total_hours\":\"1\",\"amount\":47.5,\"rate\":0.7916666666666666}]}', 47.5, 1536, '2021-01-07 20:29:13'),
(186, 20, 13, 4, 7, 31.666666666667, 31.666666666667, 380, 0, '[]', 0, 0, '[]', '[]', '{\"13\":[{\"tid\":\"3\",\"total_hours\":\"1\",\"amount\":47.5,\"rate\":0.7916666666666666},{\"tid\":\"11\",\"total_hours\":\"3\",\"amount\":142.5,\"rate\":0.7916666666666666}]}', 190, 1678, '2021-01-09 18:18:01'),
(192, 21, 13, 11, 0, 47.5, 47.5, 380, 0, '[]', 0, 0, '[]', '[]', '{\"13\":[{\"tid\":\"18\",\"total_hours\":\"8\",\"amount\":380,\"rate\":0.7916666666666666}]}', 380, 4513, '2021-01-10 12:26:38'),
(197, 22, 13, 1, 10, 427.5, 611.95833333333, 380, 0, '[]', 0, 0, '[]', '[]', '[]', 0, -612, '2021-03-09 13:03:10'),
(198, 22, 23, 1, 10, 1200, 5160, 800, 0, '[]', 0, 0, '[]', '[]', '[]', 0, -5160, '2021-03-09 13:03:10'),
(199, 22, 24, 0, 11, 0, 0, 400, 0, '[]', 0, 0, '[]', '[]', '[]', 0, 0, '2021-03-09 13:03:10'),
(200, 22, 25, 1, 10, 562.5, 808.33333333333, 500, 0, '[]', 0, 0, '[]', '[]', '{\"25\":[{\"tid\":\"21\",\"total_hours\":\"1\",\"amount\":62.50000000000001,\"rate\":1.0416666666666667}]}', 62.5, -746, '2021-03-09 13:03:10'),
(223, 23, 13, 0, 11, 0, 0, 380, 0, '[]', 0, 0, '[]', '[]', '[]', 0, 0, '2021-04-01 17:22:11'),
(224, 23, 23, 0, 11, 0, 0, 800, 300, '[{\"aid\":\"5\",\"amount\":\"300\"}]', 0, 346, '[]', '{\"23\":[{\"cid\":\"1\",\"amount\":\"346\"}]}', '[]', 0, -46, '2021-04-01 17:22:11'),
(225, 23, 24, 0, 11, 0, 0, 400, 0, '[]', 0, 0, '[]', '[]', '[]', 0, 0, '2021-04-01 17:22:11'),
(226, 23, 25, 0, 11, 0, 0, 500, 0, '[]', 100, 0, '[{\"did\":\"5\",\"amount\":\"100\"}]', '[]', '{\"25\":[{\"tid\":\"21\",\"total_hours\":\"1\",\"amount\":62.50000000000000710542735760100185871124267578125,\"rate\":1.0416666666666667406815349750104360282421112060546875}]}', 62.5, -38, '2021-04-01 17:22:11'),
(227, 26, 13, 0, 22, 0, 0, 380, 500, '[{\"aid\":\"2\",\"amount\":\"500\"}]', 0, 0, '[]', '[]', '[]', 0, 500, '2021-04-01 17:23:43'),
(228, 26, 23, 0, 22, 0, 0, 800, 300, '[{\"aid\":\"5\",\"amount\":\"300\"}]', 0, 300, '[]', '{\"23\":[{\"cid\":\"3\",\"amount\":\"300\"}]}', '[]', 0, 0, '2021-04-01 17:23:43'),
(229, 26, 24, 0, 22, 0, 0, 400, 0, '[]', 0, 0, '[]', '[]', '[]', 0, 0, '2021-04-01 17:23:43'),
(230, 26, 25, 0, 22, 0, 0, 500, 0, '[]', 0, 0, '[]', '[]', '{\"25\":[{\"tid\":\"21\",\"total_hours\":\"1\",\"amount\":62.50000000000000710542735760100185871124267578125,\"rate\":1.0416666666666667406815349750104360282421112060546875}]}', 62.5, 63, '2021-04-01 17:23:43'),
(231, 24, 13, 1, 10, 427.5, 611.95833333333, 380, 0, '[]', 0, 0, '[]', '[]', '[]', 0, -612, '2021-07-20 11:31:31'),
(232, 24, 23, 1, 10, 1200, 5160, 800, 300, '[{\"aid\":\"5\",\"amount\":\"300\"}]', 0, 346, '[]', '{\"23\":[{\"cid\":\"1\",\"amount\":\"346\"}]}', '[]', 0, -5206, '2021-07-20 11:31:31'),
(233, 24, 24, 0, 11, 0, 0, 400, 0, '[]', 0, 0, '[]', '[]', '[]', 0, 0, '2021-07-20 11:31:31'),
(234, 24, 25, 1, 10, 562.5, 808.33333333333, 500, 0, '[]', 100, 0, '[{\"did\":\"5\",\"amount\":\"100\"}]', '[]', '{\"25\":[{\"tid\":\"21\",\"total_hours\":\"1\",\"amount\":62.50000000000000710542735760100185871124267578125,\"rate\":1.0416666666666667406815349750104360282421112060546875}]}', 62.5, -846, '2021-07-20 11:31:31'),
(235, 27, 13, 0, 11, 0, 0, 380, 0, '[]', 0, 0, '[]', '[]', '[]', 0, 0, '2021-07-21 12:26:24'),
(236, 27, 23, 0, 11, 0, 0, 800, 300, '[{\"aid\":\"5\",\"amount\":\"300\"}]', 0, 346, '[]', '{\"23\":[{\"cid\":\"1\",\"amount\":\"346\"}]}', '[]', 0, -46, '2021-07-21 12:26:25'),
(237, 27, 24, 0, 11, 0, 0, 400, 0, '[]', 0, 0, '[]', '[]', '[]', 0, 0, '2021-07-21 12:26:25'),
(238, 27, 25, 0, 11, 0, 0, 500, 0, '[]', 100, 0, '[{\"did\":\"5\",\"amount\":\"100\"}]', '[]', '{\"25\":[{\"tid\":\"21\",\"total_hours\":\"1\",\"amount\":62.50000000000000710542735760100185871124267578125,\"rate\":1.0416666666666667406815349750104360282421112060546875}]}', 62.5, -38, '2021-07-21 12:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `position`
--

CREATE TABLE `position` (
  `id` int(30) NOT NULL,
  `department_id` int(30) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `position`
--

INSERT INTO `position` (`id`, `department_id`, `name`) VALUES
(1, 1, 'Programmer 222'),
(2, 2, 'HR Supervisor'),
(7, 1, 'Senior Developer');

-- --------------------------------------------------------

--
-- Table structure for table `time_logs`
--

CREATE TABLE `time_logs` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `total_hours` double NOT NULL,
  `memo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `time_logs`
--

INSERT INTO `time_logs` (`id`, `employee_id`, `start_date`, `end_date`, `total_hours`, `memo`) VALUES
(21, 25, '2021-03-09 01:03:00', '2021-03-09 02:03:00', 1, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(30) NOT NULL,
  `doctor_id` int(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `contact` text NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1=admin , 2 = staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `doctor_id`, `name`, `address`, `contact`, `username`, `password`, `type`) VALUES
(1, 0, 'Administrator', '', '', 'admin', '0192023a7bbd73250516f069df18b500', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allowances`
--
ALTER TABLE `allowances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contributions`
--
ALTER TABLE `contributions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`);

--
-- Indexes for table `employee_allowances`
--
ALTER TABLE `employee_allowances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_contributions`
--
ALTER TABLE `employee_contributions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payroll_items`
--
ALTER TABLE `payroll_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_logs`
--
ALTER TABLE `time_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allowances`
--
ALTER TABLE `allowances`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=646;

--
-- AUTO_INCREMENT for table `contributions`
--
ALTER TABLE `contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `employee_allowances`
--
ALTER TABLE `employee_allowances`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `employee_contributions`
--
ALTER TABLE `employee_contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `payroll_items`
--
ALTER TABLE `payroll_items`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `time_logs`
--
ALTER TABLE `time_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
