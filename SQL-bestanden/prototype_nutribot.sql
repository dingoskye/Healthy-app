-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2025 at 09:13 AM
-- Server version: 8.4.2
-- PHP Version: 8.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT*/;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS*/;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION*/;
/*!40101 SET NAMES utf8mb4*/;

--
-- Database: prototype_nutribot
--

-- --------------------------------------------------------

--
-- Table structure for table meals
--

CREATE TABLE meals (
                       id bigint UNSIGNED NOT NULL,
                       user_id bigint UNSIGNED NOT NULL,
                       meal_type varchar(20) NOT NULL,
                       eaten_at datetime NOT NULL,
                       protein_g decimal(6,2) DEFAULT '0.00',
                       carbs_g decimal(6,2) DEFAULT '0.00',
                       fat_g decimal(6,2) DEFAULT '0.00',
                       fiber_g decimal(6,2) DEFAULT '0.00',
                       notes text,
                       dish varchar(50) DEFAULT 'DEFAULT NULL',
                       created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table users
--

CREATE TABLE users (
                       id bigint UNSIGNED NOT NULL,
                       email varchar(255) NOT NULL,
                       password varchar(255) NOT NULL,
                       first_name varchar(100) NOT NULL,
                       last_name varchar(100) NOT NULL,
                       date_of_birth date NOT NULL,
                       sex varchar(20) NOT NULL DEFAULT 'DEFAULT NULL',
                       height_cm decimal(5,2) NOT NULL,
                       weight_kg decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table meals
--
ALTER TABLE meals
    ADD PRIMARY KEY (id),
  ADD KEY meals_user_id_index (user_id);

--
-- Indexes for table users
--
ALTER TABLE users
    ADD PRIMARY KEY (id),
  ADD UNIQUE KEY users_email_unique (email);

--
-- Constraints for dumped tables
--

--
-- Constraints for table meals
--
ALTER TABLE meals
    ADD CONSTRAINT meals_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT*/;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS*/;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION*/;