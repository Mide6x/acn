-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2024 at 02:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hrdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `approvaltbl`
--

CREATE TABLE `approvaltbl` (
  `id` int(11) NOT NULL,
  `jdrequestid` varchar(20) DEFAULT NULL,
  `jdtitle` varchar(100) DEFAULT NULL,
  `approverstaffid` varchar(20) DEFAULT NULL,
  `approvallevel` enum('TeamLead','DeptUnitLead','HOD','HR','HeadOfHR','CFO','CEO') DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'draft',
  `comments` text DEFAULT NULL,
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approvaltbl`
--

INSERT INTO `approvaltbl` (`id`, `jdrequestid`, `jdtitle`, `approverstaffid`, `approvallevel`, `status`, `comments`, `createdby`, `dandt`) VALUES
(603, 'REQ20240001', 'Corporate Sales Consultant', 'SLS001', 'DeptUnitLead', 'approved', NULL, NULL, '2024-12-02 08:32:47'),
(604, 'REQ20240001', 'Corporate Sales Consultant', 'COM001', 'HOD', 'pending', NULL, NULL, '2024-12-02 08:32:47'),
(605, 'REQ20240001', 'Corporate Sales Consultant', 'HR002', 'HR', 'draft', NULL, NULL, '2024-12-02 08:32:47'),
(606, 'REQ20240001', 'Corporate Sales Consultant', 'HR001', 'HeadOfHR', 'draft', NULL, NULL, '2024-12-02 08:32:47'),
(607, 'REQ20240001', 'Corporate Sales Consultant', 'CFO001', 'CFO', 'draft', NULL, NULL, '2024-12-02 08:32:47'),
(608, 'REQ20240001', 'Corporate Sales Consultant', 'CEO001', 'CEO', 'draft', NULL, NULL, '2024-12-02 08:32:47'),
(609, 'REQ20241475', 'Corporate Sales Specialist', 'SLS001', 'DeptUnitLead', 'pending', NULL, 'mike.j@acn.aero', '2024-12-02 08:33:34'),
(610, 'REQ20241475', 'Corporate Sales Specialist', 'COM001', 'HOD', 'draft', NULL, 'mike.j@acn.aero', '2024-12-02 08:33:34'),
(611, 'REQ20241475', 'Corporate Sales Specialist', 'HR002', 'HR', 'draft', NULL, 'mike.j@acn.aero', '2024-12-02 08:33:34'),
(612, 'REQ20241475', 'Corporate Sales Specialist', 'HR001', 'HeadOfHR', 'draft', NULL, 'mike.j@acn.aero', '2024-12-02 08:33:34'),
(613, 'REQ20241475', 'Corporate Sales Specialist', 'CFO001', 'CFO', 'draft', NULL, 'mike.j@acn.aero', '2024-12-02 08:33:34'),
(614, 'REQ20241475', 'Corporate Sales Specialist', 'CEO001', 'CEO', 'draft', NULL, 'mike.j@acn.aero', '2024-12-02 08:33:34'),
(633, 'REQ20241476', 'Commercial Director', 'SLS001', 'DeptUnitLead', 'approved', NULL, NULL, '2024-12-02 08:51:20'),
(634, 'REQ20241476', 'Commercial Director', 'COM001', 'HOD', 'pending', NULL, NULL, '2024-12-02 08:51:20'),
(635, 'REQ20241476', 'Commercial Director', 'HR002', 'HR', 'draft', NULL, NULL, '2024-12-02 08:51:20'),
(636, 'REQ20241476', 'Commercial Director', 'HR001', 'HeadOfHR', 'draft', NULL, NULL, '2024-12-02 08:51:20'),
(637, 'REQ20241476', 'Commercial Director', 'CFO001', 'CFO', 'draft', NULL, NULL, '2024-12-02 08:51:20'),
(638, 'REQ20241476', 'Commercial Director', 'CEO001', 'CEO', 'draft', NULL, NULL, '2024-12-02 08:51:20');

-- --------------------------------------------------------

--
-- Table structure for table `businessunittbl`
--

CREATE TABLE `businessunittbl` (
  `id` int(11) NOT NULL,
  `businessunit` varchar(100) NOT NULL,
  `businesscode` varchar(10) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `businessunittbl`
--

INSERT INTO `businessunittbl` (`id`, `businessunit`, `businesscode`, `status`, `createdby`, `dandt`) VALUES
(1, 'Information Technology', 'ICT', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(2, 'Commercial', 'COM', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(3, 'Operations', 'OPS', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `departmentname` varchar(100) NOT NULL,
  `departmentcode` varchar(10) NOT NULL,
  `deptnostaff` int(11) NOT NULL DEFAULT 0,
  `deptwaiver` int(11) NOT NULL DEFAULT 0,
  `depttotal` int(11) GENERATED ALWAYS AS (`deptnostaff` + `deptwaiver`) STORED,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `departmentname`, `departmentcode`, `deptnostaff`, `deptwaiver`, `status`, `createdby`, `dandt`) VALUES
(1, 'Information Technology', 'ICT', 100, 10, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(2, 'Commercial', 'COM', 150, 15, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(3, 'Human Resources', 'HRD', 50, 5, 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:53'),
(4, 'Executive Management', 'EXE', 10, 0, 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `departmentunit`
--

CREATE TABLE `departmentunit` (
  `id` int(11) NOT NULL,
  `deptcode` varchar(10) DEFAULT NULL,
  `deptunitname` varchar(100) NOT NULL,
  `deptunitcode` varchar(10) NOT NULL,
  `deptunitnostaff` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departmentunit`
--

INSERT INTO `departmentunit` (`id`, `deptcode`, `deptunitname`, `deptunitcode`, `deptunitnostaff`, `status`, `createdby`, `dandt`) VALUES
(1, 'ICT', 'Development', 'DEV', 50, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(2, 'ICT', 'IT Support', 'ITS', 40, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(3, 'COM', 'Sales', 'SLS', 60, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(4, 'COM', 'Marketing', 'MKT', 70, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(5, 'HRD', 'HR Operations', 'HRD', 30, 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54'),
(6, 'EXE', 'Executive Office', 'EXE', 10, 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54'),
(7, NULL, 'Information Technology', 'ICT', 0, 'Active', NULL, '2024-11-25 07:47:16');

--
-- Triggers `departmentunit`
--
DELIMITER $$
CREATE TRIGGER `check_deptunit_headcount` BEFORE INSERT ON `departmentunit` FOR EACH ROW BEGIN
    DECLARE dept_total INT;
    SELECT depttotal INTO dept_total
    FROM departments 
    WHERE departmentcode = NEW.deptcode;
    
    IF NEW.deptunitnostaff > dept_total THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Department unit headcount cannot exceed department total';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `employeetbl`
--

CREATE TABLE `employeetbl` (
  `id` int(11) NOT NULL,
  `deptunitcode` varchar(10) DEFAULT NULL,
  `subdeptunitcode` varchar(10) DEFAULT NULL,
  `staffname` varchar(100) NOT NULL,
  `staffid` varchar(20) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp(),
  `jdtitle` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employeetbl`
--

INSERT INTO `employeetbl` (`id`, `deptunitcode`, `subdeptunitcode`, `staffname`, `staffid`, `position`, `status`, `createdby`, `dandt`, `jdtitle`) VALUES
(2, 'SLS', 'CSLS', 'Jane Smith', 'COM001', 'HOD', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', 'Commercial Director'),
(3, 'DEV', NULL, 'Bob Wilson', 'DEV001', 'DeptUnitLead', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', 'Senior Developer'),
(4, 'ITS', NULL, 'Alice Brown', 'ITS001', 'DeptUnitLead', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', 'IT Support Manager'),
(5, 'SLS', 'CSLS', 'Mike Johnson', 'CSLS001', 'TeamLead', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', 'Sales Team Lead'),
(6, 'MKT', 'DMKT', 'Sarah Davis', 'DMKT001', 'TeamLead', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', 'Digital Marketing Lead'),
(11, 'HRD', NULL, 'Sarah Wilson', 'HR001', 'HOD', 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54', 'Head of HR'),
(12, 'HRD', NULL, 'James Brown', 'HR002', 'DeptUnitLead', 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54', 'HR Manager'),
(13, 'EXE', NULL, 'Michael Chen', 'CFO001', 'CFO', 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54', 'Chief Financial Officer'),
(14, 'EXE', NULL, 'Elizabeth Taylor', 'CEO001', 'CEO', 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54', 'Chief Executive Officer'),
(15, 'SLS', NULL, 'Samuel Anderson', 'SLS001', 'DeptUnitLead', 'Active', 'adewole.o@acn.aero', '2024-11-22 13:58:14', 'Sales Manager'),
(16, 'MKT', NULL, 'Rebecca Green', 'MKT001', 'DeptUnitLead', 'Active', 'adewole.o@acn.aero', '2024-11-22 13:58:14', 'Marketing Manager');

-- --------------------------------------------------------

--
-- Table structure for table `jobtitletbl`
--

CREATE TABLE `jobtitletbl` (
  `id` int(11) NOT NULL,
  `deptunitcode` varchar(10) DEFAULT NULL,
  `jdtitle` varchar(100) NOT NULL,
  `jddescription` text DEFAULT NULL,
  `eduqualification` varchar(100) DEFAULT NULL,
  `proqualification` text DEFAULT NULL,
  `workrelation` text DEFAULT NULL,
  `jdposition` varchar(100) DEFAULT NULL,
  `jdcondition` varchar(100) DEFAULT NULL,
  `agebracket` varchar(50) DEFAULT NULL,
  `personspec` text DEFAULT NULL,
  `fuctiontech` text DEFAULT NULL,
  `managerial` text DEFAULT NULL,
  `behavioural` text DEFAULT NULL,
  `jdstatus` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp(),
  `subdeptunit` varchar(255) DEFAULT NULL,
  `subdeptunitcode` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobtitletbl`
--

INSERT INTO `jobtitletbl` (`id`, `deptunitcode`, `jdtitle`, `jddescription`, `eduqualification`, `proqualification`, `workrelation`, `jdposition`, `jdcondition`, `agebracket`, `personspec`, `fuctiontech`, `managerial`, `behavioural`, `jdstatus`, `createdby`, `dandt`, `subdeptunit`, `subdeptunitcode`) VALUES
(1, 'DEV', 'Senior Developer', 'Lead development position', 'Bachelors Degree', NULL, NULL, 'Senior Management', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(2, 'DEV', 'Software Engineer', 'Software development role', 'Bachelors Degree', NULL, NULL, 'Middle Management', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(3, 'ITS', 'IT Support Manager', 'IT support leadership', 'Bachelors Degree', NULL, NULL, 'Middle Management', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(4, 'ITS', 'IT Support Officer', 'IT support role', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(5, 'DEV', 'Senior Software Engineer', 'Senior development position', 'Bachelors Degree', NULL, NULL, 'Senior Management', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(6, 'ITS', 'Systems Administrator', 'IT systems administration', 'Bachelors Degree', NULL, NULL, 'Middle Management', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(7, 'MKT', 'Digital Marketing Specialist', 'Digital marketing role', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', 'Digital Marketing', 'DMKT'),
(8, 'SLS', 'Sales Team Lead', 'Sales team leadership', 'Bachelors Degree', 'Certified Sales Professional (CSP) or equivalent qualification.', 'Reports to the Sales Manager, manages Sales Associates.', 'TeamLead', 'Full-time, Office-based.', '30-45', 'Strong leadership and motivational skills, ability to meet sales targets.', 'Excellent communication, sales planning, and CRM tools proficiency.', 'Ability to mentor and develop team members, decision-making under pressure.', 'Positive attitude, high energy, adaptability', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(9, 'SLS', 'Sales Officer', 'Sales role', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(10, 'MKT', 'Marketing Manager', 'Marketing leadership', 'Bachelors Degree', NULL, NULL, 'Middle Management', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(11, 'MKT', 'Digital Marketing Lead', 'Digital marketing leadership', 'Bachelors Degree', NULL, NULL, 'TeamLead', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', 'Digital Marketing', 'DMKT'),
(12, 'DEV', 'IT Director', 'IT Department Head', 'Masters Degree', NULL, NULL, 'HOD', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(13, 'SLS', 'Commercial Director', 'Commercial Department Head', 'Masters Degree', NULL, NULL, 'HOD', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40', NULL, NULL),
(14, 'HRD', 'Head of HR', 'HR Department Head', 'Masters Degree', NULL, NULL, 'HOD', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54', NULL, NULL),
(15, 'HRD', 'HR Manager', 'HR Operations Manager', 'Masters Degree', NULL, NULL, 'DeptUnitLead', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54', NULL, NULL),
(16, 'EXE', 'Chief Financial Officer', 'Financial Operations Head', 'Masters Degree', NULL, NULL, 'CFO', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54', NULL, NULL),
(17, 'EXE', 'Chief Executive Officer', 'Company Head', 'Masters Degree', NULL, NULL, 'CEO', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 11:42:54', NULL, NULL),
(50, 'DEV', 'Backend Developer', 'Develops and maintains server-side logic', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', NULL, NULL),
(51, 'DEV', 'Frontend Developer', 'Develops user-facing features', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', NULL, NULL),
(52, 'DEV', 'Software Team Lead', 'Manages the software development team', 'Bachelors Degree', NULL, NULL, 'TeamLead', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', NULL, NULL),
(53, 'ITS', 'IT Support Engineer', 'Provides technical support', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', NULL, NULL),
(54, 'ITS', 'IT Support Team Lead', 'Leads the IT Support team', 'Bachelors Degree', NULL, NULL, 'TeamLead', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', NULL, NULL),
(55, 'SLS', 'Sales Manager', 'Manages the sales team', 'Bachelors Degree', NULL, NULL, 'DeptUnitLead', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', NULL, NULL),
(56, 'SLS', 'Corporate Sales Executive', 'Handles corporate sales', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', 'Corporate Sales', 'CSLS'),
(57, 'SLS', 'Retail Sales Executive', 'Handles retail sales', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', 'Retail Sales', 'RSLS'),
(58, 'MKT', 'Brand Manager', 'Manages brand identity', 'Bachelors Degree', NULL, NULL, 'Officer', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', 'adewole.o@acn.aero', '2024-11-22 12:34:07', 'Brand Marketing', 'BMKT'),
(60, 'SLS', 'Corporate Sales Specialist', 'Handles corporate sales accounts and relationships', 'Bachelors Degree', 'Certified Sales Professional (CSP)', 'Reports to Sales Manager and liaises with clients', 'Officer', 'Full-time, Office-based', '25-40', 'Strong communication and negotiation skills', 'Proficiency in CRM tools', 'Team collaboration skills', 'Result-oriented mindset', 'Active', 'adewole.o@acn.aero', '2024-11-25 10:19:34', 'Corporate Sales', 'CSLS'),
(61, 'SLS', 'Corporate Sales Consultant', 'Consults with corporate clients on tailored solutions', 'Bachelors Degree', 'Certified Sales Consultant', 'Reports to Corporate Sales Team Lead', 'Officer', 'Remote/Hybrid possible', '30-45', 'Excellent advisory and interpersonal skills', 'Strong analytical and problem-solving skills', 'Project coordination experience', 'Client-centric approach', 'Active', 'adewole.o@acn.aero', '2024-11-25 10:19:34', 'Corporate Sales', 'CSLS'),
(62, 'SLS', 'Corporate Sales Manager', 'Oversees corporate sales strategy and team', 'Masters Degree', 'MBA or equivalent preferred', 'Leads the Corporate Sales team', 'DeptUnitLead', 'Full-time, Office-based', '35-50', 'Strategic thinker with leadership skills', 'Advanced knowledge of sales forecasting and pipeline management', 'Team leadership and coaching', 'Adaptable and inspirational leader', 'Active', 'adewole.o@acn.aero', '2024-11-25 10:19:34', 'Corporate Sales', 'CSLS'),
(63, 'SLS', 'Retail Sales Specialist', 'Engages in retail sales and customer interaction', 'Bachelors Degree', 'Certified Retail Professional', 'Reports to Retail Sales Manager', 'Officer', 'Full-time, On-site', '22-35', 'Good product knowledge and customer engagement skills', 'Knowledge of point-of-sale (POS) systems', 'Time management and multitasking', 'Friendly and approachable demeanor', 'Active', 'adewole.o@acn.aero', '2024-11-25 10:19:34', 'Retail Sales', 'RSLS'),
(64, 'SLS', 'Retail Sales Supervisor', 'Supervises retail sales operations and staff', 'Bachelors Degree', 'Retail Management Certification', 'Reports to Retail Sales Manager', 'TeamLead', 'Full-time, On-site', '28-40', 'Strong leadership and organizational skills', 'Proficiency in inventory management software', 'Team-building and problem resolution', 'Dependable and customer-focused', 'Active', 'adewole.o@acn.aero', '2024-11-25 10:19:34', 'Retail Sales', 'RSLS');

-- --------------------------------------------------------

--
-- Table structure for table `positiontbl`
--

CREATE TABLE `positiontbl` (
  `id` int(11) NOT NULL,
  `deptunitcode` varchar(10) DEFAULT NULL,
  `poname` varchar(100) NOT NULL,
  `postatus` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positiontbl`
--

INSERT INTO `positiontbl` (`id`, `deptunitcode`, `poname`, `postatus`, `createdby`, `dandt`) VALUES
(1, 'DEV', 'Senior Developer', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(2, 'DEV', 'Software Engineer', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(3, 'ITS', 'IT Support Manager', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(4, 'ITS', 'Support Engineer', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(5, 'SLS', 'Sales Team Lead', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(6, 'SLS', 'Sales Officer', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(7, 'MKT', 'Marketing Manager', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(8, 'MKT', 'Digital Marketing Manager', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `reportingline`
--

CREATE TABLE `reportingline` (
  `id` int(11) NOT NULL,
  `rponame` varchar(100) DEFAULT NULL,
  `linemanager` varchar(100) DEFAULT NULL,
  `postatus` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reportingline`
--

INSERT INTO `reportingline` (`id`, `rponame`, `linemanager`, `postatus`, `createdby`, `dandt`) VALUES
(1, 'Senior Developer', 'IT Director', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(2, 'IT Support Manager', 'IT Director', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(3, 'Sales Team Lead', 'Commercial Director', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(4, 'Digital Marketing Manager', 'Marketing Manager', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `staffrequest`
--

CREATE TABLE `staffrequest` (
  `id` int(11) NOT NULL,
  `jdrequestid` varchar(20) NOT NULL,
  `jdtitle` varchar(100) NOT NULL,
  `novacpost` int(11) NOT NULL,
  `deptunitcode` varchar(10) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'draft',
  `dandt` timestamp NOT NULL DEFAULT current_timestamp(),
  `createdby` varchar(100) DEFAULT NULL,
  `subdeptunitcode` varchar(255) DEFAULT NULL,
  `staffid` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffrequest`
--

INSERT INTO `staffrequest` (`id`, `jdrequestid`, `jdtitle`, `novacpost`, `deptunitcode`, `status`, `dandt`, `createdby`, `subdeptunitcode`, `staffid`) VALUES
(106, 'REQ20240001', 'Corporate Sales Consultant', 2, 'SLS', 'pending', '2024-12-02 08:32:47', NULL, '', 'SLS001'),
(107, 'REQ20241475', 'Corporate Sales Specialist', 3, 'SLS', 'draft', '2024-12-02 08:33:34', 'mike.j@acn.aero', 'CSLS', 'CSLS001'),
(111, 'REQ20241476', 'Commercial Director', 2, 'SLS', 'draft', '2024-12-02 08:51:20', NULL, '', 'SLS001');

--
-- Triggers `staffrequest`
--
DELIMITER $$
CREATE TRIGGER `create_approval_levels` AFTER INSERT ON `staffrequest` FOR EACH ROW BEGIN
    DECLARE deptheadid VARCHAR(20);
    DECLARE deptunitleadid VARCHAR(20);
    DECLARE requestorposition VARCHAR(20);

    -- Get the requestor's position
    SELECT position INTO requestorposition
    FROM employeetbl
    WHERE staffid = NEW.staffid;

    -- Get relevant approvers based on department
    SELECT staffid INTO deptheadid
    FROM employeetbl
    WHERE deptunitcode = NEW.deptunitcode AND position = 'HOD';
    
    SELECT staffid INTO deptunitleadid
    FROM employeetbl
    WHERE deptunitcode = NEW.deptunitcode AND position = 'DeptUnitLead';

    -- Handle request based on the requestor's position
    IF requestorposition = 'TeamLead' THEN
        -- Insert approval levels for TeamLead requestor
        IF deptunitleadid IS NOT NULL THEN
            INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
            VALUES (NEW.jdrequestid, NEW.jdtitle, deptunitleadid, 'DeptUnitLead', 'pending', NEW.createdby);
        END IF;

        -- HOD approval
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, deptheadid, 'HOD', 'draft', NEW.createdby);

        -- HR approvals
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES 
        (NEW.jdrequestid, NEW.jdtitle, 'HR002', 'HR', 'draft', NEW.createdby),
        (NEW.jdrequestid, NEW.jdtitle, 'HR001', 'HeadOfHR', 'draft', NEW.createdby),
        (NEW.jdrequestid, NEW.jdtitle, 'CFO001', 'CFO', 'draft', NEW.createdby),
        (NEW.jdrequestid, NEW.jdtitle, 'CEO001', 'CEO', 'draft', NEW.createdby);

    ELSEIF requestorposition = 'DeptUnitLead' THEN
        -- Insert approval levels for DeptUnitLead requestor
        IF deptunitleadid IS NOT NULL THEN
            INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
            VALUES (NEW.jdrequestid, NEW.jdtitle, deptunitleadid, 'DeptUnitLead', 'approved', NEW.createdby);
        END IF;

        -- HOD approval as pending
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, deptheadid, 'HOD', 'pending', NEW.createdby);

        -- HR approvals
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES 
        (NEW.jdrequestid, NEW.jdtitle, 'HR002', 'HR', 'draft', NEW.createdby),
        (NEW.jdrequestid, NEW.jdtitle, 'HR001', 'HeadOfHR', 'draft', NEW.createdby),
        (NEW.jdrequestid, NEW.jdtitle, 'CFO001', 'CFO', 'draft', NEW.createdby),
        (NEW.jdrequestid, NEW.jdtitle, 'CEO001', 'CEO', 'draft', NEW.createdby);

    ELSEIF requestorposition = 'HOD' THEN
        -- HR approval as pending
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, 'HR002', 'HR', 'pending', NEW.createdby);

        -- HeadOfHR approval
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, 'HR001', 'HeadOfHR', 'draft', NEW.createdby);

        -- CFO approval
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, 'CFO001', 'CFO', 'draft', NEW.createdby);

        -- CEO approval
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, 'CEO001', 'CEO', 'draft', NEW.createdby);

    ELSEIF requestorposition = 'HR' THEN
        -- HR approval as pending
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, 'HR001', 'HR', 'approved', NEW.createdby);

        -- HeadOfHR approval
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, 'HR002', 'HeadOfHR', 'pending', NEW.createdby);

        -- CFO approval
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, 'CFO001', 'CFO', 'draft', NEW.createdby);

        -- CEO approval
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, 'CEO001', 'CEO', 'draft', NEW.createdby);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `staffrequestperstation`
--

CREATE TABLE `staffrequestperstation` (
  `id` int(11) NOT NULL,
  `jdrequestid` varchar(20) DEFAULT NULL,
  `station` varchar(10) DEFAULT NULL,
  `employmenttype` varchar(50) DEFAULT NULL,
  `staffperstation` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp(),
  `createdby` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffrequestperstation`
--

INSERT INTO `staffrequestperstation` (`id`, `jdrequestid`, `station`, `employmenttype`, `staffperstation`, `status`, `reason`, `dandt`, `createdby`) VALUES
(28, 'REQ20240001', 'ABV', 'Contract', 2, 'pending', NULL, '2024-12-02 08:32:47', 'SLS001'),
(29, 'REQ20241475', 'KAN', 'Permanent', 3, 'pending', NULL, '2024-12-02 08:33:34', 'mike.j@acn.aero'),
(31, 'REQ20241476', 'ABV', 'Contract', 2, 'pending', NULL, '2024-12-02 08:51:20', 'SLS001');

-- --------------------------------------------------------

--
-- Table structure for table `stafftype`
--

CREATE TABLE `stafftype` (
  `id` int(11) NOT NULL,
  `stafftype` varchar(50) NOT NULL,
  `stprefix` varchar(10) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stafftype`
--

INSERT INTO `stafftype` (`id`, `stafftype`, `stprefix`, `status`, `createdby`, `dandt`) VALUES
(1, 'Permanent', 'PER', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(2, 'Contract', 'CON', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(3, 'Temporary', 'TMP', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `stationtbl`
--

CREATE TABLE `stationtbl` (
  `id` int(11) NOT NULL,
  `stationname` varchar(100) NOT NULL,
  `stationcode` varchar(10) NOT NULL,
  `stationtype` enum('Domestic','Regional','International') DEFAULT 'Domestic',
  `operationtype` enum('Fixed','Rotary') DEFAULT 'Fixed',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stationtbl`
--

INSERT INTO `stationtbl` (`id`, `stationname`, `stationcode`, `stationtype`, `operationtype`, `status`, `createdby`, `dandt`) VALUES
(1, 'Lagos', 'LOS', 'Domestic', 'Fixed', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(2, 'Abuja', 'ABV', 'Domestic', 'Fixed', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(3, 'Kano', 'KAN', 'Domestic', 'Fixed', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39'),
(4, 'United Kingdom', 'UK', 'International', 'Fixed', 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `subdeptunittbl`
--

CREATE TABLE `subdeptunittbl` (
  `id` int(11) NOT NULL,
  `deptunitcode` varchar(10) DEFAULT NULL,
  `subdeptunit` varchar(100) NOT NULL,
  `subdeptunitcode` varchar(10) NOT NULL,
  `subdeptnostaff` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `createdby` varchar(100) DEFAULT NULL,
  `dandt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subdeptunittbl`
--

INSERT INTO `subdeptunittbl` (`id`, `deptunitcode`, `subdeptunit`, `subdeptunitcode`, `subdeptnostaff`, `status`, `createdby`, `dandt`) VALUES
(1, 'SLS', 'Corporate Sales', 'CSLS', 30, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(2, 'SLS', 'Retail Sales', 'RSLS', 25, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(3, 'MKT', 'Digital Marketing', 'DMKT', 35, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40'),
(4, 'MKT', 'Brand Marketing', 'BMKT', 30, 'Active', 'adewole.o@acn.aero', '2024-11-22 10:32:40');

--
-- Triggers `subdeptunittbl`
--
DELIMITER $$
CREATE TRIGGER `check_subdept_headcount` BEFORE INSERT ON `subdeptunittbl` FOR EACH ROW BEGIN
    DECLARE unit_total INT;
    SELECT deptunitnostaff INTO unit_total
    FROM departmentunit 
    WHERE deptunitcode = NEW.deptunitcode;
    
    IF NEW.subdeptnostaff > unit_total THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Subunit headcount cannot exceed department unit total';
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approvaltbl`
--
ALTER TABLE `approvaltbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jdrequestid` (`jdrequestid`),
  ADD KEY `jdtitle` (`jdtitle`),
  ADD KEY `approverstaffid` (`approverstaffid`);

--
-- Indexes for table `businessunittbl`
--
ALTER TABLE `businessunittbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `businesscode` (`businesscode`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departmentcode` (`departmentcode`);

--
-- Indexes for table `departmentunit`
--
ALTER TABLE `departmentunit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deptunitcode` (`deptunitcode`),
  ADD KEY `deptcode` (`deptcode`);

--
-- Indexes for table `employeetbl`
--
ALTER TABLE `employeetbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staffid` (`staffid`),
  ADD KEY `deptunitcode` (`deptunitcode`),
  ADD KEY `subdeptunitcode` (`subdeptunitcode`),
  ADD KEY `jdtitle` (`jdtitle`);

--
-- Indexes for table `jobtitletbl`
--
ALTER TABLE `jobtitletbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jdtitle` (`jdtitle`),
  ADD KEY `deptunitcode` (`deptunitcode`);

--
-- Indexes for table `positiontbl`
--
ALTER TABLE `positiontbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `poname` (`poname`),
  ADD KEY `deptunitcode` (`deptunitcode`);

--
-- Indexes for table `reportingline`
--
ALTER TABLE `reportingline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rponame` (`rponame`);

--
-- Indexes for table `staffrequest`
--
ALTER TABLE `staffrequest`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jdrequestid` (`jdrequestid`),
  ADD KEY `deptunitcode` (`deptunitcode`),
  ADD KEY `jdtitle` (`jdtitle`),
  ADD KEY `subdeptunitcode` (`subdeptunitcode`);

--
-- Indexes for table `staffrequestperstation`
--
ALTER TABLE `staffrequestperstation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jdrequestid` (`jdrequestid`),
  ADD KEY `station` (`station`),
  ADD KEY `employmenttype` (`employmenttype`);

--
-- Indexes for table `stafftype`
--
ALTER TABLE `stafftype`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stafftype` (`stafftype`);

--
-- Indexes for table `stationtbl`
--
ALTER TABLE `stationtbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stationcode` (`stationcode`);

--
-- Indexes for table `subdeptunittbl`
--
ALTER TABLE `subdeptunittbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subdeptunitcode` (`subdeptunitcode`),
  ADD KEY `deptunitcode` (`deptunitcode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approvaltbl`
--
ALTER TABLE `approvaltbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=639;

--
-- AUTO_INCREMENT for table `businessunittbl`
--
ALTER TABLE `businessunittbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departmentunit`
--
ALTER TABLE `departmentunit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `employeetbl`
--
ALTER TABLE `employeetbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `jobtitletbl`
--
ALTER TABLE `jobtitletbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `positiontbl`
--
ALTER TABLE `positiontbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reportingline`
--
ALTER TABLE `reportingline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staffrequest`
--
ALTER TABLE `staffrequest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `staffrequestperstation`
--
ALTER TABLE `staffrequestperstation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `stafftype`
--
ALTER TABLE `stafftype`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stationtbl`
--
ALTER TABLE `stationtbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subdeptunittbl`
--
ALTER TABLE `subdeptunittbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `approvaltbl`
--
ALTER TABLE `approvaltbl`
  ADD CONSTRAINT `approvaltbl_ibfk_1` FOREIGN KEY (`jdrequestid`) REFERENCES `staffrequest` (`jdrequestid`),
  ADD CONSTRAINT `approvaltbl_ibfk_2` FOREIGN KEY (`jdtitle`) REFERENCES `jobtitletbl` (`jdtitle`),
  ADD CONSTRAINT `approvaltbl_ibfk_3` FOREIGN KEY (`approverstaffid`) REFERENCES `employeetbl` (`staffid`);

--
-- Constraints for table `departmentunit`
--
ALTER TABLE `departmentunit`
  ADD CONSTRAINT `departmentunit_ibfk_1` FOREIGN KEY (`deptcode`) REFERENCES `departments` (`departmentcode`);

--
-- Constraints for table `employeetbl`
--
ALTER TABLE `employeetbl`
  ADD CONSTRAINT `employeetbl_ibfk_1` FOREIGN KEY (`deptunitcode`) REFERENCES `departmentunit` (`deptunitcode`),
  ADD CONSTRAINT `employeetbl_ibfk_2` FOREIGN KEY (`subdeptunitcode`) REFERENCES `subdeptunittbl` (`subdeptunitcode`),
  ADD CONSTRAINT `employeetbl_ibfk_3` FOREIGN KEY (`jdtitle`) REFERENCES `jobtitletbl` (`jdtitle`);

--
-- Constraints for table `jobtitletbl`
--
ALTER TABLE `jobtitletbl`
  ADD CONSTRAINT `jobtitletbl_ibfk_1` FOREIGN KEY (`deptunitcode`) REFERENCES `departmentunit` (`deptunitcode`);

--
-- Constraints for table `positiontbl`
--
ALTER TABLE `positiontbl`
  ADD CONSTRAINT `positiontbl_ibfk_1` FOREIGN KEY (`deptunitcode`) REFERENCES `departmentunit` (`deptunitcode`);

--
-- Constraints for table `reportingline`
--
ALTER TABLE `reportingline`
  ADD CONSTRAINT `reportingline_ibfk_1` FOREIGN KEY (`rponame`) REFERENCES `positiontbl` (`poname`);

--
-- Constraints for table `staffrequest`
--
ALTER TABLE `staffrequest`
  ADD CONSTRAINT `staffrequest_ibfk_1` FOREIGN KEY (`deptunitcode`) REFERENCES `departmentunit` (`deptunitcode`),
  ADD CONSTRAINT `staffrequest_ibfk_2` FOREIGN KEY (`jdtitle`) REFERENCES `jobtitletbl` (`jdtitle`);

--
-- Constraints for table `staffrequestperstation`
--
ALTER TABLE `staffrequestperstation`
  ADD CONSTRAINT `staffrequestperstation_ibfk_1` FOREIGN KEY (`jdrequestid`) REFERENCES `staffrequest` (`jdrequestid`),
  ADD CONSTRAINT `staffrequestperstation_ibfk_2` FOREIGN KEY (`station`) REFERENCES `stationtbl` (`stationcode`),
  ADD CONSTRAINT `staffrequestperstation_ibfk_3` FOREIGN KEY (`employmenttype`) REFERENCES `stafftype` (`stafftype`);

--
-- Constraints for table `subdeptunittbl`
--
ALTER TABLE `subdeptunittbl`
  ADD CONSTRAINT `subdeptunittbl_ibfk_1` FOREIGN KEY (`deptunitcode`) REFERENCES `departmentunit` (`deptunitcode`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
