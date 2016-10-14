-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Oct 14, 2016 at 08:08 PM
-- Server version: 5.6.28
-- PHP Version: 7.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `evote`
--

-- --------------------------------------------------------

--
-- Table structure for table `dvds`
--

CREATE TABLE `dvds` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `cagtegory` text NOT NULL,
  `price` float NOT NULL,
  `voteAverage` int(11) NOT NULL,
  `numVotes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dvds`
--

INSERT INTO `dvds` (`id`, `title`, `cagtegory`, `price`, `voteAverage`, `numVotes`) VALUES
(1, 'Jaws', 'thriller', 9.99, 80, 20),
(2, 'Jaws II', 'thriller', 5.99, 65, 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dvds`
--
ALTER TABLE `dvds`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dvds`
--
ALTER TABLE `dvds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;